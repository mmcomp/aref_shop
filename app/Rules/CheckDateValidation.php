<?php

namespace App\Rules;

use App\Models\ProductDetailChair;
use Illuminate\Contracts\Validation\Rule;
use Log;
use Carbon\Carbon;

class CheckDateValidation implements Rule
{
    protected $from_date;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($from_date)
    {
        $this->from_date = $from_date;
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
        return $this->checkDate($this->from_date, $value);
    }
    public function checkDate($from_date, $to_date)
    {

        $toDateExpected = Carbon::createFromDate($from_date)->addYear();
        if (strtotime($to_date) > strtotime($toDateExpected)) //to_date is bigger than 1 year
        {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'to_date must be just 1 year bigger not more.';
    }
}
