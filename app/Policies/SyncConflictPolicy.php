<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SyncConflict;
use Illuminate\Auth\Access\HandlesAuthorization;

class SyncConflictPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SyncConflict');
    }

    public function view(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('View:SyncConflict');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SyncConflict');
    }

    public function update(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('Update:SyncConflict');
    }

    public function delete(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('Delete:SyncConflict');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SyncConflict');
    }

    public function restore(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('Restore:SyncConflict');
    }

    public function forceDelete(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('ForceDelete:SyncConflict');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SyncConflict');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SyncConflict');
    }

    public function replicate(AuthUser $authUser, SyncConflict $syncConflict): bool
    {
        return $authUser->can('Replicate:SyncConflict');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SyncConflict');
    }

}