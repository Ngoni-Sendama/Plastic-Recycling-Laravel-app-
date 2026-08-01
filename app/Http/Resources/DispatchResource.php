<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->toDateString(),
            'dispatch_note_number' => $this->dispatch_note_number,
            'crushing_production_id' => $this->crushing_production_id,
            'batch_reference' => $this->batch_reference,
            'material_id' => $this->material_id,
            'material' => $this->whenLoaded('material', fn () => $this->material->code),
            'weight_dispatched_kg' => (float) $this->weight_dispatched_kg,
            'transported_by' => $this->transported_by,
            'recorded_by_user_id' => $this->recorded_by_user_id,
            'recorded_by' => $this->whenLoaded('recordedByUser', fn () => $this->recordedByUser->name),
            'lock_version' => $this->lock_version,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
