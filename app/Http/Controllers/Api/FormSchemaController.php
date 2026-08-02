<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class FormSchemaController extends ApiController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'modules' => [
                'intake' => [
                    'title' => 'Material intake',
                    'shortTitle' => 'Intake',
                    'endpoint' => '/material-intakes',
                    'primary' => ['grn'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'grn', 'label' => 'GRN no.', 'type' => 'text', 'required' => true],
                        ['name' => 'buyer', 'label' => 'Buyer name', 'type' => 'text', 'required' => true],
                        ['name' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => true, 'optionsEndpoint' => '/materials'],
                        ['name' => 'gross', 'label' => 'Gross weight (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'tare', 'label' => 'Tare weight (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'price', 'label' => 'Unit price ($/kg)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'net', 'label' => 'Net weight', 'format' => 'kg'],
                        ['name' => 'value', 'label' => 'Total value', 'format' => 'money'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'buyer' => 'buyer_name', 'material' => 'material_code', 'gross' => 'gross_weight_kg', 'tare' => 'tare_weight_kg', 'price' => 'unit_price'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'grn_number' => 'grn', 'buyer_name' => 'buyer', 'material' => 'material', 'gross_weight_kg' => 'gross', 'tare_weight_kg' => 'tare', 'unit_price' => 'price', 'net_weight_kg' => 'net', 'total_value' => 'value', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'crushing' => [
                    'title' => 'Crushing production',
                    'shortTitle' => 'Crushing',
                    'endpoint' => '/crushing-productions',
                    'primary' => ['batch'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'batch', 'label' => 'Batch no.', 'type' => 'text', 'required' => true],
                        ['name' => 'grnRef', 'label' => 'GRN ref', 'type' => 'text'],
                        ['name' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => true, 'optionsEndpoint' => '/materials'],
                        ['name' => 'input', 'label' => 'Input weight (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'output', 'label' => 'Output chips (kg)', 'type' => 'number', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'loss', 'label' => 'Loss', 'format' => 'kg'],
                        ['name' => 'lossPct', 'label' => 'Loss %', 'format' => 'pct'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'grnRef' => 'grn_reference', 'material' => 'material_code', 'input' => 'input_weight_kg', 'output' => 'output_chips_kg'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'batch_number' => 'batch', 'grn_reference' => 'grnRef', 'material' => 'material', 'input_weight_kg' => 'input', 'output_chips_kg' => 'output', 'loss_kg' => 'loss', 'loss_percentage' => 'lossPct', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'dispatch' => [
                    'title' => 'Dispatch to palletizing',
                    'shortTitle' => 'Dispatch',
                    'endpoint' => '/dispatches',
                    'primary' => ['dispatchNo'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'dispatchNo', 'label' => 'Dispatch note no.', 'type' => 'text', 'required' => true],
                        ['name' => 'batchRef', 'label' => 'Batch ref', 'type' => 'text'],
                        ['name' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => true, 'optionsEndpoint' => '/materials'],
                        ['name' => 'weight', 'label' => 'Weight dispatched (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'transportedBy', 'label' => 'Transported by', 'type' => 'text'],
                    ],
                    'computedFields' => [],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'dispatchNo' => 'dispatch_note_number', 'batchRef' => 'batch_reference', 'material' => 'material_code', 'weight' => 'weight_dispatched_kg', 'transportedBy' => 'transported_by'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'dispatch_note_number' => 'dispatchNo', 'batch_reference' => 'batchRef', 'material' => 'material', 'weight_dispatched_kg' => 'weight', 'transported_by' => 'transportedBy', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'receipt' => [
                    'title' => 'Palletizing receipt',
                    'shortTitle' => 'Receipt',
                    'endpoint' => '/palletizing-receipts',
                    'primary' => ['grn'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'grn', 'label' => 'GRN no.', 'type' => 'text', 'required' => true],
                        ['name' => 'dispatchRef', 'label' => 'Dispatch note ref', 'type' => 'text'],
                        ['name' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => true, 'optionsEndpoint' => '/materials'],
                        ['name' => 'weight', 'label' => 'Weight received (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'rate', 'label' => 'Rate ($/kg)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'amount', 'label' => 'Amount payable', 'format' => 'money'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'dispatchRef' => 'dispatch_reference', 'material' => 'material_code', 'weight' => 'weight_received_kg', 'rate' => 'rate_per_kg'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'grn_number' => 'grn', 'dispatch_reference' => 'dispatchRef', 'material' => 'material', 'weight_received_kg' => 'weight', 'rate_per_kg' => 'rate', 'amount_payable' => 'amount', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'palletProd' => [
                    'title' => 'Palletizing production',
                    'shortTitle' => 'Pelletizing',
                    'endpoint' => '/palletizing-productions',
                    'primary' => ['batch'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'batch', 'label' => 'Batch no.', 'type' => 'text', 'required' => true],
                        ['name' => 'grnRef', 'label' => 'GRN ref', 'type' => 'text'],
                        ['name' => 'input', 'label' => 'Chips input (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'output', 'label' => 'Pellets output (kg)', 'type' => 'number', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'loss', 'label' => 'Loss', 'format' => 'kg'],
                        ['name' => 'lossPct', 'label' => 'Loss %', 'format' => 'pct'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'grnRef' => 'grn_reference', 'input' => 'chips_input_kg', 'output' => 'pellets_output_kg'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'batch_number' => 'batch', 'grn_reference' => 'grnRef', 'chips_input_kg' => 'input', 'pellets_output_kg' => 'output', 'loss_kg' => 'loss', 'loss_percentage' => 'lossPct', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'sales' => [
                    'title' => 'Pellet sales',
                    'shortTitle' => 'Sales',
                    'endpoint' => '/pellet-sales',
                    'primary' => ['receiptNo'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'receiptNo', 'label' => 'Receipt no.', 'type' => 'text', 'required' => true],
                        ['name' => 'customer', 'label' => 'Customer', 'type' => 'text', 'required' => true],
                        ['name' => 'kgSold', 'label' => 'Pellets sold (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'price', 'label' => 'Unit price ($/kg)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'amount', 'label' => 'Amount received', 'format' => 'money'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'customer' => 'customer_name', 'kgSold' => 'kg_sold', 'price' => 'unit_price'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'receipt_number' => 'receiptNo', 'customer_name' => 'customer', 'kg_sold' => 'kgSold', 'unit_price' => 'price', 'amount_received' => 'amount', 'recorded_by' => 'recordedBy'],
                    ],
                ],
                'remittance' => [
                    'title' => 'Cash remittance',
                    'shortTitle' => 'Remittance',
                    'endpoint' => '/cash-remittances',
                    'primary' => ['voucherNo'],
                    'fields' => [
                        ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                        ['name' => 'voucherNo', 'label' => 'Voucher no.', 'type' => 'text', 'required' => true],
                        ['name' => 'period', 'label' => 'Period covered', 'type' => 'text'],
                        ['name' => 'chipsKg', 'label' => 'Chips delivered (kg)', 'type' => 'number', 'required' => true],
                        ['name' => 'recoveryPrice', 'label' => 'Recovery price ($/kg)', 'type' => 'number', 'step' => '0.01', 'required' => true],
                        ['name' => 'salesRevenue', 'label' => 'Sales revenue in period ($)', 'type' => 'number', 'required' => true],
                        ['name' => 'cashRemitted', 'label' => 'Actual cash remitted ($)', 'type' => 'number', 'required' => true],
                    ],
                    'computedFields' => [
                        ['name' => 'maxDue', 'label' => 'Max remittance due', 'format' => 'money'],
                        ['name' => 'balanceRetained', 'label' => 'Balance retained', 'format' => 'money'],
                    ],
                    'apiMapping' => [
                        'toApi' => ['date' => 'date', 'period' => 'period_covered', 'chipsKg' => 'chips_delivered_kg', 'recoveryPrice' => 'recovery_price_per_kg', 'salesRevenue' => 'sales_revenue', 'cashRemitted' => 'cash_remitted'],
                        'fromApi' => ['id' => 'id', 'date' => 'date', 'voucher_number' => 'voucherNo', 'period_covered' => 'period', 'chips_delivered_kg' => 'chipsKg', 'recovery_price_per_kg' => 'recoveryPrice', 'sales_revenue' => 'salesRevenue', 'cash_remitted' => 'cashRemitted', 'max_remittance_due' => 'maxDue', 'balance_retained' => 'balanceRetained', 'recorded_by' => 'recordedBy'],
                    ],
                ],
            ],
        ]);
    }
}
