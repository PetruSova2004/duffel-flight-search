<?php

namespace App\Data\Flights;

use Carbon\CarbonImmutable;

final readonly class SearchResult
{
    /**
     * @param list<Offer> $offers Cheapest first
     * @param CarbonImmutable $departureDate
     * @param string $originName
     * @param string $destinationName
     */
    public function __construct(
        public array $offers,
        public CarbonImmutable $departureDate,
        public string $originName,
        public string $destinationName,
    ) {}

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->offers === [];
    }
}
