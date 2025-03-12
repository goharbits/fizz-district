<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Add an item to favorites
    public function addFavorite(Item $item)
    {
        $user = Auth::user();

        // Check if the item is already favorited
        if ($user->favorites()->where('item_id', $item->id)->exists()) {
            return ResponseHelper::formatResponse(false, [], 'Item already favorited');
        }

        // Add item to favorites
        $user->favorites()->attach($item->id);

        return ResponseHelper::formatResponse(true, [], 'Item added to favorites');
    }

    // Remove an item from favorites
    public function removeFavorite(Item $item)
    {
        $user = Auth::user();

        // Check if the item is already in favorites
        if (!$user->favorites()->where('item_id', $item->id)->exists()) {
            return ResponseHelper::formatResponse(false, [], 'Item is not in favorites');
        }

        // Remove item from favorites
        $user->favorites()->detach($item->id);

        return ResponseHelper::formatResponse(true, [], 'Item removed from favorites');
    }

    // Get user's favorite items
    public function getFavorites()
    {
       $favorites=[];
        if(Auth::guard('api')->check()){
            $user = Auth::guard('api')->user();
            $favorites = $user->favorites()->get();
            $favorites->each(function ($favorite) {
                // Assuming your favorites have an `image` field and you want to prepend the public path
                $favorite->image = asset('items/' . $favorite->image);
            });
        }


        return ResponseHelper::formatResponse(true, $favorites, 'Favorites retrieved successfully');
    }
}