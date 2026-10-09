<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassStudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'class_id' => SchoolClass::factory(),
            'user_id' => User::factory(), // Pastikan di seeder user ini memiliki role student
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
            'left_at' => null,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::Withdrawn,
            'left_at' => now(),
        ]);
    }
    
    public function transferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EnrollmentStatus::Transferred,
            'left_at' => now(),
        ]);
    }
}