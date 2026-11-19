<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('flights.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_returns_to_intended_page_after_login(): void
    {
        $user = User::factory()->create();

        $this->get(route('password.edit'))->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('password.edit'));
    }

    public function test_remember_me_sets_recaller_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertCookie(auth()->guard()->getRecallerName());
    }

    public function test_wrong_password_and_unknown_email_get_same_error(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-pass1'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->post(route('login.store'), ['email' => 'nobody@example.com', 'password' => 'wrong-pass1'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_login_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-pass1']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_log_out_via_post_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/logout')->assertNotFound();
        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
