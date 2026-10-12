<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            [
                'role_name' => 'Admin',
                'description' => 'Full system access including users, inventory, POS, security, audit logs, and device controls.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_name' => 'Cashier',
                'description' => 'POS terminal access and read-only inventory visibility.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_name' => 'Inventory Staff',
                'description' => 'Inventory, suppliers, reports, refunds, and audit access without user or security administration.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('role')
                ->constrained('roles', 'role_id')
                ->nullOnDelete();
        });

        $roleIds = DB::table('roles')->pluck('role_id', 'role_name');

        DB::table('users')->where('role', 'admin')->update(['role_id' => $roleIds['Admin'] ?? null]);
        DB::table('users')->where('role', 'cashier')->update(['role_id' => $roleIds['Cashier'] ?? null]);
        DB::table('users')->where('role', 'inventory_staff')->update(['role_id' => $roleIds['Inventory Staff'] ?? null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
