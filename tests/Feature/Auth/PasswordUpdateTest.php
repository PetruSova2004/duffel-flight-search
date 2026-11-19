<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'newSecret42',
                'password_confirmation' => 'newSecret42',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('newSecret42', $user->fresh()->password));
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'wrong-pass1',
                'password' => 'newSecret42',
                'password_confirmation' => 'newSecret42',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_differ_from_current(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'secret123',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors('password');
    }
}
