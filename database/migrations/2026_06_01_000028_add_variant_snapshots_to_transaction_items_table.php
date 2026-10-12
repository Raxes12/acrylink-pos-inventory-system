<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_items', 'variant_id')) {
                $table->foreignId('variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('transaction_items', 'product_name_snapshot')) {
                $table->string('product_name_snapshot', 200)->nullable()->after('variant_id');
            }
            if (!Schema::hasColumn('transaction_items', 'part_number_snapshot')) {
                $table->string('part_number_snapshot', 100)->nullable()->after('product_name_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'brand_snapshot')) {
                $table->string('brand_snapshot', 100)->nullable()->after('part_number_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'model_snapshot')) {
                $table->string('model_snapshot', 100)->nullable()->after('brand_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'size_snapshot')) {
                $table->string('size_snapshot', 100)->nullable()->after('model_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'color_snapshot')) {
                $table->string('color_snapshot', 50)->nullable()->after('size_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'unit_snapshot')) {
                $table->string('unit_snapshot', 20)->nullable()->after('color_snapshot');
            }
            if (!Schema::hasColumn('transaction_items', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('unit_price');
            }
        });

        DB::table('transaction_items')
            ->whereNull('product_name_snapshot')
            ->update([
                'product_name_snapshot' => DB::raw('product_name'),
                'subtotal' => DB::raw('quantity * unit_price'),
            ]);
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            foreach ([
                'subtotal',
                'unit_snapshot',
                'color_snapshot',
                'size_snapshot',
                'model_snapshot',
                'brand_snapshot',
                'part_number_snapshot',
                'product_name_snapshot',
            ] as $column) {
                if (Schema::hasColumn('transaction_items', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('transaction_items', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
        });
    }
};
