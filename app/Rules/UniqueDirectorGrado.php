<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueDirectorGrado implements ValidationRule
{
    protected $ignoreUserId;

    public function __construct($ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return; // Es opcional ser director de grupo
        }

        $query = DB::table('users')
            ->where('director_grado_id', $value);

        if ($this->ignoreUserId) {
            $query->where('id', '!=', $this->ignoreUserId);
        }

        if ($query->exists()) {
            $fail('Este grado ya tiene un director de grupo asignado.');
        }
    }
} 