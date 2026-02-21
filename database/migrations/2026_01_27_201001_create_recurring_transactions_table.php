<?php

use App\Models\{Account, Category, CreditCard};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['expense', 'income']);
            $table->decimal('amount', 10, 2);
            $table->string('due_day');
            $table->enum('payment_method', ['debit', 'credit']);
            $table->date('next_run_date');
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(Account::class);
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(CreditCard::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
