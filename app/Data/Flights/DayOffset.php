<?php

namespace App\Data\Flights;

final class DayOffset
{
    /**
     * @param FlightPoint $from
     * @param FlightPoint $to
     * @return int Difference of local calendar dates in days
     */
    public static function between(FlightPoint $from, FlightPoint $to): int
    {
        $fromDate = $from->at->format('Y-m-d');
        $toDate = $to->at->format('Y-m-d');

        return (int) (new \DateTimeImmutable($fromDate))->diff(new \DateTimeImmutable($toDate))->format('%r%a');
    }
}
