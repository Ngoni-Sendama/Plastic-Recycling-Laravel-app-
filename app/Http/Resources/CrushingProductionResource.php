<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrushingProductionResource extends JsonResource
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
            'material_intake_id' => $this->material_intake_id,
            'grn_reference' => $this->grn_reference,
            'material_id' => $this->material_id,
            'material' => $this->whenLoaded('material', fn () => $this->material->code),
            'input_weight_kg' => (float) $this->input_weight_kg,
            'output_chips_kg' => (float) $this->output_chips_kg,
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
