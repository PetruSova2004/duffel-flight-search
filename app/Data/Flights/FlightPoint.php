<?php

namespace App\Data\Flights;

use Carbon\CarbonImmutable;

/**
 * Departure or arrival: airport plus local time in that airport's time zone.
 */
final readonly class FlightPoint
{
    /**
     * @param string $airportCode
     * @param string $airportName
     * @param string|null $cityName
     * @param string|null $terminal
     * @param CarbonImmutable $at
     */
    public function __construct(
        public string $airportCode,
        public string $airportName,
        public ?string $cityName,
        public ?string $terminal,
        public CarbonImmutable $at,
    ) {}
}
