<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialIntakeResource extends JsonResource
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
            'buyer_id' => $this->buyer_id,
            'buyer_name' => $this->buyer_name,
            'buyer' => $this->whenLoaded('buyer', fn () => [
                'id' => $this->buyer->id,
                'buyer_name' => $this->buyer->buyer_name,
                'contact_number' => $this->buyer->contact_number,
            ]),
            'material_id' => $this->material_id,
            'material' => $this->whenLoaded('material', fn () => $this->material->code),
            'gross_weight_kg' => (float) $this->gross_weight_kg,
            'tare_weight_kg' => (float) $this->tare_weight_kg,
            'net_weight_kg' => (float) $this->net_weight_kg,
            'unit_price' => (float) $this->unit_price,
            'total_value' => (float) $this->total_value,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
