<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrushingProductionRequest extends FormRequest
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
            'batch_number' => ['required', 'string', 'max:255'],
            'material_intake_id' => ['nullable', 'integer', 'exists:material_intakes,id'],
            'grn_reference' => ['nullable', 'string', 'max:255'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
            'input_weight_kg' => ['required', 'numeric', 'min:0'],
            'output_chips_kg' => ['required', 'numeric', 'min:0'],
        ];
    }
}
