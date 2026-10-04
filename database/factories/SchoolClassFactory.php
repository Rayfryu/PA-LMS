<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'major_id' => Major::factory(),
            'grade_level' => fake()->randomElement(GradeLevel::cases()),
            'name' => fake()->unique()->numerify('Class ##'),
            'homeroom_teacher_id' => null,
            'capacity' => fake()->numberBetween(20, 36),
        ];
    }

    public function forAcademicYear(AcademicYear $academicYear): static
    {
        return $this->state(['academic_year_id' => $academicYear->id]);
    }

    public function forMajor(Major $major): static
    {
        return $this->state(['major_id' => $major->id]);
    }

    public function withHomeroomTeacher(User $teacher): static
    {
        return $this->state(['homeroom_teacher_id' => $teacher->id]);
    }
}