<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PalletizingProductionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'batch_number' => $this->batch_number,
            'palletizing_receipt_id' => $this->palletizing_receipt_id,
            'grn_reference' => $this->grn_reference,
            'chips_input_kg' => (float) $this->chips_input_kg,
            'pellets_output_kg' => (float) $this->pellets_output_kg,
            'loss_kg' => (float) $this->loss_kg,
            'loss_percentage' => (float) $this->loss_percentage,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
