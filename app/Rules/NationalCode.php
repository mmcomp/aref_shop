<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NationalCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->validateNationalCode($value)) {
            $fail('The :attribute is not correct!');
        }
    }

    private function validateNationalCode(string $input): bool
    {
        if (strlen($input) !== 10) {
            return false;
        }
        $digits = str_split($input);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (10 - $i) * intVal($digits[$i]);
        }
        $remaning = $sum % 11;
        if ($remaning < 2) {
            if (intVal($digits[9]) !== $remaning) {
                return false;
            }
        } else {
            if (intVal($digits[9]) !== 11 - $remaning) {
                return false;
            }
        }
        return true;
    }
}
