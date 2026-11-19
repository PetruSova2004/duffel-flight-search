<?php

namespace App\Data\Flights;

final readonly class Layover
{
    /**
     * @param string $airportCode
     * @param string|null $cityName
     * @param int $durationMinutes
     * @param bool $changesAirport Next flight leaves from another airport
     */
    public function __construct(
        public string $airportCode,
        public ?string $cityName,
        public int $durationMinutes,
        public bool $changesAirport,
    ) {}
}
