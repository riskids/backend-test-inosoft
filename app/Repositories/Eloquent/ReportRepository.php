<?php

namespace App\Repositories\Eloquent;

use App\Models\Waste;
use App\Models\Payment;
use App\Models\Household;
use App\Repositories\Contracts\ReportRepositoryInterface;
use MongoDB\Laravel\Eloquent\Model;

class ReportRepository implements ReportRepositoryInterface
{
    public function getWasteSummary(): array
    {
        return Waste::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => ['type' => '$type', 'status' => '$status'],
                        'count' => ['$sum' => 1]
                    ]
                ]
            ])->toArray();
        });
    }

    public function getPaymentSummary(): array
    {
        return Payment::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$status',
                        'count' => ['$sum' => 1],
                        'total_amount' => ['$sum' => '$amount']
                    ]
                ]
            ])->toArray();
        });
    }

    public function getHouseholdHistory(string $householdId): array
    {
        $household = Household::findOrFail($householdId);

        return [
            'household' => $household,
            'pickups' => $household->wastes,
            'payments' => $household->payments
        ];
    }
}
