<?php

namespace App\Services;

use App\Models\Household;
use App\Repositories\Contracts\HouseholdRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service layer for the Household module.
 *
 * Business rules + orchestration live here. Controllers and Repositories stay
 * framework-/persistence-agnostic respectively. New logic added in Day 1 is
 * deliberately minimal — CRUD only — to keep the foundation working before
 * the heavier pickup/payment rules land in Day 2.
 */
class HouseholdService
{
    public function __construct(
        private readonly HouseholdRepositoryInterface $households,
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->households->paginate($filters);
    }

    public function find(string $id): Household
    {
        return $this->households->findOrFail($id);
    }

    public function create(array $data): Household
    {
        return $this->households->create([
            'owner_name' => $data['owner_name'],
            'address'    => $data['address'],
            'block'      => $data['block'] ?? null,
            'no'         => $data['no'] ?? null,
        ]);
    }

    public function update(string $id, array $data): Household
    {
        $household = $this->households->findOrFail($id);

        $payload = array_intersect_key($data, array_flip([
            'owner_name', 'address', 'block', 'no',
        ]));

        return $this->households->update($household, $payload);
    }

    public function delete(string $id): void
    {
        $household = $this->households->findOrFail($id);
        $this->households->delete($household);
    }
}
