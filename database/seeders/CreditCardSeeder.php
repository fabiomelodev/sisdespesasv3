<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('credit_cards')->insert([
            [
                'name' => 'NuBank 0675',
                'limit' => 100.00,
                'closing_day' => '08',
                'due_day' => '13',
                'is_active' => 1,
                'account_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'name' => 'Inter 9999',
                'limit' => 100.00,
                'closing_day' => '08',
                'due_day' => '13',
                'is_active' => 1,
                'account_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
