<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

/**
 * Servicio para cache de consultas académicas frecuentes
 */
class CacheAcademicoService
{
    protected int $tiempoCache = 300; // 5 minutos por defecto

    /**
     * Obtiene y cachea los grados de un profesor
     */
    public function gradosDeProfesor(int $profesorId): array
    {
        return Cache::remember(
            "profesor_grados_{$profesorId}",
            $this->tiempoCache,
            function () use ($profesorId) {
                $profesor = User::find($profesorId);
                if (!$profesor) {
                    return [];
                }

                return $profesor->materias()
                    ->join('grado_materia', 'materias.id', '=', 'grado_materia.materia_id')
                    ->distinct()
                    ->pluck('grado_materia.grado_id')
                    ->toArray();
            }
        );
    }

    /**
     * Obtiene y cachea las materias de un profesor
     */
    public function materiasDeProfesor(int $profesorId): array
    {
        return Cache::remember(
            "profesor_materias_{$profesorId}",
            $this->tiempoCache,
            function () use ($profesorId) {
                $profesor = User::find($profesorId);
                if (!$profesor) {
                    return [];
                }

                return $profesor->materias()->pluck('id')->toArray();
            }
        );
    }

    /**
     * Obtiene y cachea los IDs de materias de un profesor
     */
    public function materiasIdsDeProfesor(int $profesorId): array
    {
        return Cache::remember(
            "profesor_materias_ids_{$profesorId}",
            $this->tiempoCache,
            function () use ($profesorId) {
                $profesor = User::find($profesorId);
                if (!$profesor) {
                    return [];
                }

                return $profesor->materias()->pluck('id')->toArray();
            }
        );
    }

    /**
     * Limpia el cache de un profesor específico
     */
    public function limpiarCacheProfesor(int $profesorId): void
    {
        $keys = [
            "profesor_grados_{$profesorId}",
            "profesor_materias_{$profesorId}",
            "profesor_materias_ids_{$profesorId}"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Limpia todo el cache académico
     */
    public function limpiarTodoCache(): void
    {
        $patterns = [
            'profesor_grados_*',
            'profesor_materias_*',
            'profesor_materias_ids_*'
        ];

        foreach ($patterns as $pattern) {
            Cache::flush(); // En producción usar un método más específico
        }
    }

    /**
     * Precarga cache para todos los profesores activos
     */
    public function precargaCacheProfesores(): void
    {
        $profesores = User::whereHas('roles', function ($q) {
            $q->where('name', 'profesor');
        })->pluck('id');

        foreach ($profesores as $profesorId) {
            $this->gradosDeProfesor($profesorId);
            $this->materiasDeProfesor($profesorId);
            $this->materiasIdsDeProfesor($profesorId);
        }
    }
}
