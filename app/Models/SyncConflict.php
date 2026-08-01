<?php

namespace App\Models;

use Database\Factories\SyncConflictFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'table_name',
    'record_id',
    'local_id',
    'submitted_by_user_id',
    'server_version',
    'submitted_version',
    'server_payload',
    'submitted_payload',
    'changed_fields',
    'status',
    'resolution',
    'resolved_by_user_id',
    'resolved_at',
])]
class SyncConflict extends Model
{
    /** @use HasFactory<SyncConflictFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'server_payload' => 'array',
            'submitted_payload' => 'array',
            'changed_fields' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function submittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
