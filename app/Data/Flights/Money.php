<?php

namespace App\Data\Flights;

final readonly class Money
{
    /**
     * @param string $amount Decimal string as returned by Duffel, e.g. "245.90"
     * @param string $currency ISO 4217 code
     */
    public function __construct(
        public string $amount,
        public string $currency,
    ) {}
}
