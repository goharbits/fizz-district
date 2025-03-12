<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusinessHours;
class BusinessHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessHours::create([
            'day_of_week' => 'Monday',
            'opening_time' => null,
            'closing_time' => null,
            'status' => 'closed',
        ]);

        BusinessHours::create([
            'day_of_week' => 'Tuesday',
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);
        BusinessHours::create([
            'day_of_week' => 'Wednesday',
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);

        BusinessHours::create([
            'day_of_week' => 'Thursday',
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);
        BusinessHours::create([
            'day_of_week' => 'Friday',
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);
        BusinessHours::create([
            'day_of_week' => 'Saturday',
            'opening_time' => '09:00:00',
            'closing_time' => '18:00:00',
            'status' => 'open',
        ]);
        BusinessHours::create([
            'day_of_week' => 'Sunday',
            'opening_time' => null,
            'closing_time' => null,
            'status' => 'closed',
        ]);

    }
}
