<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_teacher_cannot_access_admin_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_teacher_can_access_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get('/teacher/dashboard')
            ->assertOk();
    }

    public function test_student_cannot_access_teacher_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get('/teacher/dashboard')
            ->assertForbidden();
    }

    public function test_student_can_access_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get('/student/dashboard')
            ->assertOk();
    }

    public function test_admin_is_not_automatically_treated_as_teacher_or_student(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/teacher/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/student/dashboard')->assertForbidden();
    }
}
