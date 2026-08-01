<?php

namespace Database\Seeders;

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use App\Models\User;
use App\Services\CashRemittanceCalculator;
use App\Services\CrushingProductionCalculator;
use App\Services\MaterialIntakeCalculator;
use App\Services\PalletizingProductionCalculator;
use App\Services\PalletizingReceiptCalculator;
use App\Services\PelletSaleCalculator;
use Illuminate\Database\Seeder;

class WorkflowExampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $material = Material::where('code', 'PP')->firstOrFail();
        $admin = User::where('username', 'admin')->firstOrFail();
        $crusher = User::where('username', 'crusher01')->firstOrFail();
        $receiver = User::where('username', 'receiver01')->firstOrFail();
        $supervisor = User::where('username', 'supervisor01')->firstOrFail();

        $intakeValues = MaterialIntakeCalculator::calculate([
            'gross_weight_kg' => 1250,
            'tare_weight_kg' => 80,
            'unit_price' => 0.42,
        ]);

        $intake = MaterialIntake::updateOrCreate(
            ['grn_number' => 'GRN-2026-0001'],
            [
                'date' => '2026-07-31',
                'buyer_name' => 'GreenCycle Suppliers',
                'material_id' => $material->id,
                'gross_weight_kg' => 1250,
                'tare_weight_kg' => 80,
                'net_weight_kg' => $intakeValues['net_weight_kg'],
                'unit_price' => 0.42,
                'total_value' => $intakeValues['total_value'],
                'recorded_by_user_id' => $admin->id,
            ],
        );

        $crushingValues = CrushingProductionCalculator::calculate([
            'input_weight_kg' => 1170,
            'output_chips_kg' => 1098.5,
        ]);

        $crushingProduction = CrushingProduction::updateOrCreate(
            ['batch_number' => 'CR-BATCH-0001'],
            [
                'date' => '2026-07-31',
                'material_intake_id' => $intake->id,
                'grn_reference' => 'GRN-2026-0001',
                'material_id' => $material->id,
                'input_weight_kg' => 1170,
                'output_chips_kg' => 1098.5,
                'loss_kg' => $crushingValues['loss_kg'],
                'loss_percentage' => $crushingValues['loss_percentage'],
                'recorded_by_user_id' => $crusher->id,
            ],
        );

        $dispatch = Dispatch::updateOrCreate(
            ['dispatch_note_number' => 'DN-2026-0001'],
            [
                'date' => '2026-07-31',
                'crushing_production_id' => $crushingProduction->id,
                'batch_reference' => 'CR-BATCH-0001',
                'material_id' => $material->id,
                'weight_dispatched_kg' => 1090,
                'transported_by' => 'Highglen Truck 1',
                'recorded_by_user_id' => $crusher->id,
            ],
        );

        $receiptValues = PalletizingReceiptCalculator::calculate([
            'weight_received_kg' => 1087.5,
            'rate_per_kg' => 0.18,
        ]);

        $receipt = PalletizingReceipt::updateOrCreate(
            ['grn_number' => 'PGRN-2026-0001'],
            [
                'date' => '2026-08-01',
                'dispatch_id' => $dispatch->id,
                'dispatch_reference' => 'DN-2026-0001',
                'material_id' => $material->id,
                'weight_received_kg' => 1087.5,
                'rate_per_kg' => 0.18,
                'amount_payable' => $receiptValues['amount_payable'],
                'recorded_by_user_id' => $receiver->id,
            ],
        );

        $palletizingValues = PalletizingProductionCalculator::calculate([
            'chips_input_kg' => 1087.5,
            'pellets_output_kg' => 1018.2,
        ]);

        PalletizingProduction::updateOrCreate(
            ['batch_number' => 'PL-BATCH-0001'],
            [
                'date' => '2026-08-01',
                'palletizing_receipt_id' => $receipt->id,
                'grn_reference' => 'PGRN-2026-0001',
                'chips_input_kg' => 1087.5,
                'pellets_output_kg' => 1018.2,
                'loss_kg' => $palletizingValues['loss_kg'],
                'loss_percentage' => $palletizingValues['loss_percentage'],
                'recorded_by_user_id' => $receiver->id,
            ],
        );

        $saleValues = PelletSaleCalculator::calculate([
            'kg_sold' => 640,
            'unit_price' => 0.95,
        ]);

        PelletSale::updateOrCreate(
            ['receipt_number' => 'SALE-2026-0001'],
            [
                'date' => '2026-08-02',
                'customer_name' => 'Metro Plastics',
                'kg_sold' => 640,
                'unit_price' => 0.95,
                'amount_received' => $saleValues['amount_received'],
                'recorded_by_user_id' => $supervisor->id,
            ],
        );

        $remittanceValues = CashRemittanceCalculator::calculate([
            'chips_delivered_kg' => 1087.5,
            'recovery_price_per_kg' => 0.18,
            'sales_revenue' => 608,
            'cash_remitted' => 500,
        ]);

        CashRemittance::updateOrCreate(
            ['voucher_number' => 'REM-2026-0001'],
            [
                'date' => '2026-08-03',
                'period_covered' => '2026-07-31 to 2026-08-02',
                'chips_delivered_kg' => 1087.5,
                'recovery_price_per_kg' => 0.18,
                'sales_revenue' => 608,
                'cash_remitted' => 500,
                'max_remittance_due' => $remittanceValues['max_remittance_due'],
                'balance_retained' => $remittanceValues['balance_retained'],
                'recorded_by_user_id' => $supervisor->id,
            ],
        );
    }
}
