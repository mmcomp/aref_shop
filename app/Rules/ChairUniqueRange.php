<?php

namespace App\Rules;

use App\Models\ProductDetailChair;
use Illuminate\Contracts\Validation\Rule;
use Log;

class ChairUniqueRange implements Rule
{
    private $productsId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function setProductId($productsId)
    {
        $this->productsId = $productsId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return ProductDetailChair::whereProductsId($this->productsId)->where(function ($query) use ($value) {
            $query->where("start", "<=" , $value)->where("end", ">=", $value);
        })->count() === 0;
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'chairs number already registerd';
    }
}
