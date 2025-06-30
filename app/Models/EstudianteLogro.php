<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteLogro extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'logro_id',
        'periodo_id',
        'nivel_desempeno',
        'observaciones',
        'fecha_asignacion'
    ];

    protected $casts = [
        'fecha_asignacion' => 'date'
    ];

    /**
     * Obtener el estudiante al que pertenece este logro.
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Obtener el logro asignado.
     */
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtener el valor numérico del nivel de desempeño para cálculos.
     */
    public function getValorNumericoAttribute()
    {
        return match($this->nivel_desempeno) {
            'Superior' => 5.0,
            'Alto' => 4.0,
            'Básico' => 3.0,
            'Bajo' => 2.0,
            default => 0.0
        };
    }

    /**
     * Obtener el color del nivel de desempeño para la interfaz.
     */
    public function getColorNivelAttribute()
    {
        return match($this->nivel_desempeno) {
            'Superior' => 'success',
            'Alto' => 'info',
            'Básico' => 'warning',
            'Bajo' => 'danger',
            default => 'gray'
        };
    }
}
