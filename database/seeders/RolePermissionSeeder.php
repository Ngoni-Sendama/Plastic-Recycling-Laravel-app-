<?php

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const RESOURCE_ACTIONS = [
        'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
        'Restore', 'ForceDelete', 'ForceDeleteAny', 'RestoreAny',
        'Replicate', 'Reorder',
    ];

    private const VIEW_ACTIONS = ['ViewAny', 'View'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roleModel = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();

        foreach ($this->roles() as $roleName => $permissions) {
            $role = $roleModel::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $permissionModels = collect($permissions)
                ->map(fn (string $permission): mixed => $permissionModel::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]));

            $role->syncPermissions($permissionModels);
        }

        $this->assignRolesToUsers();

        $this->command->info('Roles, permissions and user assignments seeded.');
    }

    /**
     * Build every permission name for a Shield entity.
     *
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function resourcePermissions(string $resource, array $actions = self::RESOURCE_ACTIONS): array
    {
        return array_map(fn (string $action): string => "{$action}:{$resource}", $actions);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function roles(): array
    {
        $allResources = [
            'Material', 'MaterialIntake', 'CrushingProduction', 'Dispatch',
            'PalletizingReceipt', 'PalletizingProduction', 'PelletSale',
            'CashRemittance', 'SyncConflict', 'User', 'Role',
        ];

        $pages = [
            'View:EditProfilePage',
            'View:CashReconciliation',
            'View:ProductionSummary',
            'View:SalesSummary',
            'View:StockSummary',
        ];

        $widgets = ['View:StatsOverview', 'View:PelletSales'];

        $viewOnly = self::VIEW_ACTIONS;

        return [
            'Admin' => [
                ...$this->resourcePermissionsForResources($allResources),
                ...$pages,
                ...$widgets,
            ],

            'Stock controller' => array_merge(
                $this->resourcePermissions('MaterialIntake'),
                $this->resourcePermissions('Dispatch'),
                $this->resourcePermissions('Material', $viewOnly),
                $this->resourcePermissions('CrushingProduction', $viewOnly),
                $this->resourcePermissions('PalletizingReceipt', $viewOnly),
                $this->resourcePermissions('PalletizingProduction', $viewOnly),
                $this->resourcePermissions('PelletSale', $viewOnly),
                $this->resourcePermissions('CashRemittance', $viewOnly),
                $this->resourcePermissions('SyncConflict', $viewOnly),
                ['View:EditProfilePage', 'View:StockSummary', 'View:CashReconciliation', 'View:StatsOverview', 'View:PelletSales'],
            ),

            'Crusher operator' => array_merge(
                $this->resourcePermissions('CrushingProduction'),
                $this->resourcePermissions('Dispatch'),
                $this->resourcePermissions('Material', $viewOnly),
                $this->resourcePermissions('MaterialIntake', $viewOnly),
                ['View:EditProfilePage', 'View:StatsOverview'],
            ),

            'Stock receiver' => array_merge(
                $this->resourcePermissions('PalletizingReceipt'),
                $this->resourcePermissions('Dispatch', $viewOnly),
                $this->resourcePermissions('Material', $viewOnly),
                ['View:EditProfilePage', 'View:StatsOverview'],
            ),

            'Palletizing operator' => array_merge(
                $this->resourcePermissions('PalletizingProduction'),
                $this->resourcePermissions('PalletizingReceipt', $viewOnly),
                $this->resourcePermissions('Material', $viewOnly),
                ['View:EditProfilePage', 'View:StatsOverview'],
            ),

            'Supervisor' => [
                ...$this->resourcePermissionsForResources($allResources, $viewOnly),
                ...$pages,
                ...$widgets,
            ],
        ];
    }

    /**
     * Flatten the permission lists for a set of resources into a single list.
     *
     * @param  array<int, string>  $resources
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function resourcePermissionsForResources(array $resources, array $actions = self::RESOURCE_ACTIONS): array
    {
        return array_merge(...array_map(
            fn (string $resource): array => $this->resourcePermissions($resource, $actions),
            $resources,
        ));
    }

    /**
     * Assign the seeded staff their matching Shield roles.
     */
    private function assignRolesToUsers(): void
    {
        foreach ($this->userRoleAssignments() as $username => $roles) {
            $user = User::where('username', $username)->firstOrFail();
            $user->syncRoles($roles);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function userRoleAssignments(): array
    {
        return [
            'admin' => ['super_admin', 'Admin'],
            'crusher01' => ['Crusher operator'],
            'receiver01' => ['Stock receiver'],
            'supervisor01' => ['Supervisor'],
            'stock01' => ['Stock controller'],
            'palletizing01' => ['Palletizing operator'],
        ];
    }
}
