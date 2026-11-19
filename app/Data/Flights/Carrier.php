<?php

namespace App\Data\Flights;

final readonly class Carrier
{
    /**
     * @param string $code IATA code, e.g. "LH"
     * @param string $name
     * @param string|null $logoUrl
     */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $logoUrl,
    ) {}
}
