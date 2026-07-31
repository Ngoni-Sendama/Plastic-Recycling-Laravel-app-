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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('dispatch_note_number')->index();
            $table->foreignId('crushing_production_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_reference')->nullable()->index();
            $table->foreignId('material_id')->constrained();
            $table->decimal('weight_dispatched_kg', 12, 3);
            $table->string('transported_by')->nullable();
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
        Schema::dropIfExists('dispatches');
    }
};
