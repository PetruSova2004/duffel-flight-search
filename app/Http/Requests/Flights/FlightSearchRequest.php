<?php

namespace App\Http\Requests\Flights;

use App\Rules\CityInCountry;
use App\Services\Flights\SearchCriteria;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Search parameters on GET /flights. Without any of them the page just shows the form.
 */
class FlightSearchRequest extends FormRequest
{
    public const int MAX_DAYS_AHEAD = 330;

    public const array FIELDS = ['departure_date', 'origin_country', 'origin', 'destination_country', 'destination'];

    /** @var string */
    protected $redirectRoute = 'flights.index';

    /**
     * @return bool
     */
    public function isSearch(): bool
    {
        return $this->hasAny(self::FIELDS);
    }

    /**
     * @return void
     */
    public function validateResolved(): void
    {
        if ($this->isSearch()) {
            parent::validateResolved();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'departure_date' => [
                'bail', 'required', 'string', 'date_format:Y-m-d',
                'after_or_equal:'.self::minDate()->toDateString(),
                'before_or_equal:'.self::maxDate()->toDateString(),
            ],
            'origin_country' => ['required', 'string', 'size:2'],
            'origin' => ['bail', 'required', 'string', 'size:3', new CityInCountry('origin_country')],
            'destination_country' => ['required', 'string', 'size:2'],
            'destination' => ['bail', 'required', 'string', 'size:3', 'different:origin', new CityInCountry('destination_country')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'departure_date.after_or_equal' => __('flights.validation.date_past'),
            'departure_date.before_or_equal' => __('flights.validation.date_too_far', [
                'date' => Format::date(self::maxDate()),
            ]),
            'destination.different' => __('flights.validation.same_city'),
        ];
    }

    /**
     * @return SearchCriteria
     */
    public function criteria(): SearchCriteria
    {
        return new SearchCriteria(
            departureDate: $this->date('departure_date', '!Y-m-d')?->toImmutable()
                ?? throw new \LogicException('Departure date must be validated first'),
            origin: $this->string('origin')->value(),
            destination: $this->string('destination')->value(),
        );
    }

    /**
     * @return CarbonImmutable
     */
    public static function minDate(): CarbonImmutable
    {
        return CarbonImmutable::today();
    }

    /**
     * @return CarbonImmutable
     */
    public static function maxDate(): CarbonImmutable
    {
        return CarbonImmutable::today()->addDays(self::MAX_DAYS_AHEAD);
    }

    /**
     * Codes are compared upper-case; non-strings are left for the "string" rule.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach (['origin_country', 'origin', 'destination_country', 'destination'] as $key) {
            $value = $this->input($key);

            if (is_string($value)) {
                $this->merge([$key => strtoupper(trim($value))]);
            }
        }
    }
}
