<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FechaFinPosteriorInicio implements ValidationRule
{
    private $fechaInicio;

    public function __construct($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && $this->fechaInicio) {
            $fechaInicio = \Carbon\Carbon::parse($this->fechaInicio);
            $fechaFin = \Carbon\Carbon::parse($value);
            
            if ($fechaFin->lte($fechaInicio)) {
                $fail('La fecha de fin debe ser posterior a la fecha de inicio.');
            }
        }
    }
}
