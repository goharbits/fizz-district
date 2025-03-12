<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Drinks', 'item_group_id' => 1, 'parent_category_id' => null, 'has_subcategories' => true],
            ['name' => 'Coffee', 'item_group_id' => 1, 'parent_category_id' => null, 'has_subcategories' => false],
            ['name' => 'Special', 'item_group_id' => 2, 'parent_category_id' => null, 'has_subcategories' => true],
            ['name' => 'Casual', 'item_group_id' => 2, 'parent_category_id' => null, 'has_subcategories' => false],
            ['name' => 'Shaken Lemonade', 'item_group_id' => 1, 'parent_category_id' => 1, 'has_subcategories' => false],
            ['name' => 'Iced Tea', 'item_group_id' => 1, 'parent_category_id' => 1, 'has_subcategories' => false],
        ]);
    }
}