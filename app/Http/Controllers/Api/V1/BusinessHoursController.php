<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HolidayHours;
use App\Models\BusinessHours;
use App\Helpers\ResponseHelper;
use Carbon\Carbon;
use App\Http\Controllers\Api\V1\BaseController;

class BusinessHoursController extends BaseController
{
     public function getBusinessHours($date)
    {
        $dayOfWeek = Carbon::parse($date)->format('l');
        // Check if there's a holiday record for the specific date
        $holiday = HolidayHours::where('holiday_date', $date)->first();

        if ($holiday) {
            // If there's a holiday for this specific date, return the holiday hours
            $data = [
                'day' => $dayOfWeek,
                'status' => $holiday->status,
                'opening_time' => $holiday->opening_time,
                'closing_time' => $holiday->closing_time,
            ];

            return ResponseHelper::formatResponse(true, $data, 'Data Found');
        }


        $businessHours = BusinessHours::where('day_of_week', $dayOfWeek)->first();

        if ($businessHours) {
            // hours for businesss days
            $data = [
                'day'=>$businessHours->day_of_week,
                'status' => $businessHours->status,
                'opening_time' => $businessHours->opening_time,
                'closing_time' => $businessHours->closing_time,
            ];
            return ResponseHelper::formatResponse(true, $data, 'Data Found.');
        }

        $data = [
            'status' => 'closed',
            'opening_time' => null,
            'closing_time' => null,
        ];
        // If no record found for the day, return "closed" status
        return ResponseHelper::formatResponse(true, $data, 'No Data Found.');

    }
}
