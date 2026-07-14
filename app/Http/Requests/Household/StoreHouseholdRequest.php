<?php

namespace App\Http\Requests\Household;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth (JWT) will be bolted on in Day 3 per the plan.
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['required', 'string', 'max:120'],
            'address'    => ['required', 'string', 'max:255'],
            'block'      => ['nullable', 'string', 'max:16'],
            'no'         => ['nullable', 'string', 'max:16'],
        ];
    }
}
