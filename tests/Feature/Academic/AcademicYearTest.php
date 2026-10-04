<?php

namespace Tests\Feature\Academic;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_academic_year(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/academic-years', [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
        ]);

        $response->assertRedirect('/admin/academic-years');
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026/2027',
            'status' => 'draft',
        ]);
    }

    public function test_teacher_cannot_create_academic_year(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->post('/admin/academic-years', [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
        ])->assertForbidden();
    }

    public function test_student_cannot_create_academic_year(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post('/admin/academic-years', [
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
        ])->assertForbidden();
    }

    public function test_admin_can_update_draft_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->draft()->create(['name' => '2030/2031']);

        $response = $this->actingAs($admin)->put("/admin/academic-years/{$year->id}", [
            'name' => '2030/2031 Revised',
            'start_date' => $year->start_date->toDateString(),
            'end_date' => $year->end_date->toDateString(),
        ]);

        $response->assertRedirect('/admin/academic-years');
        $this->assertDatabaseHas('academic_years', ['id' => $year->id, 'name' => '2030/2031 Revised']);
    }

    public function test_admin_can_activate_draft_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->draft()->create();

        $this->actingAs($admin)->post("/admin/academic-years/{$year->id}/activate")
            ->assertRedirect('/admin/academic-years');

        $this->assertSame('active', $year->fresh()->status->value);
    }

    public function test_cannot_activate_second_active_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicYear::factory()->active()->create();
        $draft = AcademicYear::factory()->draft()->create();

        $this->actingAs($admin)->post("/admin/academic-years/{$draft->id}/activate")
            ->assertSessionHasErrors('status');

        $this->assertSame('draft', $draft->fresh()->status->value);
    }

    public function test_admin_can_archive_active_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create();

        $this->actingAs($admin)->post("/admin/academic-years/{$year->id}/archive")
            ->assertRedirect('/admin/academic-years');

        $this->assertSame('archived', $year->fresh()->status->value);
    }

    public function test_cannot_reactivate_archived_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->archived()->create();

        $this->actingAs($admin)->post("/admin/academic-years/{$year->id}/activate")
            ->assertSessionHasErrors('status');

        $this->assertSame('archived', $year->fresh()->status->value);
    }

    public function test_cannot_move_archived_back_to_draft(): void
    {
        // There is no route/action that moves a year back to draft at all —
        // this proves the state machine offers no such transition, not just
        // that one specific endpoint rejects it.
        $year = AcademicYear::factory()->archived()->create();

        $this->assertFalse(
            $year->status->canTransitionTo(\App\Enums\AcademicYearStatus::Draft)
        );
    }

    public function test_draft_can_be_archived_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->draft()->create();

        $this->actingAs($admin)->post("/admin/academic-years/{$year->id}/archive")
            ->assertRedirect('/admin/academic-years');

        $this->assertSame('archived', $year->fresh()->status->value);
    }

    public function test_start_date_must_be_before_end_date(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/academic-years', [
            'name' => '2040/2041',
            'start_date' => '2041-06-30',
            'end_date' => '2040-07-01',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_academic_year_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicYear::factory()->create(['name' => '2050/2051']);

        $this->actingAs($admin)->post('/admin/academic-years', [
            'name' => '2050/2051',
            'start_date' => '2050-07-01',
            'end_date' => '2051-06-30',
        ])->assertSessionHasErrors('name');
    }
}