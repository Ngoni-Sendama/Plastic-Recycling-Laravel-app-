<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'source' => $this->source,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'username' => $this->user?->username,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
