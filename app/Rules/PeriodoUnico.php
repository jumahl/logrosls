<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Periodo;

class PeriodoUnico implements ValidationRule
{
    private $numeroPeríodo;
    private $corte;
    private $añoEscolar;
    private $ignoreId;

    public function __construct($numeroPeríodo, $corte, $añoEscolar, $ignoreId = null)
    {
        $this->numeroPeríodo = $numeroPeríodo;
        $this->corte = $corte;
        $this->añoEscolar = $añoEscolar;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Periodo::where('numero_periodo', $this->numeroPeríodo)
            ->where('corte', $this->corte)
            ->where('año_escolar', $this->añoEscolar);
            
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        
        if ($query->exists()) {
            $fail('Ya existe un período con esta combinación de año escolar, número de período y corte.');
        }
    }
}
