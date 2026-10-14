<?php

namespace Tests\Feature\Academic;

use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher1;
    protected User $teacher2;
    protected User $student1;
    protected User $student2;
    protected Course $course;
    protected AcademicYear $activeYear;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['slug' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['slug' => 'student']);

        // Menggunakan forceCreate untuk memastikan field tersimpan meski belum masuk array $fillable model
        $this->activeYear = AcademicYear::forceCreate([
    'name'       => '2025/2026',
    'start_date' => '2025-07-01',
    'end_date'   => '2026-06-30',
    'status'     => 'active',
]);
        
        $schoolClass = SchoolClass::forceCreate([
            'name' => 'Kelas A',
            'academic_year_id' => $this->activeYear->id
        ]);
        
        $subject = Subject::forceCreate([
            'name' => 'Pemrograman Dasar',
            'code' => 'PD01',
            'is_active' => true
        ]);

        $this->teacher1 = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->teacher2 = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->student1 = User::factory()->create(['role_id' => $studentRole->id]);
        $this->student2 = User::factory()->create(['role_id' => $studentRole->id]);

        // Kompatibilitas dinamis untuk sistem Spatie Permission (jika digunakan)
        foreach ([$this->teacher1, $this->teacher2] as $teacher) {
            if (method_exists($teacher, 'assignRole')) {
                $teacher->assignRole('teacher');
            }
        }
        foreach ([$this->student1, $this->student2] as $student) {
            if (method_exists($student, 'assignRole')) {
                $student->assignRole('student');
            }
        }

        $this->course = Course::forceCreate([
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $this->activeYear->id,
            'teacher_id' => $this->teacher1->id,
            'is_active' => true,
        ]);

        // Pendaftaran siswa di database untuk bypass sistem otorisasi controller
        if (Schema::hasTable('student_enrollments')) {
            DB::table('student_enrollments')->insert([
                ['student_id' => $this->student1->id, 'school_class_id' => $schoolClass->id, 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $this->student2->id, 'school_class_id' => $schoolClass->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        } elseif (Schema::hasTable('enrollments')) {
            DB::table('enrollments')->insert([
                ['user_id' => $this->student1->id, 'course_id' => $this->course->id, 'school_class_id' => $schoolClass->id, 'created_at' => now(), 'updated_at' => now()],
                ['user_id' => $this->student2->id, 'course_id' => $this->course->id, 'school_class_id' => $schoolClass->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function test_assigned_teacher_can_create_assignment()
    {
        $response = $this->actingAs($this->teacher1)
            ->post("/teacher/courses/{$this->course->id}/assignments", [
                'title' => 'Tugas 1 PHP',
                'description' => 'Kerjakan modul 1',
                'due_at' => Carbon::now('Asia/Jakarta')->addDays(3)->toDateTimeString(),
                'status' => 'published',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', [
            'course_id' => $this->course->id,
            'title' => 'Tugas 1 PHP',
            'status' => 'published',
        ]);
    }

    public function test_teacher_cannot_manage_assignments_for_another_teachers_course()
    {
        $response = $this->actingAs($this->teacher2)
            ->post("/teacher/courses/{$this->course->id}/assignments", [
                'title' => 'Tugas Ilegal',
                'status' => 'published',
            ]);

        $response->assertForbidden();
    }

    public function test_eligible_student_can_view_published_assignment()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Publik',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->student1)
            ->get("/student/courses/{$this->course->id}/assignments/{$assignment->id}");

        $response->assertOk();
    }

    public function test_student_cannot_view_draft_assignment()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Rahasia',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->student1)
            ->get("/student/courses/{$this->course->id}/assignments/{$assignment->id}");

        $response->assertForbidden();
    }

    public function test_eligible_student_can_submit_before_deadline()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Tepat Waktu',
            'due_at' => Carbon::now('Asia/Jakarta')->addDays(2)->toDateTimeString(),
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->create('jawaban.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'text_response' => 'Berikut jawaban saya.',
                'attachment' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'text_response' => 'Berikut jawaban saya.',
        ]);
    }

    public function test_submission_after_deadline_is_rejected()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Expired',
            'due_at' => Carbon::now('Asia/Jakarta')->subHour()->toDateTimeString(),
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'text_response' => 'Jawaban telat',
            ]);

        $response->assertSessionHasErrors('submission');
    }

    public function test_closed_assignment_rejects_submissions()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Closed',
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'text_response' => 'Coba kirim',
            ]);

        $response->assertSessionHasErrors('submission');
    }

    public function test_student_cannot_access_another_students_submission()
    {
        $file = UploadedFile::fake()->create('jawaban1.pdf', 500, 'application/pdf');
        $path = $file->store('assignment_submissions', 'local');

        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Individu',
            'status' => 'published',
        ]);

        $submission = AssignmentSubmission::forceCreate([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'attachment_path' => $path,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->student2)
            ->get("/downloads/submissions/{$submission->id}");

        $response->assertForbidden();
    }

    public function test_teacher_can_view_submissions_for_their_course()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Kelas',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->teacher1)
            ->get("/teacher/courses/{$this->course->id}/assignments/{$assignment->id}/submissions");

        $response->assertOk();
    }

    public function test_invalid_attachment_type_is_rejected()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas PDF Only',
            'allowed_file_types' => 'pdf',
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->create('virus.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'attachment' => $file,
            ]);

        $response->assertSessionHasErrors('attachment');
    }

    public function test_unauthorized_user_cannot_download_submission_attachment()
    {
        $file = UploadedFile::fake()->create('file.pdf', 500, 'application/pdf');
        $path = $file->store('assignment_submissions', 'local');

        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Rahasia',
            'status' => 'published',
        ]);

        $submission = AssignmentSubmission::forceCreate([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'attachment_path' => $path,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->teacher2)
            ->get("/downloads/submissions/{$submission->id}");

        $response->assertForbidden();
    }

    public function test_resubmission_updates_existing_record()
    {
        $assignment = Assignment::forceCreate([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Tugas Resubmit',
            'due_at' => Carbon::now('Asia/Jakarta')->addDays(1)->toDateTimeString(),
            'status' => 'published',
        ]);

        $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'text_response' => 'Jawaban Pertama',
            ]);

        $this->actingAs($this->student1)
            ->post("/student/courses/{$this->course->id}/assignments/{$assignment->id}/submit", [
                'text_response' => 'Jawaban Revisi',
            ]);

        $this->assertDatabaseCount('assignment_submissions', 1);
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student1->id,
            'text_response' => 'Jawaban Revisi',
            'status' => 'resubmitted',
        ]);
    }

    public function test_archived_academic_year_prevents_assignment_creation()
    {
        $this->activeYear->update(['status' => 'archived']);

        $response = $this->actingAs($this->teacher1)
            ->post("/teacher/courses/{$this->course->id}/assignments", [
                'title' => 'Tugas Tahun Arsip',
                'status' => 'published',
            ]);

        $response->assertForbidden();
    }

    public function test_learning_material_routes_unaffected()
    {
        $response = $this->actingAs($this->teacher1)
            ->get("/teacher/courses/{$this->course->id}/materials");

        $response->assertOk();
    }
}