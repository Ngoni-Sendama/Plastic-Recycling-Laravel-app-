<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncPushRequest extends FormRequest
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
            'changes' => ['required', 'array'],
            'changes.materials' => ['array'],
            'changes.material_intakes' => ['array'],
            'changes.crushing_productions' => ['array'],
            'changes.dispatches' => ['array'],
            'changes.palletizing_receipts' => ['array'],
            'changes.palletizing_productions' => ['array'],
            'changes.pellet_sales' => ['array'],
            'changes.cash_remittances' => ['array'],
        ];
    }
}
