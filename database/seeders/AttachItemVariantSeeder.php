<?php

namespace Database\Seeders;

use App\Models\Variant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AttachItemVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assume these items have already been inserted into the database
        $itemCoffeeBlack = DB::table('items')->where('name', 'Coffee Black')->first();
        $itemSpecialDrink = DB::table('items')->where('name', 'Special Drink')->first();
        $itemCasualDrink = DB::table('items')->where('name', 'Casual Drink')->first();

        // Find the variants created in the VariantSeeder
        $variantSize = Variant::where('name', 'Size')->first();
        $variantCaffeine = Variant::where('name', 'Caffeine Level')->first();

        // Attaching variants to items if items exist
        if ($itemCoffeeBlack) {
            DB::table('item_variant')->insert([
                ['item_id' => $itemCoffeeBlack->id, 'variant_id' => $variantSize->id],
                ['item_id' => $itemCoffeeBlack->id, 'variant_id' => $variantCaffeine->id],
            ]);
        }

        if ($itemSpecialDrink) {
            DB::table('item_variant')->insert([
                ['item_id' => $itemSpecialDrink->id, 'variant_id' => $variantSize->id],
                ['item_id' => $itemSpecialDrink->id, 'variant_id' => $variantCaffeine->id],
            ]);
        }

        if ($itemCasualDrink) {
            DB::table('item_variant')->insert([
                ['item_id' => $itemCasualDrink->id, 'variant_id' => $variantSize->id],
                ['item_id' => $itemCasualDrink->id, 'variant_id' => $variantCaffeine->id],
            ]);
        }
    }
}
