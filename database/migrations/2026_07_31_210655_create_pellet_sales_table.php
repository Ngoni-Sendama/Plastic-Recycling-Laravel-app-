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
        Schema::create('pellet_sales', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('receipt_number')->index();
            $table->string('customer_name');
            $table->decimal('kg_sold', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('amount_received', 14, 2);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pellet_sales');
    }
};
