<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PalletizingReceipt;
use Illuminate\Auth\Access\HandlesAuthorization;

class PalletizingReceiptPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PalletizingReceipt');
    }

    public function view(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('View:PalletizingReceipt');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PalletizingReceipt');
    }

    public function update(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('Update:PalletizingReceipt');
    }

    public function delete(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('Delete:PalletizingReceipt');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PalletizingReceipt');
    }

    public function restore(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('Restore:PalletizingReceipt');
    }

    public function forceDelete(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('ForceDelete:PalletizingReceipt');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PalletizingReceipt');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PalletizingReceipt');
    }

    public function replicate(AuthUser $authUser, PalletizingReceipt $palletizingReceipt): bool
    {
        return $authUser->can('Replicate:PalletizingReceipt');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PalletizingReceipt');
    }

}