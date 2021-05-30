<?php

namespace App\Http\Validation;

use Illuminate\Support\Facades\Validator;

class ValidationError{
    public static function validationUserInput($request, $rules){
        $validator= Validator::make($request->all(), $rules);
        return $validator;
    }
}

