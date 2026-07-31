<?php

namespace App\Models;

use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name'])]
class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    public function materialIntakes(): HasMany
    {
        return $this->hasMany(MaterialIntake::class);
    }

    public function crushingProductions(): HasMany
    {
        return $this->hasMany(CrushingProduction::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function palletizingReceipts(): HasMany
    {
        return $this->hasMany(PalletizingReceipt::class);
    }
}
