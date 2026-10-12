<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('status');
            }
        });

        Schema::create('mfa_challenges', function (Blueprint $table) {
            $table->id('mfa_challenge_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('otp_hash');
            $table->string('delivery_method', 30)->default('email');
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->enum('status', ['pending', 'verified', 'expired', 'failed'])->default('pending');
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });

        DB::table('users')->where('role', 'admin')->update(['mfa_enabled' => true]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_challenges');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mfa_enabled')) {
                $table->dropColumn('mfa_enabled');
            }
        });
    }
};
