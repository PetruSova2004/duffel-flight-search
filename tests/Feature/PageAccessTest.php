<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_guest_actions(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertDontSee(route('flights.index'));
    }

    public function test_home_page_shows_search_action_for_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('flights.index'))
            ->assertSee($user->name);
    }

    public function test_guest_is_redirected_from_flights_to_login(): void
    {
        $this->get(route('flights.index'))->assertRedirect(route('login'));
    }

    public function test_user_is_redirected_from_guest_pages_to_flights(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('login'))->assertRedirect(route('flights.index'));
        $this->actingAs($user)->get(route('register'))->assertRedirect(route('flights.index'));
    }

    public function test_unknown_page_returns_styled_404(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee(__('app.errors.404.title'))
            ->assertSee(route('home'));
    }
}
