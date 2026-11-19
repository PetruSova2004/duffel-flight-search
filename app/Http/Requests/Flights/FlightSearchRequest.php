<?php

namespace App\Http\Requests\Flights;

use App\Rules\CityInCountry;
use App\Services\Flights\SearchCriteria;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class FlightSearchRequest extends FormRequest
{
    public const int MAX_DAYS_AHEAD = 330;

    /** @var string */
    protected $redirectRoute = 'flights.index';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'departure_date' => [
                'required', 'date_format:Y-m-d',
                'after_or_equal:'.self::minDate()->toDateString(),
                'before_or_equal:'.self::maxDate()->toDateString(),
            ],
            'origin_country' => ['required', 'string', 'size:2'],
            'origin' => ['required', 'string', 'size:3', new CityInCountry('origin_country')],
            'destination_country' => ['required', 'string', 'size:2'],
            'destination' => ['required', 'string', 'size:3', 'different:origin', new CityInCountry('destination_country')],
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
            departureDate: CarbonImmutable::createFromFormat('!Y-m-d', $this->string('departure_date')->value())
                ?: self::minDate(),
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
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(collect(['origin_country', 'origin', 'destination_country', 'destination'])
            ->mapWithKeys(fn (string $key) => [$key => strtoupper(trim($this->string($key)->value()))])
            ->all());
    }
}
