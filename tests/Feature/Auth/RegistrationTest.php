<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => ' Anna@Example.com ',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('flights.index'));
        $this->assertAuthenticated();

        $user = User::firstWhere('email', 'anna@example.com');
        $this->assertNotNull($user);
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'anna@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_password_requires_letters_and_numbers(): void
    {
        foreach (['12345678', 'abcdefgh', 'abc12'] as $password) {
            $this->post(route('register.store'), [
                'name' => 'Анна',
                'email' => 'anna@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertSessionHasErrors('password');
        }

        $this->assertGuest();
    }

    public function test_password_must_be_confirmed(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret124',
        ])->assertSessionHasErrors('password');
    }
}
