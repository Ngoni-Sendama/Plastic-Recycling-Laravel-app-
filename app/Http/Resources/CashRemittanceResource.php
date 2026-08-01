<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRemittanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'voucher_number' => $this->voucher_number,
            'period_covered' => $this->period_covered,
            'chips_delivered_kg' => (float) $this->chips_delivered_kg,
            'recovery_price_per_kg' => (float) $this->recovery_price_per_kg,
            'sales_revenue' => (float) $this->sales_revenue,
            'cash_remitted' => (float) $this->cash_remitted,
            'max_remittance_due' => (float) $this->max_remittance_due,
            'balance_retained' => (float) $this->balance_retained,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
