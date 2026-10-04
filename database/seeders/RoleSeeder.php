<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['slug' => Role::ADMIN, 'name' => 'Admin'],
            ['slug' => Role::TEACHER, 'name' => 'Teacher'],
            ['slug' => Role::STUDENT, 'name' => 'Student'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name']],
            );
        }
    }
}
