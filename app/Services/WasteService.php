<?php

namespace App\Services;

use App\Models\Waste;
use App\Models\Household;
use App\Repositories\Contracts\WasteRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Exceptions\Domain\UnpaidPaymentExistsException;
use App\Exceptions\Domain\InvalidPickupStatusException;
use App\Exceptions\Domain\SafetyCheckRequiredException;
use DateTimeInterface;

class WasteService
{
    public function __construct(
        protected WasteRepositoryInterface $wasteRepo,
        protected PaymentRepositoryInterface $paymentRepo,
        protected WasteFactory $wasteFactory,
        protected PaymentService $paymentService
    ) {}

    public function createPickup(string $householdId, string $type, array $attributes): Waste
    {
        if ($this->paymentRepo->hasUnpaid($householdId)) {
            throw new UnpaidPaymentExistsException();
        }

        $waste = $this->wasteFactory->make($type, array_merge($attributes, [
            'household_id' => $householdId,
            'status'       => 'pending'
        ]));

        return $this->wasteRepo->create($waste->toArray());
    }

    public function schedule(string $id, DateTimeInterface $date): Waste
    {
        $waste = $this->wasteRepo->findOrFail($id);

        if ($waste->status !== 'pending') {
            throw new InvalidPickupStatusException('Only pending pickups can be scheduled.');
        }

        if ($waste->requiresPreScheduleCheck() && ! $waste->passesPreScheduleCheck()) {
            throw new SafetyCheckRequiredException('Safety check required before scheduling.');
        }

        return $this->wasteRepo->update($waste, [
            'status' => 'scheduled',
            'pickup_date' => $date
        ]);
    }

    public function complete(string $id): Waste
    {
        $waste = $this->wasteRepo->findOrFail($id);

        if ($waste->status !== 'scheduled') {
            throw new InvalidPickupStatusException('Only scheduled pickups can be completed.');
        }

        $waste = $this->wasteRepo->update($waste, ['status' => 'completed']);
        $this->paymentService->createFromCompletedWaste($waste);

        return $waste;
    }

    public function cancel(string $id): Waste
    {
        $waste = $this->wasteRepo->findOrFail($id);

        if ($waste->status === 'completed') {
            throw new InvalidPickupStatusException('Completed pickups cannot be canceled.');
        }

        return $this->wasteRepo->update($waste, ['status' => 'canceled']);
    }
}
