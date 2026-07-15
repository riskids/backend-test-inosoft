<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

class HouseholdFactory extends Factory
{
    protected $model = Household::class;

    public function definition(): array
    {
        return [
            'owner_name' => fake()->name(),
            'address' => fake()->address(),
            'block' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'no' => (string) fake()->numberBetween(1, 50),
        ];
    }
}