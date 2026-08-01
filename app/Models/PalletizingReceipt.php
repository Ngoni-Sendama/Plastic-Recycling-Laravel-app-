<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\PalletizingReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'grn_number',
    'dispatch_id',
    'dispatch_reference',
    'material_id',
    'weight_received_kg',
    'rate_per_kg',
    'amount_payable',
    'recorded_by_user_id',
])]
class PalletizingReceipt extends Model
{
    /** @use HasFactory<PalletizingReceiptFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_received_kg' => 'decimal:3',
            'rate_per_kg' => 'decimal:2',
            'amount_payable' => 'decimal:2',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $palletizingReceipt): void {
            if (blank($palletizingReceipt->grn_number)) {
                $palletizingReceipt->grn_number = DocumentNumberGenerator::generate($palletizingReceipt, 'grn_number', 'PGRN', $palletizingReceipt->date);
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function palletizingProductions(): HasMany
    {
        return $this->hasMany(PalletizingProduction::class);
    }
}
