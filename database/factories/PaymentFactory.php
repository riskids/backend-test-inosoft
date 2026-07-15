<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'amount' => fake()->numberBetween(50000, 100000),
            'payment_date' => now(),
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function organic(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 50000,
        ]);
    }

    public function electronic(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 100000,
        ]);
    }
}