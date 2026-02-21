<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaction_groups')->insert([
            [
                'name' => 'TV',
                'description' => '',
                'total_amount' => 1000.00,
                'installments' => 10,
                'purchase_date' => '2025-12-25',
                'credit_card_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
