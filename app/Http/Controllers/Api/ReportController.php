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
        return ApiResponse::success($this->reportService->getWasteSummary(), 'Waste summary report');
    }

    public function paymentSummary(): JsonResponse
    {
        return ApiResponse::success($this->reportService->getPaymentSummary(), 'Payment summary report');
    }

    public function householdHistory(string $id): JsonResponse
    {
        return ApiResponse::success($this->reportService->getHouseholdHistory($id), 'Household history report');
    }
}
