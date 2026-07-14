<?php

namespace App\Services;

use App\Repositories\Contracts\ReportRepositoryInterface;

class ReportService
{
    public function __construct(
        protected ReportRepositoryInterface $reportRepo
    ) {}

    public function getWasteSummary(): array
    {
        return $this->reportRepo->getWasteSummary();
    }

    public function getPaymentSummary(): array
    {
        return $this->reportRepo->getPaymentSummary();
    }

    public function getHouseholdHistory(string $householdId): array
    {
        return $this->reportRepo->getHouseholdHistory($householdId);
    }
}
