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

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacher;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['slug' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['slug' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['slug' => 'student']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->teacher = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);
    }

    public function test_admin_can_view_course_list()
    {
        Course::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.courses.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_valid_course_offering()
    {
        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active->value]);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        $subject = Subject::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }

    public function test_inactive_subject_cannot_be_selected_for_new_course()
    {
        $schoolClass = SchoolClass::factory()->create();
        $inactiveSubject = Subject::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'subject_id' => $inactiveSubject->id,
            'school_class_id' => $schoolClass->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('subject_id');
    }

    public function test_cannot_create_course_for_class_in_archived_academic_year()
    {
        $archivedYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Archived->value]);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $archivedYear->id]);
        $subject = Subject::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('school_class_id');
    }

    public function test_duplicate_course_offering_for_same_class_and_academic_year_is_rejected()
    {
        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active->value]);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        $subject = Subject::factory()->create(['is_active' => true]);

        Course::create([
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('subject_id');
    }

    public function test_non_admin_cannot_mutate_courses()
{
    $course = Course::factory()->create();
    $schoolClass = SchoolClass::factory()->create();
    $subject = Subject::factory()->create(['is_active' => true]);

    $this->actingAs($this->teacher)
        ->post(route('admin.courses.store'), [
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'is_active' => true,
        ])
        ->assertForbidden();

    $this->actingAs($this->student)
        ->delete(route('admin.courses.destroy', $course->id))
        ->assertForbidden();
}
}