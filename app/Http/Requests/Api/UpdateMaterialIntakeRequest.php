<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialIntakeRequest extends FormRequest
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
            'buyer_id' => ['required', 'integer', 'exists:buyers,id'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
            'gross_weight_kg' => ['required', 'numeric', 'min:0'],
            'tare_weight_kg' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
