<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\DispatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'dispatch_note_number',
    'crushing_production_id',
    'batch_reference',
    'material_id',
    'weight_dispatched_kg',
    'transported_by',
    'recorded_by_user_id',
])]
class Dispatch extends Model
{
    /** @use HasFactory<DispatchFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_dispatched_kg' => 'decimal:3',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $dispatch): void {
            if (blank($dispatch->dispatch_note_number)) {
                $dispatch->dispatch_note_number = DocumentNumberGenerator::generate($dispatch, 'dispatch_note_number', 'DN', $dispatch->date);
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function crushingProduction(): BelongsTo
    {
        return $this->belongsTo(CrushingProduction::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function palletizingReceipts(): HasMany
    {
        return $this->hasMany(PalletizingReceipt::class);
    }
}
