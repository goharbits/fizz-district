<?php

namespace App\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomHelper
{

    public static function checkFavoriteItem($itemId){

        $userId = null;
        if(Auth::guard('api')->check()){
            $userId = Auth::guard('api')->user()->id;
        }

        return DB::table('favorites')
                        ->where('user_id', $userId)
                        ->where('item_id', $itemId)
                        ->exists();

    }
    public static function makeVariantSlug($name){
        return  Str::slug($name);
    }

}
