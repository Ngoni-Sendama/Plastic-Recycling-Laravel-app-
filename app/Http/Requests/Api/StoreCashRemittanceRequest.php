<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRemittanceRequest extends FormRequest
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
            'period_covered' => ['nullable', 'string', 'max:255'],
            'chips_delivered_kg' => ['required', 'numeric', 'min:0'],
            'recovery_price_per_kg' => ['required', 'numeric', 'min:0'],
            'sales_revenue' => ['required', 'numeric', 'min:0'],
            'cash_remitted' => ['required', 'numeric', 'min:0'],
        ];
    }
}
