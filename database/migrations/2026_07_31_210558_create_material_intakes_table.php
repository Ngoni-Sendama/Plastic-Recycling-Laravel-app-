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
        Schema::create('material_intakes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('grn_number')->index();
            $table->string('buyer_name');
            $table->foreignId('material_id')->constrained();
            $table->decimal('gross_weight_kg', 12, 3);
            $table->decimal('tare_weight_kg', 12, 3);
            $table->decimal('net_weight_kg', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_value', 14, 2);
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
        Schema::dropIfExists('material_intakes');
    }
};
