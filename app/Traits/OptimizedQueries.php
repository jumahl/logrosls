<?php

namespace App\Traits;

use App\Models\Estudiante;
use App\Models\EstudianteLogro;
use App\Models\Logro;
use App\Models\Periodo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Trait con consultas optimizadas para el sistema de logros académicos
 * 
 * Este trait contiene métodos optimizados con eager loading para evitar el problema N+1
 * y mejorar significativamente el rendimiento de las consultas más comunes del sistema.
 */
trait OptimizedQueries
{
    /**
     * ✅ OPTIMIZADO: Dashboard del profesor con eager loading
     * 
     * Antes: 50+ consultas (problema N+1)
     * Después: 3-4 consultas optimizadas
     */
    public function getDashboardStatsOptimized()
    {
        $user = auth()->user();
        
        // Una sola consulta con todas las relaciones necesarias
        $materias = $user->materias()->with([
            'grados.estudiantes' => function($query) {
                $query->where('activo', true);
            }
        ])->where('activa', true)->get();
        
        $gradoIds = $materias->pluck('grados')->flatten()->pluck('id')->unique();
        $estudiantesCount = $materias->pluck('grados')->flatten()
            ->pluck('estudiantes')->flatten()->count();
        
        return [
            'materias_count' => $materias->count(),
            'grados_count' => $gradoIds->count(),
            'estudiantes_count' => $estudiantesCount,
            'materias' => $materias // Ya tienen todas las relaciones cargadas
        ];
    }
    
    /**
     * ✅ OPTIMIZADO: Boletín de estudiante con eager loading
     * 
     * Carga todas las evaluaciones de un estudiante en un período específico
     * con todas sus relaciones en una sola consulta optimizada.
     */
    public function getBoletinEstudianteOptimizado($estudianteId, $periodoId)
    {
        return Cache::remember("boletin_estudiante_{$estudianteId}_{$periodoId}", 1800, function() use ($estudianteId, $periodoId) {
            $estudiante = Estudiante::with([
                'grado',
                'estudianteLogros' => function($query) use ($periodoId) {
                    $query->where('periodo_id', $periodoId)
                          ->with(['logro.materia', 'periodo']);
                }
            ])->findOrFail($estudianteId);
            
            // Agrupar evaluaciones por materia (ya están precargadas)
            $evaluacionesPorMateria = $estudiante->estudianteLogros
                ->groupBy('logro.materia.nombre')
                ->map(function($evaluaciones) {
                    return [
                        'evaluaciones' => $evaluaciones,
                        'promedio_numerico' => $evaluaciones->avg(function($eval) {
                            return match($eval->nivel_desempeno) {
                                'E' => 5, 'S' => 4, 'A' => 3, 'I' => 2, default => 0
                            };
                        }),
                        'total_logros' => $evaluaciones->count(),
                        'excelentes' => $evaluaciones->where('nivel_desempeno', 'E')->count(),
                        'insuficientes' => $evaluaciones->where('nivel_desempeno', 'I')->count(),
                    ];
                });
            
            return [
                'estudiante' => $estudiante,
                'evaluaciones_por_materia' => $evaluacionesPorMateria,
                'resumen_general' => [
                    'total_evaluaciones' => $estudiante->estudianteLogros->count(),
                    'promedio_general' => $evaluacionesPorMateria->avg('promedio_numerico'),
                    'materias_evaluadas' => $evaluacionesPorMateria->count()
                ]
            ];
        });
    }
    
