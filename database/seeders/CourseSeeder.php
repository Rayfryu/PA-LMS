<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::where('is_active', true)->get();
        $classes = SchoolClass::with('academicYear')->get();

        if ($subjects->isEmpty() || $classes->isEmpty()) {
            return;
        }

        foreach ($classes as $class) {
            foreach ($subjects->take(3) as $subject) {
                Course::firstOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'school_class_id' => $class->id,
                        'academic_year_id' => $class->academic_year_id,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}