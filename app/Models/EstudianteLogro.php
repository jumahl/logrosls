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

    public function calcularNivelDesempeno()
    {
        if (!$this->nota) {
            return null;
        }

        return match(true) {
            $this->nota >= 4.5 => 'Superior',
            $this->nota >= 4.0 => 'Alto',
            $this->nota >= 3.0 => 'Básico',
            default => 'Bajo'
        };
    }
}
