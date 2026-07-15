<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            '_id'          => (string) $this->getKey(),
            'id'           => (string) $this->getKey(),
            'household_id' => $this->household_id,
            'amount'       => $this->amount,
            'status'       => $this->status,
            'payment_date' => $this->payment_date?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
