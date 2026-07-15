<?php

namespace Database\Factories;

use App\Models\Waste;
use Illuminate\Database\Eloquent\Factories\Factory;

class WasteFactory extends Factory
{
    protected $model = Waste::class;

    public function definition(): array
    {
        return [
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => fake()->randomElement(['organic', 'plastic', 'paper', 'electronic']),
            'status' => 'pending',
            'pickup_date' => null,
            'safety_check' => fake()->boolean(),
        ];
    }

    public function organic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'organic',
            'safety_check' => false,
        ]);
    }

    public function plastic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'plastic',
            'safety_check' => false,
        ]);
    }

    public function paper(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'paper',
            'safety_check' => false,
        ]);
    }

    public function electronic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'electronic',
            'safety_check' => false,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'pickup_date' => now()->addDay(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'pickup_date' => now()->subDay(),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }

    public function withSafetyCheck(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_check' => true,
        ]);
    }
}