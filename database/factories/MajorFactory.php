<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Major>
 */
class MajorFactory extends Factory
{
    protected $model = Major::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => $name,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}