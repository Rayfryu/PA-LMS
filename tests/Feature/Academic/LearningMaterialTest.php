<?php

namespace Tests\Feature\Academic;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Enums\AcademicYearStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LearningMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher1;
    protected User $teacher2;
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['slug' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'student'], ['slug' => 'student']);

        $this->teacher1 = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->teacher2 = User::factory()->create(['role_id' => $teacherRole->id]);
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);

        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active ?? 'active']);
        $schoolClass = SchoolClass::factory()->create(['academic_year_id' => $academicYear->id]);
        $subject = Subject::factory()->create(['is_active' => true]);

        $this->course = Course::create([
            'subject_id' => $subject->id,
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $this->teacher1->id,
            'is_active' => true,
        ]);
    }

    public function test_assigned_teacher_can_create_learning_material_with_attachment()
    {
        $file = UploadedFile::fake()->create('modul.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->teacher1)
            ->post("/teacher/courses/{$this->course->id}/materials", [
                'title' => 'Modul 1 Pemrograman',
                'description' => 'Pengenalan Laravel',
                'attachment' => $file,
                'status' => 'published',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('learning_materials', [
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Modul 1 Pemrograman',
            'status' => 'published',
        ]);

        $material = LearningMaterial::first();
        Storage::disk('local')->assertExists($material->attachment_path);
    }

    public function test_teacher_cannot_manage_another_teachers_course_material()
    {
        $response = $this->actingAs($this->teacher2)
            ->post("/teacher/courses/{$this->course->id}/materials", [
                'title' => 'Materi Bajakan',
                'status' => 'draft',
            ]);

        $response->assertForbidden();
    }

    public function test_student_can_download_published_material()
    {
        $file = UploadedFile::fake()->create('modul.pdf', 500, 'application/pdf');
        $path = $file->store('learning_materials', 'local');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Materi Terbit',
            'attachment_path' => $path,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->student)
            ->get("/downloads/materials/{$material->id}");

        $response->assertOk();
    }

    public function test_student_cannot_download_draft_material()
    {
        $file = UploadedFile::fake()->create('draft.pdf', 500, 'application/pdf');
        $path = $file->store('learning_materials', 'local');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Materi Draft',
            'attachment_path' => $path,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->student)
            ->get("/downloads/materials/{$material->id}");

        $response->assertForbidden();
    }

    public function test_invalid_attachment_type_is_rejected()
    {
        $file = UploadedFile::fake()->create('script.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($this->teacher1)
            ->post("/teacher/courses/{$this->course->id}/materials", [
                'title' => 'Materi Executable',
                'attachment' => $file,
                'status' => 'draft',
            ]);

        $response->assertSessionHasErrors('attachment');
    }

    public function test_teacher_can_delete_material_and_its_file()
    {
        $file = UploadedFile::fake()->create('modul.pdf', 500, 'application/pdf');
        $path = $file->store('learning_materials', 'local');

        $material = LearningMaterial::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher1->id,
            'title' => 'Materi Hapus',
            'attachment_path' => $path,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->teacher1)
            ->delete("/teacher/courses/{$this->course->id}/materials/{$material->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($path);
    }
}