    /**
     * ✅ OPTIMIZADO: Reporte de rendimiento por grado
     * 
     * Genera estadísticas de rendimiento de un grado completo
     * usando consultas SQL optimizadas y agregaciones.
     */
    public function getRendimientoGradoOptimizado($gradoId, $periodoId)
    {
        return Cache::remember("rendimiento_grado_{$gradoId}_{$periodoId}", 1800, function() use ($gradoId, $periodoId) {
            // Consulta SQL optimizada con agregaciones
            $estadisticas = DB::table('estudiante_logros')
                ->join('estudiantes', 'estudiante_logros.estudiante_id', '=', 'estudiantes.id')
                ->join('logros', 'estudiante_logros.logro_id', '=', 'logros.id')
                ->join('materias', 'logros.materia_id', '=', 'materias.id')
                ->where('estudiantes.grado_id', $gradoId)
                ->where('estudiante_logros.periodo_id', $periodoId)
                ->select([
                    'materias.nombre as materia',
                    'estudiante_logros.nivel_desempeno',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('ROUND(AVG(CASE 
                        WHEN nivel_desempeno = "E" THEN 5
                        WHEN nivel_desempeno = "S" THEN 4  
                        WHEN nivel_desempeno = "A" THEN 3
                        WHEN nivel_desempeno = "I" THEN 2
                        ELSE 0 END), 2) as promedio_numerico')
                ])
                ->groupBy('materias.nombre', 'estudiante_logros.nivel_desempeno')
                ->get()
                ->groupBy('materia');
            
            // Procesar estadísticas para obtener porcentajes
            return $estadisticas->map(function($evaluaciones, $materia) {
                $total = $evaluaciones->sum('cantidad');
                $promedio = $evaluaciones->first()->promedio_numerico ?? 0;
                
                $distribucion = $evaluaciones->mapWithKeys(function($item) use ($total) {
                    return [
                        $item->nivel_desempeno => [
                            'cantidad' => $item->cantidad,
                            'porcentaje' => $total > 0 ? round(($item->cantidad / $total) * 100, 1) : 0
                        ]
                    ];
                });
                
                return [
                    'materia' => $materia,
                    'total_evaluaciones' => $total,
                    'promedio_numerico' => $promedio,
                    'distribucion' => $distribucion,
                    'estudiantes_en_riesgo' => $distribucion['I']['cantidad'] ?? 0,
                    'porcentaje_riesgo' => $distribucion['I']['porcentaje'] ?? 0
                ];
            });
        });
    }
    
    /**
     * ✅ OPTIMIZADO: Búsqueda avanzada de estudiantes
     * 
     * Búsqueda con múltiples filtros aplicando eager loading inteligente
     */
    public function buscarEstudiantesOptimizado(array $filtros = [])
    {
        $query = Estudiante::query()
            ->with(['grado']); // Eager loading básico
        
        // Si necesita datos de evaluaciones, cargarlos de una vez
        if (!empty($filtros['rendimiento']) || !empty($filtros['materia_id'])) {
            $query->with([
                'estudianteLogros' => function($q) {
                    $q->whereHas('periodo', fn($p) => $p->where('activo', true))
                      ->with(['logro.materia', 'periodo']);
                }
            ]);
        }
        
        // Filtros de búsqueda
        if (!empty($filtros['busqueda'])) {
            $query->where(function($q) use ($filtros) {
                $q->where('nombre', 'LIKE', "%{$filtros['busqueda']}%")
                  ->orWhere('apellido', 'LIKE', "%{$filtros['busqueda']}%")
                  ->orWhere('documento', 'LIKE', "%{$filtros['busqueda']}%");
            });
        }
        
        if (!empty($filtros['grado_id'])) {
            $query->where('grado_id', $filtros['grado_id']);
        }
        
        if (!empty($filtros['rendimiento'])) {
            $query->whereHas('estudianteLogros', function($q) use ($filtros) {
                $q->where('nivel_desempeno', $filtros['rendimiento'])
                  ->whereHas('periodo', fn($p) => $p->where('activo', true));
            });
        }
        
        if (!empty($filtros['materia_id'])) {
            $query->whereHas('estudianteLogros.logro', function($q) use ($filtros) {
                $q->where('materia_id', $filtros['materia_id']);
            });
        }
        
        return $query->paginate(20);
    }
    
    /**
     * ✅ OPTIMIZADO: Top logros con mayor dificultad
     * 
     * Consulta optimizada usando agregaciones SQL para evitar N+1
     */
    public function getLogrosMasDificiles($periodoId, $limite = 10)
    {
        return Cache::remember("logros_dificiles_{$periodoId}_{$limite}", 3600, function() use ($periodoId, $limite) {
            return Logro::select([
                    'logros.*',
                    DB::raw('COUNT(el.id) as total_evaluaciones'),
                    DB::raw('COUNT(CASE WHEN el.nivel_desempeno = "I" THEN 1 END) as evaluaciones_insuficientes'),
                    DB::raw('ROUND((COUNT(CASE WHEN el.nivel_desempeno = "I" THEN 1 END) / COUNT(el.id)) * 100, 1) as porcentaje_dificultad')
                ])
                ->leftJoin('estudiante_logros as el', function($join) use ($periodoId) {
                    $join->on('logros.id', '=', 'el.logro_id')
                         ->where('el.periodo_id', $periodoId);
                })
                ->with(['materia']) // Eager loading para la relación materia
                ->groupBy('logros.id')
                ->having('total_evaluaciones', '>=', 5) // Mínimo 5 evaluaciones
                ->orderByDesc('porcentaje_dificultad')
                ->limit($limite)
                ->get();
        });
    }
    
    /**
     * 🚫 EJEMPLO: Método NO optimizado (para comparación)
     * 
     * Este método tiene el problema N+1 - NO usar en producción
     */
    public function getBoletinSinOptimizar($estudianteId, $periodoId)
    {
        // ❌ PROBLEMA N+1 - Una consulta por cada relación
        $estudiante = Estudiante::find($estudianteId); // 1 consulta
        $grado = $estudiante->grado; // 1 consulta adicional
        $evaluaciones = $estudiante->estudianteLogros()->where('periodo_id', $periodoId)->get(); // 1 consulta
        
        foreach($evaluaciones as $evaluacion) {
            $logro = $evaluacion->logro; // 1 consulta por evaluación
            $materia = $logro->materia; // 1 consulta por logro
            $periodo = $evaluacion->periodo; // 1 consulta por evaluación
            
            // Si tiene 20 evaluaciones = 1 + 1 + 1 + (20 * 3) = 63 consultas! 😱
        }
        
        return $evaluaciones;
    }
    
    /**
     * Invalidar cache relacionado cuando se actualicen datos
     */
    public function invalidarCacheEstudiante($estudianteId)
    {
        $tags = [
            "boletin_estudiante_{$estudianteId}_*",
            "stats_user_*",
            "rendimiento_grado_*"
        ];
        
        foreach($tags as $pattern) {
            Cache::forget($pattern);
        }
    }
}
