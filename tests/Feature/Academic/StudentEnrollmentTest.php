<?php

namespace Tests\Feature\Academic;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Sertakan 'slug' agar memenuhi NOT NULL constraint tabel roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['slug' => 'admin']
        );

        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            ['slug' => 'student']
        );
        
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);
        
        $academicYear = AcademicYear::factory()->create(['status' => AcademicYearStatus::Active]);
        $this->schoolClass = SchoolClass::factory()->create([
            'academic_year_id' => $academicYear->id,
            'capacity' => 30
        ]);
    }

    public function test_admin_can_enroll_student()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.classes.students.store', $this->schoolClass->id), [
            'user_id' => $this->student->id,
            'enrolled_at' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('class_students', [
            'class_id' => $this->schoolClass->id,
            'user_id' => $this->student->id,
            'status' => 'active',
        ]);
    }

    public function test_cannot_enroll_student_if_capacity_is_full()
    {
        $this->schoolClass->update(['capacity' => 1]);
        
        // Enroll siswa pertama (berhasil)
        $this->actingAs($this->admin)->post(route('admin.classes.students.store', $this->schoolClass->id), [
            'user_id' => $this->student->id,
            'enrolled_at' => now()->format('Y-m-d'),
        ]);

        // Coba enroll siswa kedua
        $student2 = User::factory()->create([
            'role_id' => Role::where('name', 'student')->first()->id
        ]);
        
        $response = $this->actingAs($this->admin)->post(route('admin.classes.students.store', $this->schoolClass->id), [
            'user_id' => $student2->id,
            'enrolled_at' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors(['error' => 'Kapasitas class penuh.']);
    }
}