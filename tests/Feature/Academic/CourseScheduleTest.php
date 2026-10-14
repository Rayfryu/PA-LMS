<?php

namespace Tests\Feature\Academic;

use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\AcademicYear;
use App\Enums\AcademicYearStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected Course $course;
    protected Course $otherCourse;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['slug' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['slug' => 'teacher']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->teacher = User::factory()->create(['role_id' => $teacherRole->id]);

        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active]);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        $otherClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        
        $subject = Subject::factory()->create(['is_active' => true]);

        // Course 1
        $this->course = Course::create([
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);

        // Course 2 (beda kelas, tapi guru sama)
        $this->otherCourse = Course::create([
            'subject_id' => $subject->id,
            'school_class_id' => $otherClass->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $this->teacher->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_valid_schedule()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.schedules.store', $this->course), [
                'day_of_week' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '09:30',
                'room' => 'A1',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('course_schedules', [
            'course_id' => $this->course->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
        ]);
    }

    public function test_overlapping_schedule_for_same_teacher_is_rejected()
    {
        // Buat jadwal untuk guru di course 1
        CourseSchedule::create([
            'course_id' => $this->course->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // Coba buat jadwal menumpuk untuk guru yang sama di course 2
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.schedules.store', $this->otherCourse), [
                'day_of_week' => 'Monday',
                'start_time' => '09:00', // Overlap
                'end_time' => '11:00',
            ]);

        $response->assertSessionHasErrors(['time']);
    }

    public function test_adjacent_non_overlapping_schedules_are_allowed()
    {
        CourseSchedule::create([
            'course_id' => $this->course->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        // Berdampingan (mulai pas jam 10)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.schedules.store', $this->otherCourse), [
                'day_of_week' => 'Senin',
                'start_time' => '10:00',
                'end_time' => '12:00',
            ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_updating_schedule_excludes_itself_from_conflict_checks()
    {
        $schedule = CourseSchedule::create([
            'course_id' => $this->course->id,
            'day_of_week' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.courses.schedules.update', [$this->course, $schedule]), [
                'day_of_week' => 'Senin',
                'start_time' => '08:30', // Berubah sedikit waktunya
                'end_time' => '10:30',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('course_schedules', [
            'id' => $schedule->id,
            'start_time' => '08:30',
        ]);
    }

    public function test_cannot_mutate_schedule_in_archived_year()
    {
        // Ubah tahun ajaran jadi archived
        $this->course->schoolClass->academicYear->update(['status' => AcademicYearStatus::Archived]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.courses.schedules.store', $this->course), [
                'day_of_week' => 'Monday',
                'start_time' => '08:00',
                'end_time' => '10:00',
            ]);

        $response->assertSessionHasErrors(['general']);
    }
}