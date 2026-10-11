<?php

namespace Tests\Feature\Academic;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['slug' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['slug' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['slug' => 'student']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);

        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active]);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        $subject = Subject::factory()->create(['is_active' => true]);

        $this->course = Course::create([
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_assign_eligible_teacher_to_course()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.assign-teacher', $this->course), [
                'teacher_id' => $this->teacher->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('courses', [
            'id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
        ]);
    }

    public function test_user_without_teacher_role_cannot_be_assigned()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.assign-teacher', $this->course), [
                'teacher_id' => $this->student->id,
            ]);

        $response->assertSessionHasErrors('teacher_id');
        $this->assertDatabaseMissing('courses', [
            'id' => $this->course->id,
            'teacher_id' => $this->student->id,
        ]);
    }

    public function test_non_admin_cannot_mutate_assignments()
    {
        $this->actingAs($this->teacher)
            ->put(route('admin.courses.assign-teacher', $this->course), [
                'teacher_id' => $this->teacher->id,
            ])
            ->assertForbidden();
    }

    public function test_assignment_changes_rejected_in_archived_year()
    {
        $this->course->schoolClass->academicYear->update(['status' => AcademicYearStatus::Archived]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.assign-teacher', $this->course), [
                'teacher_id' => $this->teacher->id,
            ]);

        $response->assertSessionHasErrors('teacher_id');
    }

    public function test_existing_assignment_can_be_cleared()
    {
        $this->course->update(['teacher_id' => $this->teacher->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.assign-teacher', $this->course), [
                'teacher_id' => null,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $this->course->id,
            'teacher_id' => null,
        ]);
    }
}