<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\PalletizingProductionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'batch_number',
    'palletizing_receipt_id',
    'grn_reference',
    'chips_input_kg',
    'pellets_output_kg',
    'loss_kg',
    'loss_percentage',
    'recorded_by_user_id',
])]
class PalletizingProduction extends Model
{
    /** @use HasFactory<PalletizingProductionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'chips_input_kg' => 'decimal:3',
            'pellets_output_kg' => 'decimal:3',
            'loss_kg' => 'decimal:3',
            'loss_percentage' => 'decimal:4',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $palletizingProduction): void {
            if (blank($palletizingProduction->batch_number)) {
                $palletizingProduction->batch_number = DocumentNumberGenerator::generate($palletizingProduction, 'batch_number', 'PL-BATCH', $palletizingProduction->date);
            }
        });
    }

    public function palletizingReceipt(): BelongsTo
    {
        return $this->belongsTo(PalletizingReceipt::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
