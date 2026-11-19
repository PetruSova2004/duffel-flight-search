<?php

namespace Tests\Feature\Duffel;

use App\Models\User;
use App\Services\Duffel\PlaceDirectory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\Concerns\FakesDuffel;
use Tests\TestCase;

class PlaceDirectoryTest extends TestCase
{
    use FakesDuffel;
    use RefreshDatabase;

    public function test_it_groups_airports_into_countries_and_cities(): void
    {
        $this->fakeDuffel();

        $places = app(PlaceDirectory::class);

        $this->assertSame(['GB' => 'Великобритания', 'US' => 'Соединенные Штаты', 'UA' => 'Украина'], $places->countries());
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
        $this->fakeDuffel();

        app(PlaceDirectory::class)->countries();
        app()->forgetScopedInstances();
        app(PlaceDirectory::class)->cities('UA');

        Http::assertSentCount(1);
    }

    public function test_warm_command_fills_the_cache(): void
    {
        $this->fakeDuffel();

        $this->assertSame(0, Artisan::call('duffel:warm-places'));
        $this->assertStringContainsString('Cached 3 countries and 5 cities.', Artisan::output());
    }

    public function test_cities_endpoint_returns_cities_for_country(): void
    {
        $this->fakeDuffel();

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
}
