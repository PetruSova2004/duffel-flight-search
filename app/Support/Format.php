<?php

namespace App\Support;

use App\Data\Flights\Money;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Lang;
use IntlDateFormatter;
use NumberFormatter;

/**
 * Human-friendly formatting for dates, durations and prices in the current locale.
 */
final class Format
{
    // Formatters are costly to create, the results page calls them hundreds of times
    /** @var array<string, NumberFormatter> */
    private static array $numberFormatters = [];

    /** @var array<string, IntlDateFormatter> */
    private static array $dateFormatters = [];

    /**
     * @param Money $money
     * @return string e.g. "245,90 €"
     */
    public static function money(Money $money): string
    {
        $formatter = self::$numberFormatters[app()->getLocale()] ??= new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);

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
     * @return string e.g. "12 дек. 2026"
     */
    public static function date(CarbonInterface $date): string
    {
        return self::pattern($date, 'd MMM y');
    }

    /**
     * @param CarbonInterface $date
     * @return string e.g. "сб, 12 дек."
     */
    public static function shortDate(CarbonInterface $date): string
    {
        return self::pattern($date, 'EEEEEE, d MMM');
    }

    /**
     * @param CarbonInterface $date
     * @return string e.g. "08:05"
     */
    public static function time(CarbonInterface $date): string
    {
        return $date->format('H:i');
    }

    /**
     * @param string|null $cabinClass Duffel code: economy, premium_economy, business, first
     * @return string|null
     */
    public static function cabin(?string $cabinClass): ?string
    {
        if ($cabinClass === null) {
            return null;
        }

        return Lang::has('flights.cabin.'.$cabinClass) ? __('flights.cabin.'.$cabinClass) : $cabinClass;
    }

    /**
     * Formats in the date's own time zone, so airport-local times stay local.
     *
     * @param CarbonInterface $date
     * @param string $pattern ICU pattern
     * @return string
     */
    private static function pattern(CarbonInterface $date, string $pattern): string
    {
        $key = implode('|', [app()->getLocale(), $date->getTimezone()->getName(), $pattern]);
        $formatter = self::$dateFormatters[$key] ??= new IntlDateFormatter(
            app()->getLocale(), IntlDateFormatter::NONE, IntlDateFormatter::NONE, $date->getTimezone(), null, $pattern,
        );

        return $formatter->format($date) ?: $date->toDateString();
    }
}
