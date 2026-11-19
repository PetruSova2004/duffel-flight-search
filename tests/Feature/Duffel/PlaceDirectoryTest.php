<?php

namespace Tests\Feature\Duffel;

use App\Models\User;
use App\Services\Duffel\PlaceDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class PlaceDirectoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    private function fakeAirports(): void
    {
        Http::fake(['api.duffel.com/air/airports*' => Http::response([
            'data' => [
                $this->airport('LHR', 'GB', 'London', ['iata_code' => 'LON', 'name' => 'London']),
                $this->airport('LGW', 'GB', 'London', ['iata_code' => 'LON', 'name' => 'London']),
                $this->airport('ABZ', 'GB', 'Aberdeen'),
                $this->airport('KBP', 'UA', 'Kiev', ['iata_code' => 'IEV', 'name' => 'Kiev']),
                $this->airport('LWO', 'UA', 'Lviv'),
            ],
            'meta' => ['after' => null],
        ])]);
    }

    public function test_it_groups_airports_into_countries_and_cities(): void
    {
        $this->fakeAirports();

        $places = app(PlaceDirectory::class);

        $this->assertSame(['GB' => 'Великобритания', 'UA' => 'Украина'], $places->countries());
        $this->assertSame([
            ['code' => 'ABZ', 'name' => 'Aberdeen'],
            ['code' => 'LON', 'name' => 'London'],
        ], $places->cities('gb'));
        $this->assertTrue($places->hasCity('UA', 'IEV'));
        $this->assertFalse($places->hasCity('UA', 'KBP'));
        $this->assertFalse($places->hasCity('GB', 'IEV'));
    }

    public function test_it_is_cached_between_requests(): void
    {
        $this->fakeAirports();

        app(PlaceDirectory::class)->countries();
        app()->forgetScopedInstances();
        app(PlaceDirectory::class)->cities('UA');

        Http::assertSentCount(1);
    }

    public function test_warm_command_fills_the_cache(): void
    {
        $this->fakeAirports();

        $this->assertSame(0, Artisan::call('duffel:warm-places'));
        $this->assertStringContainsString('Cached 2 countries and 4 cities.', Artisan::output());
    }

    public function test_cities_endpoint_returns_cities_for_country(): void
    {
        $this->fakeAirports();

        $this->actingAs(User::factory()->create())
            ->getJson(route('flights.cities', ['country' => 'UA']))
            ->assertOk()
            ->assertExactJson(['data' => [
                ['code' => 'IEV', 'name' => 'Kiev'],
                ['code' => 'LWO', 'name' => 'Lviv'],
            ]]);
    }

    public function test_cities_endpoint_validates_country(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('flights.cities', ['country' => 'U1']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('country');
    }

    public function test_cities_endpoint_is_for_users_only(): void
    {
        $this->getJson(route('flights.cities', ['country' => 'UA']))->assertUnauthorized();
    }

    public function test_cities_endpoint_reports_duffel_failure(): void
    {
        Sleep::fake();
        Http::fake(['api.duffel.com/*' => Http::response(['errors' => []], 500)]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('flights.cities', ['country' => 'UA']))
            ->assertServiceUnavailable()
            ->assertJsonPath('message', __('duffel.errors.unavailable'));
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
