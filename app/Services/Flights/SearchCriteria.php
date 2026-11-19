<?php

namespace App\Services\Flights;

use Carbon\CarbonImmutable;

final readonly class SearchCriteria
{
    /**
     * @param CarbonImmutable $departureDate
     * @param string $origin IATA city or airport code
     * @param string $destination IATA city or airport code
     */
    public function __construct(
        public CarbonImmutable $departureDate,
        public string $origin,
        public string $destination,
    ) {}
}
