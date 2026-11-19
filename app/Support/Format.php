<?php

namespace App\Support;

use App\Data\Flights\Money;
use Carbon\CarbonInterface;
use NumberFormatter;

/**
 * Human-friendly formatting for dates, durations and prices in the current locale.
 */
final class Format
{
    /**
     * @param Money $money
     * @return string e.g. "245,90 €"
     */
    public static function money(Money $money): string
    {
        $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $money->amount, $money->currency) ?: $money->amount.' '.$money->currency;
    }

    /**
     * @param int $minutes
     * @return string e.g. "2 ч 35 мин"
     */
    public static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return match (true) {
            $hours === 0 => __('flights.duration.minutes', ['m' => $rest]),
            $rest === 0 => __('flights.duration.hours', ['h' => $hours]),
            default => __('flights.duration.full', ['h' => $hours, 'm' => $rest]),
        };
    }

    /**
     * @param CarbonInterface $date
     * @return string e.g. "12 дек 2026"
     */
    public static function date(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    }

    /**
     * @param CarbonInterface $date
     * @return string e.g. "сб, 12 дек"
     */
    public static function shortDate(CarbonInterface $date): string
    {
        return $date->locale(app()->getLocale())->isoFormat('dd, D MMM');
    }

    /**
     * @param CarbonInterface $date
     * @return string e.g. "08:05"
     */
    public static function time(CarbonInterface $date): string
    {
        return $date->format('H:i');
    }
}
