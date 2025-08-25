<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Periodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'corte',
    'anio_escolar',
        'numero_periodo',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    'anio_escolar' => 'integer',
        'numero_periodo' => 'integer'
    ];

    /**
     * Obtener el nombre del período generado dinámicamente.
     */
    public function getNombreAttribute()
    {
        return "Período {$this->numero_periodo}";
    }

    /**
     * Obtener los logros de este periodo.
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(Logro::class)
            ->withTimestamps();
    }

    /**
     * Obtener los desempeños de materias de este período.
     */
    public function desempenosMateria(): HasMany
    {
        return $this->hasMany(DesempenoMateria::class);
    }

    /**
     * Obtener los logros de estudiantes a través de desempeños de materia.
     */
    public function estudianteLogros(): HasManyThrough
    {
        return $this->hasManyThrough(
            EstudianteLogro::class,
            DesempenoMateria::class,
            'periodo_id',
            'desempeno_materia_id',
            'id',
            'id'
        );
    }

    /**
     * Scope para filtrar períodos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por año escolar.
     */
    public function scopePorAñoEscolar($query, $año)
    {
        return $query->where('anio_escolar', $año);
    }

    /**
     * Scope para filtrar por número de período.
     */
    public function scopePorNumeroPeriodo($query, $numero)
    {
        return $query->where('numero_periodo', $numero);
    }

    /**
     * Scope para filtrar por corte.
     */
    public function scopePorCorte($query, $corte)
    {
        return $query->where('corte', $corte);
    }

    /**
     * Obtener el período completo (nombre + corte + año).
     */
    public function getPeriodoCompletoAttribute()
    {
    return "{$this->nombre} - {$this->corte} {$this->anio_escolar}";
    }

    /**
     * Obtener el período anterior del mismo año escolar.
     */
    public function getPeriodoAnteriorAttribute()
    {
        if ($this->corte === 'Segundo Corte' && $this->numero_periodo === 1) {
            // Retornar el primer corte del primer período
            return static::where('anio_escolar', $this->anio_escolar)
                ->where('numero_periodo', 1)
                ->where('corte', 'Primer Corte')
                ->first();
        } elseif ($this->corte === 'Primer Corte' && $this->numero_periodo === 2) {
            // Retornar el segundo corte del primer período
            return static::where('anio_escolar', $this->anio_escolar)
                ->where('numero_periodo', 1)
                ->where('corte', 'Segundo Corte')
                ->first();
        }
        
        return null;
    }

    /**
     * Validar que las fechas sean coherentes.
     */
    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($periodo) {
            if ($periodo->fecha_inicio && $periodo->fecha_fin) {
                if ($periodo->fecha_inicio >= $periodo->fecha_fin) {
                    throw new \Exception('La fecha de fin debe ser posterior a la fecha de inicio.');
                }
            }
        });
        
        static::deleting(function ($periodo) {
            // Desvincular los logros del período
            $periodo->logros()->detach();
            
            // Eliminar en cascada los desempeños del período
            $periodo->desempenosMateria()->delete();
        });
    }
}
