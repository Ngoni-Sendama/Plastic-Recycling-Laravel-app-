<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\PelletSaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'kg_sold' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $pelletSale): void {
            if ($pelletSale->filled('receipt_number') === false) {
                $pelletSale->receipt_number = DocumentNumberGenerator::generate($pelletSale, 'receipt_number', 'SALE', $pelletSale->date);
            }
        });
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
