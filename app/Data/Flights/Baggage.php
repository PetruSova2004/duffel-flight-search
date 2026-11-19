<?php

namespace App\Data\Flights;

final readonly class Baggage
{
    /**
     * @param string $type "checked" or "carry_on"
     * @param int $quantity
     */
    public function __construct(
        public string $type,
        public int $quantity,
    ) {}
}
