<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreDispatchRequest extends FormRequest
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
            'crushing_production_id' => ['nullable', 'integer', 'exists:crushing_productions,id'],
            'batch_reference' => ['nullable', 'string', 'max:255'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
            'weight_dispatched_kg' => ['required', 'numeric', 'min:0'],
            'transported_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
