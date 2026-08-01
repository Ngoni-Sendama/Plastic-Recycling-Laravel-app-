<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPermission
{
    /**
     * Allow the request only when the authenticated user holds the given Shield permission.
     *
     * The check goes through Spatie's hasPermissionTo(), which is also satisfied by
     * Shield's super_admin role (it holds every permission and bypasses the gate).
     *
     * @param  string  $permission  e.g. "Create:MaterialIntake"
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // super_admin bypasses the permission check (mirrors Shield's Gate::before),
        // so the bypass stays explicit even if its permission set is ever trimmed.
        // The roles relation is used instead of hasRole() to avoid a
        // RoleDoesNotExist exception when the role row is absent (fresh DB).
        $isSuperAdmin = $user !== null
            && $user->roles->pluck('name')->contains(config('filament-shield.super_admin.name', 'super_admin'));

        try {
            $allowed = $isSuperAdmin || ($user !== null && $user->hasPermissionTo($permission));
        } catch (PermissionDoesNotExist) {
            $allowed = false;
        }

        if (! $allowed) {
            return response()->json([
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
