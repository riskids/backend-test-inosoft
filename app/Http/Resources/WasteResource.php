<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WasteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->_id,
            'household_id' => $this->household_id,
            'type'         => $this->type,
            'status'       => $this->status,
            'pickup_date'  => $this->pickup_date,
            'safety_check' => $this->safety_check,
            'created_at'   => $this->created_at,
        ];
    }
}
