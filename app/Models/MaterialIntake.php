<?php

namespace App\Models;

use App\Services\DocumentNumberGenerator;
use Database\Factories\MaterialIntakeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'date',
    'grn_number',
    'buyer_name',
    'material_id',
    'gross_weight_kg',
    'tare_weight_kg',
    'net_weight_kg',
    'unit_price',
    'total_value',
    'recorded_by_user_id',
])]
class MaterialIntake extends Model
{
    /** @use HasFactory<MaterialIntakeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Default attributes for new instances (DB also defaults this column).
     */
    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'gross_weight_kg' => 'decimal:3',
            'tare_weight_kg' => 'decimal:3',
            'net_weight_kg' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'lock_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $materialIntake): void {
            if (blank($materialIntake->grn_number)) {
                $materialIntake->grn_number = DocumentNumberGenerator::generate($materialIntake, 'grn_number', 'GRN', $materialIntake->date);
            }
        });
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function crushingProductions(): HasMany
    {
        return $this->hasMany(CrushingProduction::class);
    }
}
