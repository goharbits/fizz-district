<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubVariantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sub_variants')->insert([
            ['name' => '12 oz', 'variant_id' => 1],
            ['name' => '16 oz', 'variant_id' => 1],
            ['name' => '10 mg', 'variant_id' => 2],
            ['name' => '20 mg', 'variant_id' => 2],
        ]);
    }
}
