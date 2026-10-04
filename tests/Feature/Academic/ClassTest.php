<?php

namespace Tests\Feature\Academic;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    protected function validPayload(array $overrides = []): array
    {
        $year = $overrides['_year'] ?? AcademicYear::factory()->active()->create();
        $major = $overrides['_major'] ?? Major::factory()->create(['is_active' => true]);
        unset($overrides['_year'], $overrides['_major']);

        return array_merge([
            'academic_year_id' => $year->id,
            'major_id' => $major->id,
            'grade_level' => 'X',
            'name' => 'X RPL 1',
            'homeroom_teacher_id' => null,
            'capacity' => 30,
        ], $overrides);
    }

    // --- Authorization -----------------------------------------------

    public function test_admin_can_create_class(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/classes', $this->validPayload());

        $response->assertRedirect('/admin/classes');
        $this->assertDatabaseHas('classes', ['name' => 'X RPL 1']);
    }

    public function test_teacher_cannot_create_class(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->post('/admin/classes', $this->validPayload())
            ->assertForbidden();
    }

    public function test_student_cannot_create_class(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post('/admin/classes', $this->validPayload())
            ->assertForbidden();
    }

    // --- Academic Year state -------------------------------------------

    public function test_cannot_create_class_in_archived_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->archived()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['_year' => $year]))
            ->assertSessionHasErrors('academic_year_id');
    }

    public function test_can_create_class_in_draft_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->draft()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['_year' => $year]))
            ->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('classes', ['academic_year_id' => $year->id]);
    }

    public function test_can_create_class_in_active_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['_year' => $year]))
            ->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('classes', ['academic_year_id' => $year->id]);
    }

    public function test_cannot_move_class_from_archived_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $archivedYear = AcademicYear::factory()->archived()->create();
        $otherYear = AcademicYear::factory()->draft()->create();
        $major = Major::factory()->create(['is_active' => true]);

        $class = SchoolClass::factory()->create([
            'academic_year_id' => $archivedYear->id,
            'major_id' => $major->id,
            'name' => 'XII RPL 1',
        ]);

        $this->actingAs($admin)->put("/admin/classes/{$class->id}", [
            'academic_year_id' => $otherYear->id,
            'major_id' => $major->id,
            'grade_level' => 'XII',
            'name' => 'XII RPL 1',
            'homeroom_teacher_id' => null,
            'capacity' => 30,
        ])->assertSessionHasErrors('academic_year_id');

        $this->assertSame($archivedYear->id, $class->fresh()->academic_year_id);
    }

    public function test_cannot_change_class_academic_year_after_it_becomes_historical(): void
    {
        // Same intent as the previous test, phrased as the spec's own case:
        // a class created while its year was active, whose year is LATER
        // archived, must also reject a move at that point.
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create();
        $otherYear = AcademicYear::factory()->draft()->create();
        $major = Major::factory()->create(['is_active' => true]);

        $class = SchoolClass::factory()->create([
            'academic_year_id' => $year->id,
            'major_id' => $major->id,
            'name' => 'XII RPL 2',
        ]);

        $year->update(['status' => 'archived']);

        $this->actingAs($admin)->put("/admin/classes/{$class->id}", [
            'academic_year_id' => $otherYear->id,
            'major_id' => $major->id,
            'grade_level' => 'XII',
            'name' => 'XII RPL 2',
            'homeroom_teacher_id' => null,
            'capacity' => 30,
        ])->assertSessionHasErrors('academic_year_id');

        $this->assertSame($year->id, $class->fresh()->academic_year_id);
    }

    // --- Major state -----------------------------------------------------

    public function test_cannot_create_class_with_inactive_major(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['_major' => $major]))
            ->assertSessionHasErrors('major_id');
    }

    public function test_can_create_class_with_active_major(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['_major' => $major]))
            ->assertRedirect('/admin/classes');
    }

    // --- Homeroom teacher --------------------------------------------------

    public function test_teacher_can_be_assigned_as_homeroom_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['homeroom_teacher_id' => $teacher->id]))
            ->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('classes', ['homeroom_teacher_id' => $teacher->id]);
    }

    public function test_student_cannot_be_assigned_as_homeroom_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['homeroom_teacher_id' => $student->id]))
            ->assertSessionHasErrors('homeroom_teacher_id');
    }

    public function test_admin_cannot_be_assigned_as_homeroom_teacher(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['homeroom_teacher_id' => $otherAdmin->id]))
            ->assertSessionHasErrors('homeroom_teacher_id');
    }

    public function test_null_homeroom_teacher_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['homeroom_teacher_id' => null]))
            ->assertRedirect('/admin/classes');
    }

    // --- Capacity ------------------------------------------------------

    public function test_capacity_must_be_at_least_one(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['capacity' => 0]))
            ->assertSessionHasErrors('capacity');
    }

    // --- Grade level -----------------------------------------------------

    public function test_invalid_grade_level_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload(['grade_level' => 'Grade 13']))
            ->assertSessionHasErrors('grade_level');
    }

    // --- Uniqueness ------------------------------------------------------

    public function test_same_name_same_academic_year_same_major_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create();
        $major = Major::factory()->create(['is_active' => true]);

        SchoolClass::factory()->create([
            'academic_year_id' => $year->id,
            'major_id' => $major->id,
            'name' => 'XI RPL 1',
        ]);

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload([
                '_year' => $year, '_major' => $major, 'name' => 'XI RPL 1',
            ]))
            ->assertSessionHasErrors('name');
    }

    public function test_same_name_different_academic_year_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();
        $yearA = AcademicYear::factory()->active()->create();
        $yearB = AcademicYear::factory()->draft()->create();
        $major = Major::factory()->create(['is_active' => true]);

        SchoolClass::factory()->create([
            'academic_year_id' => $yearA->id,
            'major_id' => $major->id,
            'name' => 'XI RPL 1',
        ]);

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload([
                '_year' => $yearB, '_major' => $major, 'name' => 'XI RPL 1',
            ]))
            ->assertRedirect('/admin/classes');
    }

    public function test_same_name_different_major_is_allowed(): void
    {
        $admin = User::factory()->admin()->create();
        $year = AcademicYear::factory()->active()->create();
        $majorA = Major::factory()->create(['is_active' => true]);
        $majorB = Major::factory()->create(['is_active' => true]);

        SchoolClass::factory()->create([
            'academic_year_id' => $year->id,
            'major_id' => $majorA->id,
            'name' => 'XI RPL 1',
        ]);

        $this->actingAs($admin)
            ->post('/admin/classes', $this->validPayload([
                '_year' => $year, '_major' => $majorB, 'name' => 'XI RPL 1',
            ]))
            ->assertRedirect('/admin/classes');
    }
}