<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CrushingProduction;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrushingProductionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CrushingProduction');
    }

    public function view(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('View:CrushingProduction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CrushingProduction');
    }

    public function update(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('Update:CrushingProduction');
    }

    public function delete(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('Delete:CrushingProduction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CrushingProduction');
    }

    public function restore(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('Restore:CrushingProduction');
    }

    public function forceDelete(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('ForceDelete:CrushingProduction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CrushingProduction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CrushingProduction');
    }

    public function replicate(AuthUser $authUser, CrushingProduction $crushingProduction): bool
    {
        return $authUser->can('Replicate:CrushingProduction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CrushingProduction');
    }

}