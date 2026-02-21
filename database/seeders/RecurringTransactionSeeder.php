<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecurringTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('recurring_transactions')->insert([
            [
                'name' => 'Conta de água',
                'type' => 'expense',
                'amount' => 50.00,
                'due_day' => '13',
                'payment_method' => 'credit',
                'next_run_date' => now(),
                'is_active' => 1,
                'account_id' => 1,
                'category_id' => 1,
                'credit_card_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
