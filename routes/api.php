<?php

use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\PickupController;
use App\Http\Controllers\Api\PaymentController;
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
    Route::apiResource('households', HouseholdController::class);

    Route::post('pickups', [PickupController::class, 'store']);
    Route::put('pickups/{id}/schedule', [PickupController::class, 'schedule']);
    Route::put('pickups/{id}/complete', [PickupController::class, 'complete']);
    Route::put('pickups/{id}/cancel', [PickupController::class, 'cancel']);

    Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);
    Route::put('payments/{id}/confirm', [PaymentController::class, 'confirm']);
});
