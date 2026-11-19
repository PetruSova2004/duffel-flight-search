<?php

namespace Tests\Concerns;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

trait FakesDuffel
{
    /**
     * Fakes the airports list plus optional extra stubs (registered first, so they win).
     *
     * @param array<string, mixed> $extra
     * @return Factory
     */
    protected function fakeDuffel(array $extra = []): Factory
    {
        return Http::fake([...$extra, 'api.duffel.com/air/airports*' => Http::response([
            'data' => [
                $this->airport('LHR', 'GB', 'London', ['iata_code' => 'LON', 'name' => 'London']),
                $this->airport('LGW', 'GB', 'London', ['iata_code' => 'LON', 'name' => 'London']),
                $this->airport('ABZ', 'GB', 'Aberdeen'),
                $this->airport('JFK', 'US', 'New York', ['iata_code' => 'NYC', 'name' => 'New York']),
                $this->airport('KBP', 'UA', 'Kiev', ['iata_code' => 'IEV', 'name' => 'Kiev']),
                $this->airport('LWO', 'UA', 'Lviv'),
            ],
            'meta' => ['after' => null],
        ])]);
    }

    /**
     * Trimmed real Duffel response: a connection (Air Transat) and a direct flight (Iberia).
     *
     * @return array<string, mixed>
     */
    protected function offerRequestFixture(): array
    {
        return json_decode((string) file_get_contents(base_path('tests/Fixtures/duffel/offer_request.json')), true);
    }

    /**
     * @param string $code
     * @param string $country
     * @param string $cityName
     * @param array<string, string>|null $city
     * @return array<string, mixed>
     */
    private function airport(string $code, string $country, string $cityName, ?array $city = null): array
    {
        return [
            'iata_code' => $code,
            'iata_country_code' => $country,
            'iata_city_code' => $city['iata_code'] ?? $code,
            'city_name' => $cityName,
            'city' => $city,
            'name' => $cityName.' Airport',
        ];
    }
}
