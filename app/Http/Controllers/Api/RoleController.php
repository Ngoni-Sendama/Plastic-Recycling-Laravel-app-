<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    /**
     * List all roles with their permission counts.
     */
    public function index(): JsonResponse
    {
        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions_count,
            ]);

        return response()->json(['data' => $roles]);
    }

    /**
     * Show a single role with its assigned permissions.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions:id,name');

        return response()->json([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ],
        ]);
    }

    /**
     * List all permissions grouped by resource.
     */
    public function permissions(): JsonResponse
    {
        $all = Permission::orderBy('name')->pluck('name')->values()->all();

        $grouped = [];
        foreach ($all as $perm) {
            [$action, $resource] = explode(':', $perm, 2) + [$perm, ''];
            $grouped[$resource][] = $perm;
        }

        return response()->json(['data' => $grouped]);
    }
}
