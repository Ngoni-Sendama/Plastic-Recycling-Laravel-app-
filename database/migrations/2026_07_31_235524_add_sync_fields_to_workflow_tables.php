<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables synced by the mobile app that need optimistic-lock and soft-delete support.
     */
    private array $syncableTables = [
        'materials',
        'material_intakes',
        'crushing_productions',
        'dispatches',
        'palletizing_receipts',
        'palletizing_productions',
        'pellet_sales',
        'cash_remittances',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->syncableTables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedInteger('lock_version')->default(1);
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->syncableTables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['lock_version', 'deleted_at']);
            });
        }
    }
};
