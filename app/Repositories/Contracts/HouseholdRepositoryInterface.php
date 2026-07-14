<?php

namespace App\Repositories\Contracts;

use App\Models\Household;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HouseholdRepositoryInterface
{
    /**
     * Paginated listing with optional search + filter.
     *
     * @param array{
     *   search?: string|null,
     *   block?: string|null,
     *   no?: string|null,
     *   per_page?: int,
     *   page?: int
     * } $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function find(string $id): ?Household;

    /** @throws \Illuminate\Database\Eloquent\ModelNotFoundException */
    public function findOrFail(string $id): Household;

    public function create(array $data): Household;

    public function update(Household $household, array $data): Household;

    public function delete(Household $household): bool;
}
