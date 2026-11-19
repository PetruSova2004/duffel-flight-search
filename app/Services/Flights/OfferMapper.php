<?php

namespace App\Services\Flights;

use App\Data\Flights\Baggage;
use App\Data\Flights\Carrier;
use App\Data\Flights\FlightPoint;
use App\Data\Flights\Layover;
use App\Data\Flights\Money;
use App\Data\Flights\Offer;
use App\Data\Flights\Segment;
use Carbon\CarbonImmutable;
use DateInterval;
use Exception;

/**
 * Turns a raw Duffel offer (single slice) into display-ready DTOs.
 */
class OfferMapper
{
    /**
     * @param array<string, mixed> $offer
     * @return Offer
     * @throws Exception On a malformed offer
     */
    public function map(array $offer): Offer
    {
        $slice = $offer['slices'][0];
        $segments = array_map($this->segment(...), array_values($slice['segments']));

        if ($segments === []) {
            throw new \UnexpectedValueException('Offer has no segments');
        }

        $first = $segments[0];
        $last = $segments[array_key_last($segments)];
        $passenger = $slice['segments'][0]['passengers'][0] ?? [];

        return new Offer(
            id: $offer['id'],
            airline: $this->carrier($offer['owner']),
            price: new Money($offer['total_amount'], $offer['total_currency']),
            durationMinutes: $this->minutes($slice['duration'] ?? null)
                ?? $this->diffMinutes($first->departure->at, $last->arrival->at),
            segments: $segments,
            layovers: $this->layovers($segments),
            cabinClass: $passenger['cabin_class_marketing_name'] ?? $passenger['cabin_class'] ?? null,
            fareBrand: $slice['fare_brand_name'] ?? null,
            baggage: array_values(array_map(
                fn (array $item) => new Baggage($item['type'], (int) $item['quantity']),
                $passenger['baggages'] ?? [],
            )),
            expiresAt: isset($offer['expires_at']) ? CarbonImmutable::parse($offer['expires_at']) : null,
            co2Kg: isset($offer['total_emissions_kg']) ? (int) $offer['total_emissions_kg'] : null,
        );
    }

    /**
     * @param array<string, mixed> $segment
     * @return Segment
     * @throws Exception
     */
    private function segment(array $segment): Segment
    {
        $marketing = $this->carrier($segment['marketing_carrier']);
        $operating = $this->carrier($segment['operating_carrier'] ?? $segment['marketing_carrier']);
        $departure = $this->point($segment['origin'], $segment['origin_terminal'] ?? null, $segment['departing_at']);
        $arrival = $this->point($segment['destination'], $segment['destination_terminal'] ?? null, $segment['arriving_at']);

        return new Segment(
            flightNumber: $marketing->code.' '.$segment['marketing_carrier_flight_number'],
            marketingCarrier: $marketing,
            operatingCarrier: $operating->code !== $marketing->code ? $operating : null,
            departure: $departure,
            arrival: $arrival,
            durationMinutes: $this->minutes($segment['duration'] ?? null) ?? $this->diffMinutes($departure->at, $arrival->at),
            aircraft: $segment['aircraft']['name'] ?? null,
        );
    }

    /**
     * @param non-empty-list<Segment> $segments
     * @return list<Layover>
     */
    private function layovers(array $segments): array
    {
        $layovers = [];

        for ($i = 1; $i < count($segments); $i++) {
            $arrival = $segments[$i - 1]->arrival;
            $next = $segments[$i]->departure;

            $layovers[] = new Layover(
                airportCode: $arrival->airportCode,
                cityName: $arrival->cityName,
                durationMinutes: $this->diffMinutes($arrival->at, $next->at),
                changesAirport: $arrival->airportCode !== $next->airportCode,
            );
        }

        return $layovers;
    }

    /**
     * @param array<string, mixed> $carrier
     * @return Carrier
     */
    private function carrier(array $carrier): Carrier
    {
        return new Carrier(
            code: (string) ($carrier['iata_code'] ?? ''),
            name: (string) ($carrier['name'] ?? ''),
            logoUrl: $carrier['logo_symbol_url'] ?? null,
        );
    }

    /**
     * Duffel sends local times without offset; the airport's time zone completes them.
     *
     * @param array<string, mixed> $place
     * @param string|null $terminal
     * @param string $localTime
     * @return FlightPoint
     */
    private function point(array $place, ?string $terminal, string $localTime): FlightPoint
    {
        return new FlightPoint(
            airportCode: $place['iata_code'],
            airportName: $place['name'],
            cityName: $place['city_name'] ?? $place['city']['name'] ?? null,
            terminal: $terminal,
            at: CarbonImmutable::parse($localTime, $place['time_zone'] ?? 'UTC'),
        );
    }

    /**
     * @param string|null $isoDuration e.g. "PT7H58M" or "P1DT2H"
     * @return int|null
     * @throws Exception
     */
    private function minutes(?string $isoDuration): ?int
    {
        if ($isoDuration === null || $isoDuration === '') {
            return null;
        }

        $interval = new DateInterval($isoDuration);

        return $interval->d * 1440 + $interval->h * 60 + $interval->i;
    }

    /**
     * @param CarbonImmutable $from
     * @param CarbonImmutable $to
     * @return int
     */
    private function diffMinutes(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (int) $from->diffInMinutes($to);
    }
}
