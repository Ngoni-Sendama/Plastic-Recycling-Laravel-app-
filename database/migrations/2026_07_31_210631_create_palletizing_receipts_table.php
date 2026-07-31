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
        Schema::create('palletizing_receipts', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('grn_number')->index();
            $table->foreignId('dispatch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('dispatch_reference')->nullable()->index();
            $table->foreignId('material_id')->constrained();
            $table->decimal('weight_received_kg', 12, 3);
            $table->decimal('rate_per_kg', 12, 2);
            $table->decimal('amount_payable', 14, 2);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['material_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palletizing_receipts');
    }
};
