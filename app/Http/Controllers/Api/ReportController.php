<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Support\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function wasteSummary(): JsonResponse
    {
        return ApiResponse::success('Waste summary report', $this->reportService->getWasteSummary());
    }

    public function paymentSummary(): JsonResponse
    {
        return ApiResponse::success('Payment summary report', $this->reportService->getPaymentSummary());
    }

    public function householdHistory(string $id): JsonResponse
    {
        return ApiResponse::success('Household history report', $this->reportService->getHouseholdHistory($id));
    }
}
