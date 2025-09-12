<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para optimizar consultas relacionadas con evaluaciones académicas
 */
trait OptimizaConsultasAcademicas
{
    /**
     * Scope para cargar estudiantes con sus relaciones optimizadas
     */
    public function scopeConDatosCompletos(Builder $query): Builder
    {
        return $query->select([
                'id', 'nombre', 'apellido', 'documento', 'fecha_nacimiento',
                'grado_id', 'direccion', 'telefono', 'email', 'activo', 'created_at'
            ])
            ->with(['grado:id,nombre,grupo']);
    }

    /**
     * Scope para cargar solo datos esenciales de estudiantes
     */
    public function scopeConDatosBasicos(Builder $query): Builder
    {
        return $query->select(['id', 'nombre', 'apellido', 'documento', 'grado_id'])
            ->with(['grado:id,nombre,grupo']);
    }

    /**
     * Scope para filtrar estudiantes por grados de un profesor
     */
    public function scopeDeProfesor(Builder $query, $profesorId): Builder
    {
        return $query->whereIn('grado_id', function ($subQuery) use ($profesorId) {
            $subQuery->select('grado_materia.grado_id')
                ->from('grado_materia')
                ->join('materias', 'grado_materia.materia_id', '=', 'materias.id')
                ->where('materias.docente_id', $profesorId)
                ->distinct();
        });
    }

    /**
     * Scope para estudiantes activos
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para ordenamiento estándar de estudiantes
     */
    public function scopeOrdenadosPorNombre(Builder $query): Builder
    {
        return $query->orderBy('grado_id')
            ->orderBy('apellido')
            ->orderBy('nombre');
    }
}
