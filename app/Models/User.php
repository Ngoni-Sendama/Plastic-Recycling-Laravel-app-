<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'avatar_url', 'locale', 'theme_color', 'custom_fields'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function materialIntakes(): HasMany
    {
        return $this->hasMany(MaterialIntake::class, 'recorded_by_user_id');
    }

    public function crushingProductions(): HasMany
    {
        return $this->hasMany(CrushingProduction::class, 'recorded_by_user_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class, 'recorded_by_user_id');
    }

    public function palletizingReceipts(): HasMany
    {
        return $this->hasMany(PalletizingReceipt::class, 'recorded_by_user_id');
    }

    public function palletizingProductions(): HasMany
    {
        return $this->hasMany(PalletizingProduction::class, 'recorded_by_user_id');
    }

    public function pelletSales(): HasMany
    {
        return $this->hasMany(PelletSale::class, 'recorded_by_user_id');
    }

    public function cashRemittances(): HasMany
    {
        return $this->hasMany(CashRemittance::class, 'recorded_by_user_id');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');
        return $this->$avatarColumn ? Storage::url($this->$avatarColumn) : null;
    }
}
