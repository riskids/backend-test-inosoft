<?php

namespace App\Repositories\Eloquent;

use App\Models\Household;
use App\Repositories\Contracts\HouseholdRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * Mongo/Eloquent implementation of HouseholdRepositoryInterface.
 *
 * Repositories hold PERSISTENCE ONLY. No business rules live here.
 */
class HouseholdRepository implements HouseholdRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Household::query();

        if (! empty($filters['search'])) {
            $needle = preg_quote($filters['search'], '/');
            $query->where('owner_name', 'regexp', "/{$needle}/i");
        }

        if (! empty($filters['block'])) {
            $query->where('block', $filters['block']);
        }

        if (! empty($filters['no'])) {
            $query->where('no', $filters['no']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(string $id): ?Household
    {
        $id = $this->normalizeId($id);

        return Household::find($id);
    }

    public function findOrFail(string $id): Household
    {
        $id = $this->normalizeId($id);
        $household = Household::find($id);

        if (! $household) {
            throw (new ModelNotFoundException())->setModel(Household::class, [$id]);
        }

        return $household;
    }

    public function create(array $data): Household
    {
        $household = new Household($data);
        $household->save();

        return $household;
    }

    public function update(Household $household, array $data): Household
    {
        $household->fill($data);
        $household->save();

        return $household;
    }

    public function delete(Household $household): bool
    {
        return (bool) $household->delete();
    }

    /**
     * Mongo stores IDs as ObjectId. Accept either a 24-char hex string or
     * an ObjectId instance and return the canonical ObjectId.
     */
    private function normalizeId(string $id): string|ObjectId
    {
        if ($id instanceof ObjectId) {
            return $id;
        }

        if (preg_match('/^[a-f0-9]{24}$/i', $id) === 1) {
            return new ObjectId($id);
        }

        // Fall through with the raw id; Eloquent will throw on lookup.
        return $id;
    }
}
