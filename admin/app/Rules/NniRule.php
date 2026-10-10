<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NniRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regex = config('multishop.nni_regex');

        if (!is_string($value) || !preg_match($regex, trim($value))) {
            $fail(translate('invalid_nni_format'));
        }
    }
}
