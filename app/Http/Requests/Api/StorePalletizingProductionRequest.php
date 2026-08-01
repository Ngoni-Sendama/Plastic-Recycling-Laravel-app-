<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePalletizingProductionRequest extends FormRequest
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
            'palletizing_receipt_id' => ['nullable', 'integer', 'exists:palletizing_receipts,id'],
            'grn_reference' => ['nullable', 'string', 'max:255'],
            'chips_input_kg' => ['required', 'numeric', 'min:0'],
            'pellets_output_kg' => ['required', 'numeric', 'min:0'],
        ];
    }
}
