<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'audit_logs' => $this->whenLoaded('auditLogs', function (): array {
                return $this->auditLogs
                    ->take(20)
                    ->map(fn ($log) => [
                        'id' => $log->id,
                        'action' => $log->action,
                        'description' => $log->description,
                        'source' => $log->source,
                        'created_at' => $log->created_at?->toISOString(),
                    ])
                    ->values()
                    ->all();
            }, []),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
