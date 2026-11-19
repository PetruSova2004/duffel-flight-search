<?php

namespace App\Rules;

use App\Services\Duffel\DuffelException;
use App\Services\Duffel\PlaceDirectory;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * The city code must exist in our directory for the country chosen in another field.
 */
class CityInCountry implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    /**
     * @param string $countryField
     */
    public function __construct(private readonly string $countryField) {}

    /**
     * @param array<string, mixed> $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $country = $this->data[$this->countryField] ?? null;

        if (! is_string($value) || ! is_string($country)) {
            return;
        }

        try {
            if (! app(PlaceDirectory::class)->hasCity($country, $value)) {
                $fail('validation.city_in_country')->translate();
            }
        } catch (DuffelException $e) {
            $fail($e->userMessage());
        }
    }
}
