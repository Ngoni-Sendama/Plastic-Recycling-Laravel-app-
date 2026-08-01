<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CashRemittance;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashRemittancePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashRemittance');
    }

    public function view(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('View:CashRemittance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashRemittance');
    }

    public function update(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('Update:CashRemittance');
    }

    public function delete(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('Delete:CashRemittance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CashRemittance');
    }

    public function restore(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('Restore:CashRemittance');
    }

    public function forceDelete(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('ForceDelete:CashRemittance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CashRemittance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CashRemittance');
    }

    public function replicate(AuthUser $authUser, CashRemittance $cashRemittance): bool
    {
        return $authUser->can('Replicate:CashRemittance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CashRemittance');
    }

}