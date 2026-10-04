<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_not_stored_as_plaintext(): void
    {
        $user = User::factory()->student()->create([
            'password' => 'plain-text-password',
        ]);

        $this->assertNotEquals('plain-text-password', $user->fresh()->password);
        $this->assertTrue(str_starts_with($user->fresh()->password, '$2y$'));
    }

    public function test_authenticated_user_prop_does_not_leak_sensitive_fields(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertInertia(fn ($page) => $page
            ->has('auth.user.id')
            ->has('auth.user.name')
            ->has('auth.user.email')
            ->has('auth.user.role')
            ->missing('auth.user.password')
            ->missing('auth.user.remember_token')
        );
    }

    public function test_unauthorized_role_access_is_rejected_on_the_backend_not_just_hidden_in_the_frontend(): void
    {
        $student = User::factory()->student()->create();

        // Directly hitting the URL (not navigating via UI) must still be blocked server-side.
        $this->actingAs($student)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }
}
