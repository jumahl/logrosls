<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\EstudianteLogro;

class EstudianteLogroUnico implements ValidationRule
{
    private $estudianteId;
    private $logroId;
    private $periodoId;
    private $ignoreId;

    public function __construct($estudianteId, $logroId, $periodoId, $ignoreId = null)
    {
        $this->estudianteId = $estudianteId;
        $this->logroId = $logroId;
        $this->periodoId = $periodoId;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = EstudianteLogro::where('estudiante_id', $this->estudianteId)
            ->where('logro_id', $this->logroId)
            ->where('periodo_id', $this->periodoId);
            
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        
        if ($query->exists()) {
            $fail('Este estudiante ya tiene una calificación para este logro en el período seleccionado.');
        }
    }
}
