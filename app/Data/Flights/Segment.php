<?php

namespace App\Data\Flights;

final readonly class Segment
{
    /**
     * @param string $flightNumber e.g. "LH 903"
     * @param Carrier $marketingCarrier
     * @param Carrier|null $operatingCarrier Set only when it differs from the marketing one
     * @param FlightPoint $departure
     * @param FlightPoint $arrival
     * @param int $durationMinutes
     * @param string|null $aircraft
     */
    public function __construct(
        public string $flightNumber,
        public Carrier $marketingCarrier,
        public ?Carrier $operatingCarrier,
        public FlightPoint $departure,
        public FlightPoint $arrival,
        public int $durationMinutes,
        public ?string $aircraft,
    ) {}

    /**
     * @return int Days between local departure and arrival dates (+1 for next day)
     */
    public function arrivalDayOffset(): int
    {
        return DayOffset::between($this->departure, $this->arrival);
    }
}
