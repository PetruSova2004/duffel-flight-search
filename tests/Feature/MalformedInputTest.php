<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\FakesDuffel;
use Tests\TestCase;

class MalformedInputTest extends TestCase
{
    use FakesDuffel;
    use RefreshDatabase;

    public function test_array_email_on_login_is_a_validation_error(): void
    {
        $this->post(route('login.store'), ['email' => ['a@b.c'], 'password' => 'x'])
            ->assertSessionHasErrors('email');
    }

    public function test_array_email_on_register_is_a_validation_error(): void
    {
        $this->post(route('register.store'), ['name' => 'A', 'email' => ['a@b.c'], 'password' => 'secret123', 'password_confirmation' => 'secret123'])
            ->assertSessionHasErrors('email');
    }

    public function test_array_current_password_is_a_validation_error(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('password.update'), ['current_password' => ['x'], 'password' => 'newSecret42', 'password_confirmation' => 'newSecret42'])
            ->assertSessionHasErrors('current_password');
    }

    public function test_array_search_fields_are_a_validation_error(): void
    {
        $this->fakeDuffel();

        $this->actingAs(User::factory()->create())
            ->get(route('flights.index', ['origin_country' => ['GB'], 'origin' => ['LON'], 'departure_date' => ['x']]))
            ->assertRedirect(route('flights.index'))
            ->assertSessionHasErrors(['origin_country', 'origin', 'departure_date']);

        $this->get(route('flights.index'))->assertOk();
    }
}
