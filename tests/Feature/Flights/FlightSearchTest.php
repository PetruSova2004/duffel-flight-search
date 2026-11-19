<?php

namespace Tests\Feature\Flights;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesDuffel;
use Tests\TestCase;

class FlightSearchTest extends TestCase
{
    use FakesDuffel;
    use RefreshDatabase;

    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_search(): void
    {
        $this->get(route('flights.index', $this->searchQuery()))->assertRedirect(route('login'));
    }

    public function test_form_lists_countries(): void
    {
        $this->fakeDuffel();

        $this->actingAs($this->user)
            ->get(route('flights.index'))
            ->assertOk()
            ->assertSee(__('flights.form.submit'))
            ->assertSee('Великобритания');
    }

    public function test_search_shows_offers_cheapest_first(): void
    {
        $this->fakeDuffel(['api.duffel.com/air/offer_requests*' => Http::response($this->offerRequestFixture(), 201)]);

        $this->actingAs($this->user)
            ->get(route('flights.index', $this->searchQuery()))
            ->assertOk()
            ->assertSeeInOrder(['Iberia', "218,22\u{a0}€", 'Air Transat', "670,26\u{a0}€"])
            ->assertSee(__('flights.offer.direct'))
            ->assertSee('Porter Airlines')
            ->assertSee(trans_choice('flights.results.count', 2));

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/air/offer_requests?return_offers=true')
            && $request['data']['slices'] === [['origin' => 'LON', 'destination' => 'NYC', 'departure_date' => $this->date()]]
            && $request['data']['passengers'] === [['type' => 'adult']]
            && $request['data']['cabin_class'] === 'economy');
    }

    public function test_date_boundaries_are_accepted(): void
    {
        $this->fakeDuffel(['api.duffel.com/air/offer_requests*' => Http::response($this->offerRequestFixture(), 201)]);

        foreach ([0, 330] as $days) {
            $this->actingAs($this->user)
                ->get(route('flights.index', [...$this->searchQuery(), 'departure_date' => today()->addDays($days)->toDateString()]))
                ->assertOk()
                ->assertSessionHasNoErrors();
        }
    }

    public function test_form_shows_error_when_directory_is_down(): void
    {
        Http::fake(['api.duffel.com/*' => Http::response(['errors' => []], 401)]);

        $this->actingAs($this->user)
            ->get(route('flights.index'))
            ->assertOk()
            ->assertSee(__('duffel.errors.unauthorized'));

        Http::assertSentCount(1);
    }

    public function test_all_broken_offers_are_an_error_not_an_empty_result(): void
    {
        $this->fakeDuffel(['api.duffel.com/air/offer_requests*' => Http::response(['data' => ['offers' => [['id' => 'off_1']]]], 201)]);

        $this->actingAs($this->user)
            ->get(route('flights.index', $this->searchQuery()))
            ->assertOk()
            ->assertSee(__('flights.error.title'))
            ->assertDontSee(__('flights.empty.title'));
    }

    public function test_empty_result_has_its_own_screen(): void
    {
        $this->fakeDuffel(['api.duffel.com/air/offer_requests*' => Http::response(['data' => ['offers' => [], 'slices' => []]], 201)]);

        $this->actingAs($this->user)
            ->get(route('flights.index', $this->searchQuery()))
            ->assertOk()
            ->assertSee(__('flights.empty.title'));
    }

    public function test_duffel_failure_shows_friendly_error(): void
    {
        $this->fakeDuffel(['api.duffel.com/air/offer_requests*' => Http::response(['errors' => [['message' => 'Boom']]], 503)]);

        $this->actingAs($this->user)
            ->get(route('flights.index', $this->searchQuery()))
            ->assertOk()
            ->assertSee(__('flights.error.title'))
            ->assertSee(__('duffel.errors.unavailable'))
            ->assertDontSee('Boom');
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function invalidQueries(): array
    {
        return [
            'past date' => [['departure_date' => '2020-01-01'], 'departure_date'],
            'too far' => [['departure_date' => now()->addDays(331)->toDateString()], 'departure_date'],
            'same city' => [['destination_country' => 'GB', 'destination' => 'LON'], 'destination'],
            'city from other country' => [['origin' => 'IEV'], 'origin'],
            'no country' => [['origin_country' => ''], 'origin_country'],
        ];
    }

    /**
     * @param array<string, string> $override
     * @param string $field
     * @return void
     */
    #[DataProvider('invalidQueries')]
    public function test_invalid_search_is_rejected(array $override, string $field): void
    {
        $this->fakeDuffel();

        $this->actingAs($this->user)
            ->from(route('flights.index'))
            ->get(route('flights.index', [...$this->searchQuery(), ...$override]))
            ->assertRedirect(route('flights.index'))
            ->assertSessionHasErrors($field)
            ->assertSessionHasInput('origin', $override['origin'] ?? 'LON');

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'offer_requests'));
    }

    /**
     * @return array<string, string>
     */
    private function searchQuery(): array
    {
        return [
            'departure_date' => $this->date(),
            'origin_country' => 'GB',
            'origin' => 'LON',
            'destination_country' => 'US',
            'destination' => 'NYC',
        ];
    }

    /**
     * @return string
     */
    private function date(): string
    {
        return now()->addDays(30)->toDateString();
    }
}
