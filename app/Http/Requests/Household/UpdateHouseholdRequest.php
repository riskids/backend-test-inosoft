<?php

namespace App\Http\Requests\Household;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'owner_name' => ['sometimes', 'string', 'max:120'],
            'address'    => ['sometimes', 'string', 'max:255'],
            'block'      => ['sometimes', 'nullable', 'string', 'max:16'],
            'no'         => ['sometimes', 'nullable', 'string', 'max:16'],
        ];
    }
}
