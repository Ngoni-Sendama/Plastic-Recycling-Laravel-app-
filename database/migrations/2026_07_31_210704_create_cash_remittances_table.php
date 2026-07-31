<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_remittances', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('voucher_number')->index();
            $table->string('period_covered')->nullable();
            $table->decimal('chips_delivered_kg', 12, 3);
            $table->decimal('recovery_price_per_kg', 12, 2);
            $table->decimal('sales_revenue', 14, 2);
            $table->decimal('cash_remitted', 14, 2);
            $table->decimal('max_remittance_due', 14, 2);
            $table->decimal('balance_retained', 14, 2);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_remittances');
    }
};
