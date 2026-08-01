<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PalletizingReceiptResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'grn_number' => $this->grn_number,
            'dispatch_id' => $this->dispatch_id,
            'dispatch_reference' => $this->dispatch_reference,
            'material_id' => $this->material_id,
            'material' => $this->whenLoaded('material', fn () => $this->material->code),
            'weight_received_kg' => (float) $this->weight_received_kg,
            'rate_per_kg' => (float) $this->rate_per_kg,
            'amount_payable' => (float) $this->amount_payable,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
