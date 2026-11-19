<?php

namespace Tests\Unit;

use App\Data\Flights\Money;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class FormatTest extends TestCase
{
    public function test_duration(): void
    {
        $this->assertSame('2 ч 35 мин', Format::duration(155));
        $this->assertSame('3 ч', Format::duration(180));
        $this->assertSame('45 мин', Format::duration(45));
    }

    public function test_money_and_dates(): void
    {
        $date = CarbonImmutable::parse('2026-12-12 08:05');

        $this->assertSame("245,90\u{a0}€", Format::money(new Money('245.90', 'EUR')));
        $this->assertSame('12 дек. 2026', Format::date($date));
        $this->assertSame('сб, 12 дек.', Format::shortDate($date));
        $this->assertSame('эконом', Format::cabin('economy'));
        $this->assertSame('08:05', Format::time($date));
    }
}
