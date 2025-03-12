<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Models\RecommendedAddOn;
use App\Http\Controllers\Controller;

class RecommendedAddOnController extends Controller
{
    public function getAll()
    {
        $recommendedAddOns = RecommendedAddOn::all();
        return ResponseHelper::formatResponse(true, $recommendedAddOns, 'Recommended add-ons retrieved successfully.');
    }

    public function getSpecificRecord($id)
    {
        $recommendedAddOn = RecommendedAddOn::find($id);

        if (!$recommendedAddOn) {
            return ResponseHelper::formatResponse(false, [], 'Recommended add-on not found.');
        }

        return ResponseHelper::formatResponse(true, $recommendedAddOn, 'Recommended add-on retrieved successfully.');
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $recommendedAddOn = RecommendedAddOn::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'description' => $request->input('description'),
        ]);

        return ResponseHelper::formatResponse(true, $recommendedAddOn, 'Recommended add-on created successfully.');
    }

    public function attachRecommendedAddOns(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $recommendedAddOnIds = $request->input('recommended_add_on_ids', []);

        if (empty($recommendedAddOnIds)) {
            return ResponseHelper::formatResponse(false, [], 'No recommended add-ons provided.');
        }

        $alreadyAttached = [];
        $newlyAttached = [];

        foreach ($recommendedAddOnIds as $recommendedAddOnId) {
            if ($item->recommendedAddOns()->where('recommended_add_on_id', $recommendedAddOnId)->exists()) {
                $alreadyAttached[] = $recommendedAddOnId;
            } else {
                $item->recommendedAddOns()->attach($recommendedAddOnId);
                $newlyAttached[] = $recommendedAddOnId;
            }
        }

        $message = 'Recommended add-ons attached successfully.';
        if (!empty($alreadyAttached)) {
            $message .= ' Some recommended add-ons were already attached: ' . implode(', ', $alreadyAttached);
        }

        return ResponseHelper::formatResponse(true, ['newly_attached' => $newlyAttached], $message);
    }

    public function detachRecommendedAddOns(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return ResponseHelper::formatResponse(false, [], 'Item not found.');
        }

        $recommendedAddOnIds = $request->input('recommended_add_on_ids', []);

        if (empty($recommendedAddOnIds)) {
            return ResponseHelper::formatResponse(false, [], 'No recommended add-ons provided.');
        }

        $item->recommendedAddOns()->detach($recommendedAddOnIds);

        return ResponseHelper::formatResponse(true, [], 'Recommended add-ons detached successfully.');
    }
}