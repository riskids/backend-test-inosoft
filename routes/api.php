<?php

use App\Http\Controllers\Api\HouseholdController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api by bootstrap/app.php → withRouting(api:).
| Today (Day 1) only the Household CRUD is wired. Pickups, payments and
| reports land in Day 2 / Day 3.
|
*/

Route::prefix('v1')->group(function () {
    Route::apiResource('households', HouseholdController::class);
});
