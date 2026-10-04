<?php

namespace Database\Factories;

use App\Enums\AcademicYearStatus;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2015, 2100);

        return [
            'name' => "{$startYear}/".($startYear + 1),
            'start_date' => "{$startYear}-07-01",
            'end_date' => ($startYear + 1).'-06-30',
            'status' => AcademicYearStatus::Draft,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => AcademicYearStatus::Draft]);
    }

    public function active(): static
    {
        return $this->state(['status' => AcademicYearStatus::Active]);
    }

    public function archived(): static
    {
        return $this->state(['status' => AcademicYearStatus::Archived]);
    }
}