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
        Schema::create('crushing_productions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('batch_number')->index();
            $table->foreignId('material_intake_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grn_reference')->nullable()->index();
            $table->foreignId('material_id')->constrained();
            $table->decimal('input_weight_kg', 12, 3);
            $table->decimal('output_chips_kg', 12, 3);
            $table->decimal('loss_kg', 12, 3);
            $table->decimal('loss_percentage', 7, 4);
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
        Schema::dropIfExists('crushing_productions');
    }
};
