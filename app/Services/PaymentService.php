<?php

namespace App\Services;

use App\Models\Waste;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepo
    ) {}

    public function createFromCompletedWaste(Waste $waste): Payment
    {
        return $this->paymentRepo->create([
            'household_id' => $waste->household_id,
            'amount'       => $waste->completionAmount(),
            'status'       => 'pending',
            'payment_date' => null,
            'source_pickup_id' => $waste->id,
        ]);
    }
}
