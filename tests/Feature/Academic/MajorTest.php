<?php

namespace Tests\Feature\Academic;

use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MajorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_major(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/majors', [
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => null,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/majors');
        $this->assertDatabaseHas('majors', ['code' => 'RPL']);
    }

    public function test_teacher_cannot_create_major(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->post('/admin/majors', [
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
        ])->assertForbidden();
    }

    public function test_student_cannot_create_major(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->post('/admin/majors', [
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
        ])->assertForbidden();
    }

    public function test_admin_can_update_major(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put("/admin/majors/{$major->id}", [
            'code' => $major->code,
            'name' => 'New Name',
            'description' => null,
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/majors');
        $this->assertDatabaseHas('majors', ['id' => $major->id, 'name' => 'New Name']);
    }

    public function test_code_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Major::factory()->create(['code' => 'TKJ']);

        $this->actingAs($admin)->post('/admin/majors', [
            'code' => 'TKJ',
            'name' => 'Some Other Name',
        ])->assertSessionHasErrors('code');
    }

    public function test_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Major::factory()->create(['name' => 'Teknik Jaringan Komputer']);

        $this->actingAs($admin)->post('/admin/majors', [
            'code' => 'ZZZ',
            'name' => 'Teknik Jaringan Komputer',
        ])->assertSessionHasErrors('name');
    }

    public function test_admin_can_deactivate_major(): void
    {
        $admin = User::factory()->admin()->create();
        $major = Major::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->put("/admin/majors/{$major->id}", [
            'code' => $major->code,
            'name' => $major->name,
            'description' => null,
            'is_active' => false,
        ])->assertRedirect('/admin/majors');

        $this->assertFalse($major->fresh()->is_active);
    }
}