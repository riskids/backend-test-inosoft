<?php

namespace App\Repositories\Contracts;

use App\Models\Waste;

interface WasteRepositoryInterface
{
    public function findOrFail(string $id): Waste;
    public function create(array $attributes): Waste;
    public function update(Waste $waste, array $attributes): Waste;
    public function delete(string $id): bool;
}
