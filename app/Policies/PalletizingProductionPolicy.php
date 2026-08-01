<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PalletizingProduction;
use Illuminate\Auth\Access\HandlesAuthorization;

class PalletizingProductionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PalletizingProduction');
    }

    public function view(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('View:PalletizingProduction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PalletizingProduction');
    }

    public function update(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('Update:PalletizingProduction');
    }

    public function delete(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('Delete:PalletizingProduction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PalletizingProduction');
    }

    public function restore(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('Restore:PalletizingProduction');
    }

    public function forceDelete(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('ForceDelete:PalletizingProduction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PalletizingProduction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PalletizingProduction');
    }

    public function replicate(AuthUser $authUser, PalletizingProduction $palletizingProduction): bool
    {
        return $authUser->can('Replicate:PalletizingProduction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PalletizingProduction');
    }

}