<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    /**
     * Obtener los estudiantes de este grado.
     */
    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    /**
     * Obtener las materias de este grado.
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    /**
     * Obtener los logros de este grado.
     */
    public function logros(): HasMany
    {
        return $this->hasMany(Logro::class);
    }
}
