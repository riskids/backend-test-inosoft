<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pickup\StorePickupRequest;
use App\Http\Resources\WasteResource;
use App\Http\Support\ApiResponse;
use App\Services\WasteService;
use Illuminate\Http\Request;

class PickupController extends Controller
{
    public function __construct(
        protected WasteService $wasteService
    ) {}

    public function store(StorePickupRequest $request)
    {
        $waste = $this->wasteService->createPickup(
            $request->household_id,
            $request->type,
            $request->only(['safety_check'])
        );
        return ApiResponse::success(new WasteResource($waste), 'Pickup created', 201);
    }

    public function schedule(Request $request, string $id)
    {
        $waste = $this->wasteService->schedule($id, now()->addDay());
        return ApiResponse::success(new WasteResource($waste), 'Pickup scheduled');
    }

    public function complete(string $id)
    {
        $waste = $this->wasteService->complete($id);
        return ApiResponse::success(new WasteResource($waste), 'Pickup completed');
    }

    public function cancel(string $id)
    {
        $waste = $this->wasteService->cancel($id);
        return ApiResponse::success(new WasteResource($waste), 'Pickup canceled');
    }
}
