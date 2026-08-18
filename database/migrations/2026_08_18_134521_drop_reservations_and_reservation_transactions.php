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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['reservation_id']);
            $table->dropColumn('reservation_id');
        });

        Schema::dropIfExists('reservation_transactions');
        Schema::dropIfExists('reservations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('target_amount', 10, 2)->default(0);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('account_id');
            $table->timestamps();
        });

        Schema::create('reservation_transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->date('transaction_date');
            $table->boolean('is_paid')->default(true);
            $table->foreignId('reservation_id');
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('reservation_id')
                ->nullable()
                ->after('recurring_transaction_id')
                ->constrained('reservations')
                ->nullOnDelete();
        });
    }
};
