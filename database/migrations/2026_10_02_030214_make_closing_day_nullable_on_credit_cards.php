<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sem doctrine/dbal instalado, Schema::table()->change() não funciona;
        // ALTER direto evita adicionar essa dependência só por isso.
        DB::statement('ALTER TABLE credit_cards MODIFY closing_day VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE credit_cards MODIFY closing_day VARCHAR(255) NOT NULL DEFAULT '01'");
    }
};
