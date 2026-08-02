<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogWriter
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function write(
        string $action,
        Model $auditable,
        string $description,
        array $oldValues = [],
        array $newValues = [],
    ): void {
        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'source' => $this->source($request),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    public function writeForDeletion(string $action, Model $auditable, string $description, array $oldValues = []): void
    {
        $this->write($action, $auditable, $description, $oldValues, []);
    }

    private function source(?Request $request): ?string
    {
        if ($request === null) {
            return null;
        }

        if ($request->is('api/*')) {
            return 'mobile_api';
        }

        return 'web';
    }
}
