<?php

namespace App\Repositories\Contracts;

interface ReportRepositoryInterface
{
    public function getWasteSummary(): array;
    public function getPaymentSummary(): array;
    public function getHouseholdHistory(string $householdId): array;
}
