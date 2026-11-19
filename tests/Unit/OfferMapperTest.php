<?php

namespace Tests\Unit;

use App\Services\Flights\OfferMapper;
use PHPUnit\Framework\TestCase;

class OfferMapperTest extends TestCase
{
    /** @var list<array<string, mixed>> */
    private array $offers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->offers = json_decode((string) file_get_contents(__DIR__.'/../Fixtures/duffel/offer_request.json'), true)['data']['offers'];
    }

    public function test_it_maps_a_direct_flight(): void
    {
        $offer = (new OfferMapper)->map($this->offers[1]);

        $this->assertSame('Iberia', $offer->airline->name);
        $this->assertSame('218.22', $offer->price->amount);
        $this->assertSame('EUR', $offer->price->currency);
        $this->assertSame(478, $offer->durationMinutes);
        $this->assertSame([], $offer->layovers);
        $this->assertSame('LHR', $offer->departure()->airportCode);
        $this->assertSame('2', $offer->departure()->terminal);
        $this->assertSame('2026-12-19 13:56 Europe/London', $offer->departure()->at->format('Y-m-d H:i e'));
        $this->assertSame('2026-12-19 16:54 America/New_York', $offer->arrival()->at->format('Y-m-d H:i e'));
        $this->assertSame(0, $offer->arrivalDayOffset());
        $this->assertSame('Economy', $offer->cabinClass);
        $this->assertSame(1, $offer->baggageCount('checked'));
        $this->assertSame(1, $offer->baggageCount('carry_on'));
        $this->assertSame(472, $offer->co2Kg);
        $this->assertNull($offer->segments[0]->operatingCarrier);
    }

    public function test_it_maps_a_connection_with_layover_and_other_operator(): void
    {
        $offer = (new OfferMapper)->map($this->offers[0]);

        $this->assertSame(['TS 0123', 'TS 7607'], $offer->flightNumbers());
        $this->assertSame(802, $offer->durationMinutes);
        $this->assertCount(1, $offer->layovers);
        $this->assertSame('YYZ', $offer->layovers[0]->airportCode);
        $this->assertSame(200, $offer->layovers[0]->durationMinutes);
        $this->assertFalse($offer->layovers[0]->changesAirport);
        $this->assertSame('Porter Airlines', $offer->segments[1]->operatingCarrier?->name);
        $this->assertSame('Embraer 195 E2', $offer->segments[1]->aircraft);
    }

    public function test_it_detects_next_day_arrival(): void
    {
        $raw = $this->offers[1];
        $raw['slices'][0]['segments'][0]['arriving_at'] = '2026-12-20T01:10:00';

        $this->assertSame(1, (new OfferMapper)->map($raw)->arrivalDayOffset());
    }
}
