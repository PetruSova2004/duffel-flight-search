<?php

namespace App\Data\Flights;

use Carbon\CarbonImmutable;

/**
 * One bookable option for a one-way trip, ready for display.
 */
final readonly class Offer
{
    /**
     * @param string $id
     * @param Carrier $airline Offer owner
     * @param Money $price
     * @param int $durationMinutes Whole trip incl. layovers
     * @param non-empty-list<Segment> $segments
     * @param list<Layover> $layovers
     * @param string|null $cabinClass Duffel code, e.g. "economy"
     * @param string|null $fareBrand e.g. "Basic"
     * @param list<Baggage> $baggage
     * @param CarbonImmutable|null $expiresAt
     * @param int|null $co2Kg
     */
    public function __construct(
        public string $id,
        public Carrier $airline,
        public Money $price,
        public int $durationMinutes,
        public array $segments,
        public array $layovers,
        public ?string $cabinClass,
        public ?string $fareBrand,
        public array $baggage,
        public ?CarbonImmutable $expiresAt,
        public ?int $co2Kg,
    ) {}

    /**
     * @return FlightPoint
     */
    public function departure(): FlightPoint
    {
        return $this->segments[0]->departure;
    }

    /**
     * @return FlightPoint
     */
    public function arrival(): FlightPoint
    {
        return $this->segments[array_key_last($this->segments)]->arrival;
    }

    /**
     * @return int
     */
    public function arrivalDayOffset(): int
    {
        return DayOffset::between($this->departure(), $this->arrival());
    }

    /**
     * @return list<string>
     */
    public function flightNumbers(): array
    {
        return array_map(fn (Segment $segment) => $segment->flightNumber, $this->segments);
    }

    /**
     * @param string $type
     * @return int
     */
    public function baggageCount(string $type): int
    {
        return array_sum(array_map(
            fn (Baggage $item) => $item->type === $type ? $item->quantity : 0,
            $this->baggage,
        ));
    }
}
