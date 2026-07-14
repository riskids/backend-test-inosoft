<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Household\StoreHouseholdRequest;
use App\Http\Requests\Household\UpdateHouseholdRequest;
use App\Http\Resources\HouseholdResource;
use App\Http\Support\ApiResponse;
use App\Services\HouseholdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public function __construct(
        private readonly HouseholdService $householdService,
    ) {}

    /**
     * GET /api/households
     * Query params: search, block, no, per_page, page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'block', 'no', 'per_page', 'page']);
        $paginator = $this->householdService->list($filters);

        return ApiResponse::success([
            'items'      => HouseholdResource::collection($paginator->getCollection())->resolve(),
            'pagination' => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
                'pages'    => $paginator->lastPage(),
            ],
        ], 'Households retrieved successfully');
    }

    /**
     * POST /api/households
     */
    public function store(StoreHouseholdRequest $request): JsonResponse
    {
        $household = $this->householdService->create($request->validated());

        return ApiResponse::created(
            (new HouseholdResource($household))->resolve(),
            'Household created successfully'
        );
    }

    /**
     * GET /api/households/{id}
     */
    public function show(string $id): JsonResponse
    {
        $household = $this->householdService->find($id);

        return ApiResponse::success(
            (new HouseholdResource($household))->resolve(),
            'Household retrieved successfully'
        );
    }

    /**
     * PUT/PATCH /api/households/{id}
     */
    public function update(UpdateHouseholdRequest $request, string $id): JsonResponse
    {
        $household = $this->householdService->update($id, $request->validated());

        return ApiResponse::success(
            (new HouseholdResource($household))->resolve(),
            'Household updated successfully'
        );
    }

    /**
     * DELETE /api/households/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->householdService->delete($id);

        return ApiResponse::noContent();
    }
}
