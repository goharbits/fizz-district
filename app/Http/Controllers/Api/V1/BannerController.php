<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\WelcomeMessage;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\ValidatorHelper;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BannerController extends Controller
{
    // Get all banners
    public function getAll()
    {

            $banners = Banner::where('is_active', true)->get();
            $banners = $banners->map(function($banner) {
                    return [
                        'id' => $banner->id,
                        'image' => asset('banners/' . $banner->image), // Construct the full image URL
                        'name' => $banner->name, // Assuming the banner has a 'name' field
                    ];
                });

             // Fetch all relevant items in a single query
            $items = Item::where('drink_of_day', 1)
                    ->orWhere('flavor_of_week', 1)
                    ->orWhere('drink_of_month', 1)
                    ->select('id', 'image', 'name', 'price', 'drink_of_day', 'flavor_of_week', 'drink_of_month')
                    ->get();

            // Filter the items based on their flags
            $drink_of_day = $items->firstWhere('drink_of_day', 1);
            if($drink_of_day)
            {
                $isFav =  CustomHelper::checkFavoriteItem($drink_of_day->id);
                $drink_of_day = $drink_of_day->toArray();
                $drink_of_day['isFav'] = $isFav;
                $drink_of_day['image'] = asset('items/' . @$drink_of_day['image']);

            }

            $flavor_of_week = $items->firstWhere('flavor_of_week', 1);
            if($flavor_of_week)
            {
                $isFav =  CustomHelper::checkFavoriteItem($flavor_of_week->id);
                $flavor_of_week = $flavor_of_week->toArray();
                $flavor_of_week['isFav'] = $isFav;
                $flavor_of_week['image'] = asset('items/' . @$flavor_of_week['image']);
            }


            $drink_of_month = $items->firstWhere('drink_of_month', 1);
             if($drink_of_month)
            {
                $isFav =  CustomHelper::checkFavoriteItem($drink_of_month->id);
                $drink_of_month = $drink_of_month->toArray();
                $drink_of_month['isFav'] = $isFav;
                $drink_of_month['image'] = asset('items/' . @$drink_of_month['image']);
            }

            $currentMonth = Carbon::now()->month;


            $welcome_msg = WelcomeMessage::where('start_month', '<=', $currentMonth)
                            ->where('end_month', '>=', $currentMonth)
                            ->first();
            // Combine banners and the drink items in the response
            $responseData = [
                'banners' => $banners,
                'drink_of_day' => $drink_of_day,
                'flavor_of_week' => $flavor_of_week,
                'drink_of_month' => $drink_of_month,
                'welcome_msg'=> @$welcome_msg->title ?? null
            ];


        return ResponseHelper::formatResponse(true, $responseData, 'Banners retrieved successfully.');
    }


    // Add a new banner
    public function add(Request $request)
    {
        // Validation rules
        $rules = [
            'image' => 'required|string', // Image file validation
            'is_active' => 'boolean',
        ];

        // Validate request
        $validation = ValidatorHelper::validate($request->all(), $rules);

        if (!$validation['success']) {
            return ResponseHelper::formatResponse(false, [], $validation['errors']);
        }

        // Create the banner
        $banner = Banner::create([
            'image' =>  $request->image,
            'is_active' => $request->input('is_active', true),
        ]);

        return ResponseHelper::formatResponse(true, $banner, 'Banner created successfully.');
    }
}