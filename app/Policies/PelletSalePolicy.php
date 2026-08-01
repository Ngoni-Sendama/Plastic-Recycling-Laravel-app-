<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PelletSale;
use Illuminate\Auth\Access\HandlesAuthorization;

class PelletSalePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PelletSale');
    }

    public function view(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('View:PelletSale');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PelletSale');
    }

    public function update(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('Update:PelletSale');
    }

    public function delete(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('Delete:PelletSale');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PelletSale');
    }

    public function restore(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('Restore:PelletSale');
    }

    public function forceDelete(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('ForceDelete:PelletSale');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PelletSale');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PelletSale');
    }

    public function replicate(AuthUser $authUser, PelletSale $pelletSale): bool
    {
        return $authUser->can('Replicate:PelletSale');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PelletSale');
    }

}