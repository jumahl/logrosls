<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesempenoMateria extends Model
{
    use HasFactory;

    protected $table = 'desempenos_materia';

    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'periodo_id',
        'nivel_desempeno',
        'observaciones_finales',
        'fecha_asignacion',
        'estado',
        'locked_at',
        'locked_by'
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'locked_at' => 'datetime'
    ];

    /**
     * Obtener el estudiante al que pertenece este desempeño.
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Obtener la materia de este desempeño.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Obtener el periodo de este desempeño.
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    /**
     * Obtener el usuario que bloqueó este desempeño.
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Obtener los logros asociados a este desempeño.
     */
    public function estudianteLogros(): HasMany
    {
        return $this->hasMany(EstudianteLogro::class, 'desempeno_materia_id');
    }

    /**
     * Scope para filtrar por estudiante
     */
    public function scopePorEstudiante($query, $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    /**
     * Scope para filtrar por materia
     */
    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    /**
     * Scope para filtrar por periodo
     */
    public function scopePorPeriodo($query, $periodoId)
    {
        return $query->where('periodo_id', $periodoId);
    }

    /**
     * Scope para filtrar por nivel de desempeño
     */
    public function scopePorNivelDesempeno($query, $nivel)
    {
        return $query->where('nivel_desempeno', $nivel);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para desempeños publicados
     */
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    /**
     * Scope para desempeños en borrador
     */
    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    /**
     * Scope para desempeños bloqueados
     */
    public function scopeBloqueados($query)
    {
        return $query->whereNotNull('locked_at');
    }

    /**
     * Obtener el valor numérico del nivel de desempeño para cálculos.
     */
    public function getValorNumericoAttribute()
    {
        return match($this->nivel_desempeno) {
            'E' => 5.0, // Excelente
            'S' => 4.0, // Sobresaliente
            'A' => 3.0, // Aceptable
            'I' => 2.0, // Insuficiente
            null => 0.0,
            '' => 0.0,
            default => 0.0
        };
    }

    /**
     * Obtener el color del nivel de desempeño para la interfaz.
     */
    public function getColorNivelAttribute()
    {
        return match($this->nivel_desempeno) {
            'E' => 'success', // Excelente - verde
            'S' => 'info',    // Sobresaliente - azul
            'A' => 'warning', // Aceptable - amarillo
            'I' => 'danger',  // Insuficiente - rojo
            null => 'gray',
            '' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Obtener el nombre completo del nivel de desempeño.
     */
    public function getNivelDesempenoCompletoAttribute()
    {
        return match($this->nivel_desempeno) {
            'E' => 'Excelente',
            'S' => 'Sobresaliente',
            'A' => 'Aceptable',
            'I' => 'Insuficiente',
            null => 'No asignado',
            '' => 'No asignado',
            default => 'No definido'
        };
    }

    /**
     * Obtener el color del estado para la interfaz.
     */
    public function getColorEstadoAttribute()
    {
        return match($this->estado) {
            'borrador' => 'warning',
            'publicado' => 'success',
            'revisado' => 'info',
            default => 'gray'
        };
    }

    /**
     * Verificar si el desempeño está bloqueado.
     */
    public function getBloqueadoAttribute()
    {
        return !is_null($this->locked_at);
    }

    /**
     * Accessor para observaciones (alias de observaciones_finales)
     */
    public function getObservacionesAttribute()
    {
        return $this->observaciones_finales;
    }

    /**
     * Mutator para observaciones (alias de observaciones_finales)
     */
    public function setObservacionesAttribute($value)
    {
        $this->observaciones_finales = $value;
    }

    /**
     * Verificar si el desempeño puede ser editado.
     */
    public function getEditableAttribute()
    {
        return is_null($this->locked_at) && $this->estado !== 'publicado';
    }

    /**
     * Bloquear el desempeño.
     */
    public function bloquear($userId = null)
    {
        $this->update([
            'locked_at' => now(),
            'locked_by' => $userId ?: auth()->id(),
            'estado' => 'publicado'
        ]);
    }

    /**
     * Desbloquear el desempeño.
     */
    public function desbloquear()
    {
        $this->update([
            'locked_at' => null,
            'locked_by' => null,
            'estado' => 'borrador'
        ]);
    }

    /**
     * Obtener estadísticas de logros para este desempeño.
     */
    public function getEstadisticasLogrosAttribute()
    {
        $logros = $this->estudianteLogros;
        
        return [
            'total_logros' => $logros->count(),
            'logros_alcanzados' => $logros->where('alcanzado', true)->count(),
            'porcentaje_alcanzado' => $logros->count() > 0 
                ? round(($logros->where('alcanzado', true)->count() / $logros->count()) * 100, 1) 
                : 0
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($desempeno) {
            // Eliminar en cascada los logros asociados
            $desempeno->estudianteLogros()->delete();
        });
    }
}
