<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\{Account, Category, CreditCard, Invoice, TransactionGroup};

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['debit', 'credit', 'pix']);
            $table->integer('installment_number')->nullable();
            $table->date('transaction_date');
            $table->boolean('is_paid')->default(true);
            $table->foreignIdFor(Account::class);
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(CreditCard::class)->nullable();
            $table->foreignIdFor(Invoice::class)->nullable();
            $table->foreignIdFor(model: TransactionGroup::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
