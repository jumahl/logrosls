<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteLogro extends Model
{
    use HasFactory;

    protected $fillable = [
        'logro_id',
        'desempeno_materia_id',
        'alcanzado'
    ];

    protected $casts = [
        'alcanzado' => 'boolean'
    ];

    /**
     * Obtener el logro asignado.
     */
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class);
    }

    /**
     * Obtener el desempeño de materia al que pertenece este logro.
     */
    public function desempenoMateria(): BelongsTo
    {
        return $this->belongsTo(DesempenoMateria::class);
    }

    /**
     * Accessors que delegan a través de DesempenoMateria
     */
    public function getEstudianteAttribute()
    {
        return $this->desempenoMateria?->estudiante;
    }

    public function getPeriodoAttribute()
    {
        return $this->desempenoMateria?->periodo;
    }

    public function getMateriaAttribute()
    {
        return $this->desempenoMateria?->materia;
    }

    /**
     * Scope para filtrar por logro
     */
    public function scopePorLogro($query, $logroId)
    {
        return $query->where('logro_id', $logroId);
    }

    /**
     * Scope para filtrar por desempeño de materia
     */
    public function scopePorDesempenoMateria($query, $desempenoMateriaId)
    {
        return $query->where('desempeno_materia_id', $desempenoMateriaId);
    }

    /**
     * Scope para filtrar logros alcanzados
     */
    public function scopeAlcanzados($query)
    {
        return $query->where('alcanzado', true);
    }

    /**
     * Scope para filtrar logros no alcanzados
     */
    public function scopeNoAlcanzados($query)
    {
        return $query->where('alcanzado', false);
    }

    /**
     * Obtener el nivel de desempeño desde la relación con DesempenoMateria
     */
    public function getNivelDesempenoAttribute()
    {
        return $this->desempenoMateria?->nivel_desempeno;
    }

    /**
     * Obtener las observaciones desde la relación con DesempenoMateria
     */
    public function getObservacionesAttribute()
    {
        return $this->desempenoMateria?->observaciones_finales;
    }

    /**
     * Obtener la fecha de asignación desde la relación con DesempenoMateria
     */
    public function getFechaAsignacionAttribute()
    {
        return $this->desempenoMateria?->created_at;
    }

    /**
     * Accessor para determinar si la evaluación está completada
     */
    public function getEvaluadoAttribute()
    {
        return !is_null($this->desempenoMateria?->observaciones_finales);
    }

    /**
     * Obtener el valor numérico del nivel de desempeño para cálculos.
     */
    public function getValorNumericoAttribute()
    {
        if (!$this->desempenoMateria) {
            return 0.0;
        }

        return match($this->desempenoMateria->nivel_desempeno) {
            'E' => 5.0, // Excelente
            'S' => 4.0, // Sobresaliente
            'A' => 3.0, // Aceptable
            'I' => 2.0, // Insuficiente
            default => 0.0
        };
    }

    /**
     * Obtener el color del nivel de desempeño para la interfaz.
     */
    public function getColorNivelAttribute()
    {
        if (!$this->desempenoMateria) {
            return 'gray';
        }

        return match($this->desempenoMateria->nivel_desempeno) {
            'E' => 'success', // Excelente - verde
            'S' => 'info',    // Sobresaliente - azul
            'A' => 'warning', // Aceptable - amarillo
            'I' => 'danger',  // Insuficiente - rojo
            default => 'gray'
        };
    }

    /**
     * Obtener el nombre completo del nivel de desempeño.
     */
    public function getNivelDesempenoCompletoAttribute()
    {
        if (!$this->desempenoMateria) {
            return 'No definido';
        }

        return match($this->desempenoMateria->nivel_desempeno) {
            'E' => 'Excelente',
            'S' => 'Sobresaliente',
            'A' => 'Aceptable',
            'I' => 'Insuficiente',
            default => 'No definido'
        };
    }
}
