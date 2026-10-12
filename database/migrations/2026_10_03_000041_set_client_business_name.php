<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();

        DB::table('system_settings')->insertOrIgnore([
            'key' => 'business.name',
            'value' => 'VMC Auto Parts & Accessories',
            'type' => 'string',
            'group' => 'general',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('system_settings')
            ->where('key', 'business.name')
            ->where('value', 'Acry-Link')
            ->update(['value' => 'VMC Auto Parts & Accessories', 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('key', 'business.name')
            ->where('value', 'VMC Auto Parts & Accessories')
            ->update(['value' => 'Acry-Link', 'updated_at' => now()]);
    }
};
