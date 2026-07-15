<?php

use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\PickupController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api by bootstrap/app.php → withRouting(api:).
|
*/

Route::prefix('v1')->group(function () {
    // Public: authentication
    Route::post('auth/login', [AuthController::class, 'login']);

    // Public: households (no auth required)
    Route::apiResource('households', HouseholdController::class);

    // Protected: pickups and payments require JWT
    Route::middleware('auth:api')->group(function () {
        Route::post('pickups', [PickupController::class, 'store']);
        Route::put('pickups/{id}/schedule', [PickupController::class, 'schedule']);
        Route::put('pickups/{id}/complete', [PickupController::class, 'complete']);
        Route::put('pickups/{id}/cancel', [PickupController::class, 'cancel']);

        Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);
        Route::put('payments/{id}/confirm', [PaymentController::class, 'confirm']);
    });

    // Public: reports
    Route::prefix('reports')->group(function () {
        Route::get('waste-summary', [App\Http\Controllers\Api\ReportController::class, 'wasteSummary']);
        Route::get('payment-summary', [App\Http\Controllers\Api\ReportController::class, 'paymentSummary']);
        Route::get('households/{id}/history', [App\Http\Controllers\Api\ReportController::class, 'householdHistory']);
    });
});
