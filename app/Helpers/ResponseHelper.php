<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function formatResponse($success, $data = [], $msg = "")
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'msg' => $msg
        ]);
    }
}
