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
        'codigo',
        'titulo',
        'descripcion',
        'materia_id',
        'nivel',
        'tipo',
        'activo',
        'competencia',
        'tema',
        'indicador_desempeno',
        'dimension'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Obtener la materia a la que pertenece el logro.
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /**
     * Obtener los periodos en los que se usa este logro.
     */
    public function periodos(): BelongsToMany
    {
        return $this->belongsToMany(Periodo::class)
            ->withTimestamps();
    }

    /**
     * Obtener los estudiantes que tienen este logro.
     */
    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_logros')
            ->withPivot('fecha_asignacion', 'observaciones')
            ->withTimestamps();
    }

    /**
     * Obtener los registros de logros de estudiantes.
     */
    public function estudianteLogros(): HasMany
    {
        return $this->hasMany(EstudianteLogro::class);
    }

    /**
     * Scope para filtrar logros activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por grado.
     */
    public function scopePorGrado($query, $gradoId)
    {
        return $query->whereHas('materia.grado', function ($q) use ($gradoId) {
            $q->where('grados.id', $gradoId);
        });
    }

    /**
     * Scope para filtrar por materia.
     */
    public function scopePorMateria($query, $materiaId)
    {
        return $query->where('materia_id', $materiaId);
    }

    /**
     * Scope para filtrar por nivel de dificultad.
     */
    public function scopePorNivelDificultad($query, $nivel)
    {
        return $query->where('nivel_dificultad', $nivel);
    }

    /**
     * Scope para filtrar por tipo de logro.
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para ordenar por orden de presentación.
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($logro) {
            // Eliminar en cascada los logros de estudiantes
            $logro->estudianteLogros()->delete();
            
            // Desvincular de los períodos
            $logro->periodos()->detach();
        });
    }
}
