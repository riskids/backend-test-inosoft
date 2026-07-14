<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function hasUnpaid(string $householdId): bool;
    public function create(array $attributes): Payment;
    public function findOrFail(string $id): Payment;
    public function update(Payment $payment, array $attributes): Payment;
}
