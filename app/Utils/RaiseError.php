<?php
namespace App\Utils;
use Illuminate\Http\Exceptions\HttpResponseException;

class RaiseError {
    
    public function ValidationError($condition, $errorMessage) {

        if ($condition) {
            throw new HttpResponseException(
                response()->json(['errors' => $errorMessage], 422)
            );
        }
    }
}
