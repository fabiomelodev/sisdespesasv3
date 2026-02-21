<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('invoices')->insert([
            [
                'reference_month' => '2025-12-01',
                'due_date' => '2026-01-14',
                'is_closed' => 1,
                'is_paid' => 1,
                'credit_card_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
