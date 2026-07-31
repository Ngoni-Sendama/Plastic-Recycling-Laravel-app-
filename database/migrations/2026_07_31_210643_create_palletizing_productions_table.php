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
        Schema::create('palletizing_productions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('batch_number')->index();
            $table->foreignId('palletizing_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grn_reference')->nullable()->index();
            $table->decimal('chips_input_kg', 12, 3);
            $table->decimal('pellets_output_kg', 12, 3);
            $table->decimal('loss_kg', 12, 3);
            $table->decimal('loss_percentage', 7, 4);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('palletizing_productions');
    }
};
