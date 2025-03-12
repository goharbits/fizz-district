<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class ValidatorHelper
{
    public static function validate($data, $rules,array $messages = [])
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }

        return [
            'success' => true,
            'errors' => null
        ];
    }
}