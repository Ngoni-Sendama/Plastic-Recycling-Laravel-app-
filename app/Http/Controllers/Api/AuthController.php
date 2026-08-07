<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Log the user in and issue a Sanctum personal access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->string('username'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $this->payloadFor($user),
        ]);
    }

    /**
     * Return the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->load(['auditLogs' => fn ($query) => $query->latest()->limit(20)]);

        return response()->json([
            'data' => $this->payloadFor($user),
        ]);
    }

    /**
     * Revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Build the auth payload the mobile app consumes.
     *
     * @return array<string, mixed>
     */
    private function payloadFor(User $user): array
    {
        $roles = $this->rolesForUser($user);
        $permissions = $this->permissionsForUser($user, $roles);

        if (empty($roles) && filled($user->role)) {
            $roles = [$user->role];
        }

        if (empty($permissions)) {
            $permissions = $this->fallbackPermissionsForRoles($roles, $user->role);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'roles' => $roles,
            'permissions' => $permissions,
            'audit_logs' => $user->relationLoaded('auditLogs') ? $this->auditLogsPayload($user) : [],
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function rolesForUser(User $user): array
    {
        try {
            $roles = $user->getRoleNames()->values()->all();
        } catch (\Throwable) {
            $roles = [];
        }

        if (empty($roles)) {
            $roles = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_type', User::class)
                ->where('model_has_roles.model_id', $user->id)
                ->orderBy('roles.name')
                ->pluck('roles.name')
                ->values()
                ->all();
        }

        if (empty($roles) && filled($user->role)) {
            $roles = [$user->role];
        }

        return $roles;
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function permissionsForUser(User $user, array $roles): array
    {
        try {
            $permissions = $user->getAllPermissions()->pluck('name')->values()->all();
        } catch (\Throwable) {
            $permissions = [];
        }

        if (empty($permissions)) {
            $permissions = DB::table('model_has_permissions')
                ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
                ->where('model_has_permissions.model_type', User::class)
                ->where('model_has_permissions.model_id', $user->id)
                ->orderBy('permissions.name')
                ->pluck('permissions.name')
                ->values()
                ->all();
        }

        if (empty($permissions)) {
            $permissions = $this->fallbackPermissionsForRoles($roles, $user->role);
        }

        return $permissions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function auditLogsPayload(User $user): array
    {
        return $user->auditLogs
            ->take(20)
            ->map(fn ($log): array => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'source' => $log->source,
                'created_at' => $log->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function fallbackPermissionsForRoles(array $roles, ?string $roleColumn): array
    {
        $effectiveRoles = array_filter(array_unique([
            ...$roles,
            $roleColumn,
        ]));

        foreach ($effectiveRoles as $role) {
            $permissions = match ($role) {
                'Admin' => [
                    'ViewAny:Buyer', 'View:Buyer', 'Create:Buyer', 'Update:Buyer', 'Delete:Buyer', 'DeleteAny:Buyer', 'Restore:Buyer', 'ForceDelete:Buyer', 'ForceDeleteAny:Buyer', 'RestoreAny:Buyer', 'Replicate:Buyer', 'Reorder:Buyer',
                    'ViewAny:Material', 'View:Material', 'Create:Material', 'Update:Material', 'Delete:Material', 'DeleteAny:Material', 'Restore:Material', 'ForceDelete:Material', 'ForceDeleteAny:Material', 'RestoreAny:Material', 'Replicate:Material', 'Reorder:Material',
                    'ViewAny:MaterialIntake', 'View:MaterialIntake', 'Create:MaterialIntake', 'Update:MaterialIntake', 'Delete:MaterialIntake', 'DeleteAny:MaterialIntake', 'Restore:MaterialIntake', 'ForceDelete:MaterialIntake', 'ForceDeleteAny:MaterialIntake', 'RestoreAny:MaterialIntake', 'Replicate:MaterialIntake', 'Reorder:MaterialIntake',
                    'ViewAny:CrushingProduction', 'View:CrushingProduction', 'Create:CrushingProduction', 'Update:CrushingProduction', 'Delete:CrushingProduction', 'DeleteAny:CrushingProduction', 'Restore:CrushingProduction', 'ForceDelete:CrushingProduction', 'ForceDeleteAny:CrushingProduction', 'RestoreAny:CrushingProduction', 'Replicate:CrushingProduction', 'Reorder:CrushingProduction',
                    'ViewAny:Dispatch', 'View:Dispatch', 'Create:Dispatch', 'Update:Dispatch', 'Delete:Dispatch', 'DeleteAny:Dispatch', 'Restore:Dispatch', 'ForceDelete:Dispatch', 'ForceDeleteAny:Dispatch', 'RestoreAny:Dispatch', 'Replicate:Dispatch', 'Reorder:Dispatch',
                    'ViewAny:PalletizingReceipt', 'View:PalletizingReceipt', 'Create:PalletizingReceipt', 'Update:PalletizingReceipt', 'Delete:PalletizingReceipt', 'DeleteAny:PalletizingReceipt', 'Restore:PalletizingReceipt', 'ForceDelete:PalletizingReceipt', 'ForceDeleteAny:PalletizingReceipt', 'RestoreAny:PalletizingReceipt', 'Replicate:PalletizingReceipt', 'Reorder:PalletizingReceipt',
                    'ViewAny:PalletizingProduction', 'View:PalletizingProduction', 'Create:PalletizingProduction', 'Update:PalletizingProduction', 'Delete:PalletizingProduction', 'DeleteAny:PalletizingProduction', 'Restore:PalletizingProduction', 'ForceDelete:PalletizingProduction', 'ForceDeleteAny:PalletizingProduction', 'RestoreAny:PalletizingProduction', 'Replicate:PalletizingProduction', 'Reorder:PalletizingProduction',
                    'ViewAny:PelletSale', 'View:PelletSale', 'Create:PelletSale', 'Update:PelletSale', 'Delete:PelletSale', 'DeleteAny:PelletSale', 'Restore:PelletSale', 'ForceDelete:PelletSale', 'ForceDeleteAny:PelletSale', 'RestoreAny:PelletSale', 'Replicate:PelletSale', 'Reorder:PelletSale',
                    'ViewAny:CashRemittance', 'View:CashRemittance', 'Create:CashRemittance', 'Update:CashRemittance', 'Delete:CashRemittance', 'DeleteAny:CashRemittance', 'Restore:CashRemittance', 'ForceDelete:CashRemittance', 'ForceDeleteAny:CashRemittance', 'RestoreAny:CashRemittance', 'Replicate:CashRemittance', 'Reorder:CashRemittance',
                    'ViewAny:ExpenseCategory', 'View:ExpenseCategory', 'Create:ExpenseCategory', 'Update:ExpenseCategory', 'Delete:ExpenseCategory', 'DeleteAny:ExpenseCategory', 'Restore:ExpenseCategory', 'ForceDelete:ExpenseCategory', 'ForceDeleteAny:ExpenseCategory', 'RestoreAny:ExpenseCategory', 'Replicate:ExpenseCategory', 'Reorder:ExpenseCategory',
                    'ViewAny:Expense', 'View:Expense', 'Create:Expense', 'Update:Expense', 'Delete:Expense', 'DeleteAny:Expense', 'Restore:Expense', 'ForceDelete:Expense', 'ForceDeleteAny:Expense', 'RestoreAny:Expense', 'Replicate:Expense', 'Reorder:Expense',
                    'ViewAny:SyncConflict', 'View:SyncConflict', 'Create:SyncConflict', 'Update:SyncConflict', 'Delete:SyncConflict', 'DeleteAny:SyncConflict', 'Restore:SyncConflict', 'ForceDelete:SyncConflict', 'ForceDeleteAny:SyncConflict', 'RestoreAny:SyncConflict', 'Replicate:SyncConflict', 'Reorder:SyncConflict',
                    'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', 'DeleteAny:User', 'Restore:User', 'ForceDelete:User', 'ForceDeleteAny:User', 'RestoreAny:User', 'Replicate:User', 'Reorder:User',
                    'ViewAny:Role', 'View:Role', 'Create:Role', 'Update:Role', 'Delete:Role', 'DeleteAny:Role', 'Restore:Role', 'ForceDelete:Role', 'ForceDeleteAny:Role', 'RestoreAny:Role', 'Replicate:Role', 'Reorder:Role',
                    'View:EditProfilePage', 'View:CashReconciliation', 'View:ProductionSummary', 'View:SalesSummary', 'View:StockSummary', 'View:StatsOverview', 'View:PelletSales',
                ],
                'Supervisor' => [
                    'ViewAny:Buyer',
                    'ViewAny:Material',
                    'ViewAny:MaterialIntake',
                    'ViewAny:CrushingProduction',
                    'ViewAny:Dispatch',
                    'ViewAny:PalletizingReceipt',
                    'ViewAny:PalletizingProduction',
                    'ViewAny:PelletSale',
                    'ViewAny:CashRemittance',
                    'ViewAny:ExpenseCategory',
                    'ViewAny:Expense',
                    'ViewAny:User',
                    'View:StatsOverview',
                    'View:EditProfilePage',
                    'View:CashReconciliation',
                    'View:ProductionSummary',
                    'View:SalesSummary',
                    'View:StockSummary',
                    'View:PelletSales',
                ],
                default => [],
            };

            if (! empty($permissions)) {
                return $permissions;
            }
        }

        return [];
    }
}
