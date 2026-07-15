<?php

namespace App\Repositories\Eloquent;

use App\Models\Waste;
use App\Repositories\Contracts\WasteRepositoryInterface;

class WasteRepository implements WasteRepositoryInterface
{
    public function findOrFail(string $id): Waste
    {
        return Waste::findOrFail($id);
    }

    public function create(array $attributes): Waste
    {
        return Waste::create($attributes);
    }

    public function update(Waste $waste, array $attributes): Waste
    {
        $waste->update($attributes);
        $fresh = $waste->fresh();
        return $fresh ?? $waste;
    }

    public function delete(string $id): bool
    {
        return (bool) Waste::destroy($id);
    }
}
