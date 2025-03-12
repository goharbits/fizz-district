<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\V1\BaseController;


class PromoController extends BaseController
{
    public function createPromo(Request $request){
            $response = $this->cloverService->createPromo($request);
            if (!$response->getData()->success) {
                return ResponseHelper::formatResponse(false, [], $response->getData()->msg);
            }
            return ResponseHelper::formatResponse(true, $response,'Promo Created');
    }
}
