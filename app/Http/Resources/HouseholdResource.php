<?php

namespace App\Http\Resources;

use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Household
 */
class HouseholdResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            '_id'        => (string) $this->getKey(),
            'id'         => (string) $this->getKey(),
            'owner_name' => $this->owner_name,
            'address'    => $this->address,
            'block'      => $this->block,
            'no'         => $this->no,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
