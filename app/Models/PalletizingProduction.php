<?php

namespace App\Models;

use Database\Factories\PalletizingProductionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'chips_input_kg' => 'decimal:3',
            'pellets_output_kg' => 'decimal:3',
            'loss_kg' => 'decimal:3',
            'loss_percentage' => 'decimal:4',
        ];
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
