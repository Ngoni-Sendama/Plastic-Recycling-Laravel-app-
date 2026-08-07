<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        try {
            $roles = $this->getRoleNames()->values()->all();
        } catch (\Throwable) {
            $roles = [];
        }

        try {
            $permissions = $this->getAllPermissions()->pluck('name')->values()->all();
        } catch (\Throwable) {
            $permissions = [];
        }

        if (empty($roles) && filled($this->role)) {
            $roles = [$this->role];
        }

        if (empty($permissions)) {
            $permissions = $this->fallbackPermissions();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'roles' => $roles,
            'permissions' => $permissions,
            'audit_logs' => $this->whenLoaded('auditLogs', function (): array {
                return $this->auditLogs
                    ->take(20)
                    ->map(fn ($log) => [
                        'id' => $log->id,
                        'action' => $log->action,
                        'description' => $log->description,
                        'source' => $log->source,
                        'created_at' => $log->created_at?->toISOString(),
                    ])
                    ->values()
                    ->all();
            }, []),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function fallbackPermissions(): array
    {
        return match ($this->role) {
            'Admin' => $this->allPermissions(),
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
    }

    /**
     * @return array<int, string>
     */
    private function allPermissions(): array
    {
        return [
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
        ];
    }
}
