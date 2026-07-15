<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WasteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            '_id'          => (string) $this->getKey(),
            'id'           => (string) $this->getKey(),
            'household_id' => $this->household_id,
            'type'         => $this->type,
            'status'       => $this->status,
            'pickup_date'  => $this->pickup_date?->toIso8601String(),
            'safety_check' => $this->safety_check,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
