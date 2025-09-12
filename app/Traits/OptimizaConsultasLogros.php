<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para optimizar consultas relacionadas con logros y evaluaciones
 */
trait OptimizaConsultasLogros
{
    /**
     * Scope para cargar logros con sus relaciones optimizadas
     */
    public function scopeConDatosCompletos(Builder $query): Builder
    {
        return $query->select([
                'id', 'codigo', 'titulo', 'desempeno', 'materia_id', 
                'orden', 'activo', 'created_at'
            ])
            ->with([
                'materia:id,nombre,codigo,docente_id',
                'materia.docente:id,name'
            ]);
    }

    /**
     * Scope para logros activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar logros por materias de un profesor
     */
    public function scopeDeProfesor(Builder $query, $profesorId): Builder
    {
        return $query->whereHas('materia', function ($q) use ($profesorId) {
            $q->where('docente_id', $profesorId);
        });
    }

    /**
     * Scope para ordenamiento estándar de logros
     */
    public function scopeOrdenadosPorMateria(Builder $query): Builder
    {
        return $query->orderBy('materia_id')
            ->orderBy('orden')
            ->orderBy('codigo');
    }

    /**
     * Scope para logros de un grado específico
     */
    public function scopeDeGrado(Builder $query, $gradoId): Builder
    {
        return $query->whereHas('materia.grados', function ($q) use ($gradoId) {
            $q->where('grado_id', $gradoId);
        });
    }
}
