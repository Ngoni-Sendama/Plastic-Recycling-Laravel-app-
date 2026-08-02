<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_intakes', function (Blueprint $table): void {
            $table->foreignId('buyer_id')
                ->nullable()
                ->after('grn_number')
                ->constrained('buyers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_intakes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('buyer_id');
        });
    }
};
