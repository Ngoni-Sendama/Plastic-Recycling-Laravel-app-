<?php

namespace App\Models;

use Database\Factories\BuyerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['buyer_name', 'contact_number', 'lock_version'])]
class Buyer extends Model
{
    /** @use HasFactory<BuyerFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = ['lock_version' => 1];

    protected function casts(): array
    {
        return [
            'lock_version' => 'integer',
        ];
    }

    public function materialIntakes(): HasMany
    {
        return $this->hasMany(MaterialIntake::class);
    }
}
