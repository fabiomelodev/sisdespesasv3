<?php

use App\Models\CreditCard;
use Database\Seeders\CreditCardSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_groups', function (Blueprint $table) {
            $table->foreignIdFor(CreditCard::class)->change()->nullable();
            $table->enum('payment_method', ['debit', 'credit', 'pix'])->after('installments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_groups', function (Blueprint $table) {
            //
        });
    }
};
