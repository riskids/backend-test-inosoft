<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function hasUnpaid(string $householdId): bool
    {
        return Payment::where('household_id', $householdId)
            ->where('status', 'pending')
            ->exists();
    }

    public function create(array $attributes): Payment
    {
        return Payment::create($attributes);
    }

    public function findOrFail(string $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function update(Payment $payment, array $attributes): Payment
    {
        $payment->update($attributes);
        return $payment->fresh();
    }
}
