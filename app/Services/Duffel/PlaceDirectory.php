<?php

namespace App\Services\Duffel;

use Collator;
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Locale;

/**
 * Countries and cities with airports, built from GET /air/airports and cached.
 *
 * A "city" is the IATA metro area when the airport belongs to one (LON, IEV),
 * otherwise the airport itself — Duffel accepts both codes as origin/destination.
 */
#[Scoped]
class PlaceDirectory
{
    private const string CACHE_KEY = 'duffel:places:v1';

    /** @var array{countries: array<string, string>, cities: array<string, array<string, string>>}|null */
    private ?array $places = null;

    /**
     * @param DuffelClient $client
     * @param int $ttl Cache lifetime in seconds
     */
    public function __construct(
        private readonly DuffelClient $client,
        #[Config('services.duffel.places_ttl')] private readonly int $ttl,
    ) {}

    /**
     * @return array<string, string> Country code => localized name, sorted by name
     * @throws DuffelException
     */
    public function countries(): array
    {
        return $this->places()['countries'];
    }

    /**
     * @param string $countryCode
     * @return list<array{code: string, name: string}>
     * @throws DuffelException
     */
    public function cities(string $countryCode): array
    {
        $cities = $this->places()['cities'][strtoupper($countryCode)] ?? [];

        return array_map(
            fn (string $code, string $name) => ['code' => $code, 'name' => $name],
            array_keys($cities),
            $cities,
        );
    }

    /**
     * @param string $countryCode
     * @param string $cityCode
     * @return bool
     * @throws DuffelException
     */
    public function hasCity(string $countryCode, string $cityCode): bool
    {
        return isset($this->places()['cities'][strtoupper($countryCode)][strtoupper($cityCode)]);
    }

    /**
     * Rebuilds the directory from Duffel and stores it in the cache.
     *
     * @return array{countries: array<string, string>, cities: array<string, array<string, string>>}
     * @throws DuffelException
     */
    public function refresh(): array
    {
        $places = $this->build();

        Cache::put(self::CACHE_KEY, $places, $this->ttl);

        return $this->places = $places;
    }

    /**
     * @return array{countries: array<string, string>, cities: array<string, array<string, string>>}
     * @throws DuffelException
     */
    private function places(): array
    {
        if ($this->places !== null) {
            return $this->places;
        }

        $cached = Cache::get(self::CACHE_KEY);

        if ($this->isValid($cached)) {
            return $this->places = $cached;
        }

        // Lock so that parallel requests on a cold cache do not all hit Duffel
        try {
            Cache::lock(self::CACHE_KEY.':lock', 120)->block(90, function () {
                $cached = Cache::get(self::CACHE_KEY);
                $this->places = $this->isValid($cached) ? $cached : $this->refresh();
            });
        } catch (LockTimeoutException $e) {
            throw new DuffelException(DuffelErrorType::Unavailable, 'Places directory is still being built', null, $e);
        }

        return $this->places ?? $this->refresh();
    }

    /**
     * Guards against an outdated or foreign value under our cache key.
     *
     * @param mixed $value
     * @return bool
     * @phpstan-assert-if-true array{countries: array<string, string>, cities: array<string, array<string, string>>} $value
     */
    private function isValid(mixed $value): bool
    {
        return is_array($value) && is_array($value['countries'] ?? null) && is_array($value['cities'] ?? null);
    }

    /**
     * @return array{countries: array<string, string>, cities: array<string, array<string, string>>}
     * @throws DuffelException
     */
    private function build(): array
    {
        $cities = [];

        foreach ($this->client->paginate('/air/airports') as $airport) {
            $country = $airport['iata_country_code'] ?? null;
            $code = $airport['city']['iata_code'] ?? $airport['iata_code'] ?? null;
            $name = $airport['city']['name'] ?? $airport['city_name'] ?? $airport['name'] ?? null;

            if ($country && $code && $name) {
                $cities[$country][$code] ??= $name;
            }
        }

        if ($cities === []) {
            throw new DuffelException(DuffelErrorType::Unknown, 'Duffel returned no airports');
        }

        $collator = new Collator(app()->getLocale());
        $countries = [];

        foreach ($cities as $country => &$list) {
            $collator->asort($list);
            $countries[$country] = Locale::getDisplayRegion('-'.$country, app()->getLocale()) ?: $country;
        }
        unset($list);

        $collator->asort($countries);

        return ['countries' => $countries, 'cities' => $cities];
    }
}
