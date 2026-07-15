<?php

namespace App\Http\Requests\Pickup;

use App\Models\Household;
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
            'household_id' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! Household::where('_id', $value)->exists()) {
                    $fail('The selected household id is invalid.');
                }
            }],
            'type'         => 'required|in:organic,plastic,paper,electronic',
            'safety_check' => 'boolean',
        ];
    }
}
