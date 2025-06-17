<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Logro extends Model
{
    use HasFactory;

    protected $fillable = [
        'grado_id',
        'periodo_id',
        'materia_id',
        'codigo',
        'competencia',
        'tema',
        'indicador_desempeno',
        'dimension',
    ];

    /**
     * Obtener el grado al que pertenece el logro.
     */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Obtener el periodo al que pertenece el logro.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtener la materia a la que pertenece el logro.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Obtener los estudiantes que han alcanzado este logro.
     */
    public function estudianteLogros(): HasMany
    {
        return $this->hasMany(EstudianteLogro::class);
    }

    /**
     * Obtener los estudiantes que han alcanzado este logro.
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_logros')
            ->withPivot('fecha_asignacion', 'observaciones')
            ->withTimestamps();
    }
}
