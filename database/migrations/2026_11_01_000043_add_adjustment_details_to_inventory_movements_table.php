<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('adjustment_reason', 50)->nullable()->after('movement_type');
            $table->uuid('adjustment_token')->nullable()->unique()->after('reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropUnique(['adjustment_token']);
            $table->dropColumn(['adjustment_reason', 'adjustment_token']);
        });
    }
};
