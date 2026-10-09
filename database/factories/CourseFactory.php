<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $schoolClass = SchoolClass::factory()->create();

        return [
            'subject_id' => Subject::factory(),
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $schoolClass->academic_year_id,
            'is_active' => true,
        ];
    }
}