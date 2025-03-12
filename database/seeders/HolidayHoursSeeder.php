<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HolidayHours;
class HolidayHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        HolidayHours::create([
            'holiday_date' => '2023-12-25',
            'opening_time' => '10:00:00',
            'closing_time' => '16:00:00',
            'status' => 'open',
        ]);

        HolidayHours::create([
            'holiday_date' => '2023-12-31',
            'opening_time' => '12:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);

    }
}
