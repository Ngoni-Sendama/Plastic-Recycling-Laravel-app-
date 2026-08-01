<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MaterialIntake;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaterialIntakePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MaterialIntake');
    }

    public function view(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('View:MaterialIntake');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MaterialIntake');
    }

    public function update(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('Update:MaterialIntake');
    }

    public function delete(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('Delete:MaterialIntake');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MaterialIntake');
    }

    public function restore(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('Restore:MaterialIntake');
    }

    public function forceDelete(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('ForceDelete:MaterialIntake');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MaterialIntake');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MaterialIntake');
    }

    public function replicate(AuthUser $authUser, MaterialIntake $materialIntake): bool
    {
        return $authUser->can('Replicate:MaterialIntake');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MaterialIntake');
    }

}