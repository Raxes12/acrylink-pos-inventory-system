<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('customer_name', 150)->index();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('credit_enabled')->default(false)->index();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->unsignedSmallInteger('default_credit_term_days')->default(30);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Retain the shared sequence high-water mark to avoid reusing issued codes.
        Schema::dropIfExists('customers');
    }
};
