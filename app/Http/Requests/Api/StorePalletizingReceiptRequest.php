<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePalletizingReceiptRequest extends FormRequest
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
            'grn_number' => ['required', 'string', 'max:255'],
            'dispatch_id' => ['nullable', 'integer', 'exists:dispatches,id'],
            'dispatch_reference' => ['nullable', 'string', 'max:255'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
            'weight_received_kg' => ['required', 'numeric', 'min:0'],
            'rate_per_kg' => ['required', 'numeric', 'min:0'],
        ];
    }
}
