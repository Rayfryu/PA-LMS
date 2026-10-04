<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::query()->where('status', 'active')->first();
        $rpl = Major::query()->where('code', 'RPL')->first();
        $homeroomTeacher = User::query()
            ->whereHas('role', fn ($q) => $q->where('slug', Role::TEACHER))
            ->first();

        if (! $activeYear || ! $rpl) {
            $this->command?->warn('ClassSeeder skipped: seed AcademicYearSeeder and MajorSeeder first.');

            return;
        }

        $classes = [
            ['grade_level' => 'X', 'name' => 'X RPL 1'],
            ['grade_level' => 'X', 'name' => 'X RPL 2'],
            ['grade_level' => 'XI', 'name' => 'XI RPL 1'],
            ['grade_level' => 'XI', 'name' => 'XI RPL 2'],
            ['grade_level' => 'XII', 'name' => 'XII RPL 1'],
        ];

        foreach ($classes as $class) {
            SchoolClass::query()->updateOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'major_id' => $rpl->id,
                    'name' => $class['name'],
                ],
                [
                    'grade_level' => $class['grade_level'],
                    'homeroom_teacher_id' => $homeroomTeacher?->id,
                    'capacity' => 32,
                ]
            );
        }
    }
}