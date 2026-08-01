<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
            'gross_weight_kg' => ['required', 'numeric', 'min:0'],
            'tare_weight_kg' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
