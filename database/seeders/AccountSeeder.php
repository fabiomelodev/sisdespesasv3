<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('accounts')->insert([
            [
                'name' => 'NuBank',
                'type' => 'checking',
                'initial_balance' => 100.00,
                'color' => '#000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'name' => 'Inter',
                'type' => 'checking',
                'initial_balance' => 100.00,
                'color' => '#000000',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
