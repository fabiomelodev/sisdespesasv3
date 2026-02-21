<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Alimento',
                'type' => 'expense',
                'color' => '#000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Mercado',
                'type' => 'expense',
                'color' => '#000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Salário',
                'type' => 'income',
                'color' => '#000000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
