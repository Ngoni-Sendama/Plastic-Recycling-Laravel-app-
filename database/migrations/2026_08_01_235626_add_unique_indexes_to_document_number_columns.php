<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'material_intakes' => 'grn_number',
            'crushing_productions' => 'batch_number',
            'dispatches' => 'dispatch_note_number',
            'palletizing_receipts' => 'grn_number',
            'palletizing_productions' => 'batch_number',
            'pellet_sales' => 'receipt_number',
            'cash_remittances' => 'voucher_number',
        ];

        foreach ($columns as $table => $column) {
            $indexName = "{$table}_{$column}_index";

            Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->dropIndex($indexName);
                $table->unique($column);
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'material_intakes' => 'grn_number',
            'crushing_productions' => 'batch_number',
            'dispatches' => 'dispatch_note_number',
            'palletizing_receipts' => 'grn_number',
            'palletizing_productions' => 'batch_number',
            'pellet_sales' => 'receipt_number',
            'cash_remittances' => 'voucher_number',
        ];

        foreach ($columns as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropUnique([$column]);
                $table->index($column);
            });
        }
    }
};
