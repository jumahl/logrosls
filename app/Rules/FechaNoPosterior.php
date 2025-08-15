<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FechaNoPosterior implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && \Carbon\Carbon::parse($value)->isFuture()) {
            $fail('La fecha no puede ser posterior a la fecha actual.');
        }
    }
}
