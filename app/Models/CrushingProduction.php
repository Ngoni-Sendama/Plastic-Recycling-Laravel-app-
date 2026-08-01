<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\CrushingProductionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'batch_number',
    'material_intake_id',
    'grn_reference',
    'material_id',
    'input_weight_kg',
    'output_chips_kg',
    'loss_kg',
    'loss_percentage',
    'recorded_by_user_id',
])]
class CrushingProduction extends Model
{
    /** @use HasFactory<CrushingProductionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'input_weight_kg' => 'decimal:3',
            'output_chips_kg' => 'decimal:3',
            'loss_kg' => 'decimal:3',
            'loss_percentage' => 'decimal:4',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $crushingProduction): void {
            if ($crushingProduction->filled('batch_number') === false) {
                $crushingProduction->batch_number = DocumentNumberGenerator::generate($crushingProduction, 'batch_number', 'CR-BATCH', $crushingProduction->date);
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function materialIntake(): BelongsTo
    {
        return $this->belongsTo(MaterialIntake::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }
}
