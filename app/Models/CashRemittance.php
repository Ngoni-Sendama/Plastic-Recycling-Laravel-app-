<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\CashRemittanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'voucher_number',
    'period_covered',
    'chips_delivered_kg',
    'recovery_price_per_kg',
    'sales_revenue',
    'cash_remitted',
    'max_remittance_due',
    'balance_retained',
    'recorded_by_user_id',
])]
class CashRemittance extends Model
{
    /** @use HasFactory<CashRemittanceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'chips_delivered_kg' => 'decimal:3',
            'recovery_price_per_kg' => 'decimal:2',
            'sales_revenue' => 'decimal:2',
            'cash_remitted' => 'decimal:2',
            'max_remittance_due' => 'decimal:2',
            'balance_retained' => 'decimal:2',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $cashRemittance): void {
            if ($cashRemittance->filled('voucher_number') === false) {
                $cashRemittance->voucher_number = DocumentNumberGenerator::generate($cashRemittance, 'voucher_number', 'REM', $cashRemittance->date);
            }
        });
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
