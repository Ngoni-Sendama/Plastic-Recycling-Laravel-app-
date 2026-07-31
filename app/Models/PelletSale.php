<?php

namespace App\Models;

use Database\Factories\PelletSaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'date',
    'receipt_number',
    'customer_name',
    'kg_sold',
    'unit_price',
    'amount_received',
    'recorded_by_user_id',
])]
class PelletSale extends Model
{
    /** @use HasFactory<PelletSaleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'kg_sold' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'amount_received' => 'decimal:2',
        ];
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
