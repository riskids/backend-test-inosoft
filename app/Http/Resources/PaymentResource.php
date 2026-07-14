<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->_id,
            'household_id' => $this->household_id,
            'amount'       => $this->amount,
            'status'       => $this->status,
            'payment_date' => $this->payment_date,
            'created_at'   => $this->created_at,
        ];
    }
}
