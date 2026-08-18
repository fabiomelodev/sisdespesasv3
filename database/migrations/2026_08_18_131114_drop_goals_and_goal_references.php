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
            $table->dropColumn('goal_id');
            $table->enum('type', ['income', 'expense', 'reserve', 'transfer'])->change();
        });

        Schema::dropIfExists('goals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('balance', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['progress', 'completed'])->default('progress');
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['income', 'expense', 'goal', 'reserve', 'transfer'])->change();
            $table->foreignId('goal_id')->nullable()->after('reservation_id');
        });
    }
};
