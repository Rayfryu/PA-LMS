<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->student()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->student()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_access_their_protected_dashboard(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)
            ->get('/student/dashboard')
            ->assertOk();
    }

    public function test_guest_cannot_access_a_protected_dashboard(): void
    {
        $this->get('/student/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
