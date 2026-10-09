<?php

namespace Tests\Feature\Academic;

use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
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

    public function test_admin_can_view_subject_list()
    {
        Subject::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.subjects.index'));

        $response->assertOk();
    }

    public function test_admin_can_create_subject_with_normalization()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'name' => ' Pemrograman Web ',
            'code' => ' web101 ',
            'description' => 'Basic web programming.',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', [
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
        ]);
    }

    public function test_duplicate_code_and_name_are_rejected()
    {
        Subject::factory()->create(['name' => 'Database', 'code' => 'DB01']);

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'name' => 'Database',
            'code' => 'DB02',
        ]);
        $response->assertSessionHasErrors('name');

        $response = $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
            'name' => 'New Subject',
            'code' => 'DB01',
        ]);
        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_subject_retaining_own_code()
    {
        $subject = Subject::factory()->create(['name' => 'Original', 'code' => 'ORIG']);

        $response = $this->actingAs($this->admin)->put(route('admin.subjects.update', $subject->id), [
            'name' => 'Updated Name',
            'code' => 'ORIG',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_toggle_subject_status()
    {
        $subject = Subject::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.subjects.toggle-status', $subject->id));
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'is_active' => false]);

        $this->actingAs($this->admin)->post(route('admin.subjects.toggle-status', $subject->id));
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'is_active' => true]);
    }

    public function test_non_admin_cannot_mutate_subjects()
    {
        $subject = Subject::factory()->create();

        $this->actingAs($this->teacher)
            ->post(route('admin.subjects.store'), ['name' => 'Test', 'code' => 'TST'])
            ->assertForbidden();

        $this->actingAs($this->student)
            ->delete(route('admin.subjects.destroy', $subject->id))
            ->assertForbidden();
    }
}