<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('items')->insert([
            ['name' => 'Coffee Black', 'category_id' => 2],
            ['name' => 'Special Drink', 'category_id' => 5],
            ['name' => 'Casual Drink', 'category_id' => 6],
        ]);
    }
}
