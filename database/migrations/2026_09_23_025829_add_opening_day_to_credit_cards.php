<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->string('opening_day')->nullable()->after('closing_day');
        });

        // Preserva o comportamento atual (ciclo continuo) para os cartoes
        // ja cadastrados: abertura = fechamento + 1 dia.
        DB::table('credit_cards')->get(['id', 'closing_day'])->each(function ($creditCard) {
            $openingDay = ((int) $creditCard->closing_day) + 1;

            if ($openingDay > 31) {
                $openingDay = 1;
            }

            DB::table('credit_cards')
                ->where('id', $creditCard->id)
                ->update(['opening_day' => str_pad((string) $openingDay, 2, '0', STR_PAD_LEFT)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_cards', function (Blueprint $table) {
            $table->dropColumn('opening_day');
        });
    }
};
