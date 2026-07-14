<?php

namespace App\Http\Requests\Pickup;

use Illuminate\Foundation\Http\FormRequest;

class StorePickupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => 'required|exists:households,_id',
            'type'         => 'required|in:organic,plastic,paper,electronic',
            'safety_check' => 'boolean',
        ];
    }
}
