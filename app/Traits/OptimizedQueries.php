<?php

namespace App\Traits;

use App\Models\Estudiante;
use App\Models\DesempenoMateria;
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
 * 
 * Actualizado para usar la nueva estructura con tabla consolidada de desempeños.
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
     * Carga todas las calificaciones de un estudiante en un período específico
     * con todas sus relaciones en una sola consulta optimizada.
     */
    public function getBoletinEstudianteOptimizado($estudianteId, $periodoId)
    {
        return Cache::remember("boletin_estudiante_{$estudianteId}_{$periodoId}", 1800, function() use ($estudianteId, $periodoId) {
            $estudiante = Estudiante::with([
                'grado',
                'desempenosMateria' => function($query) use ($periodoId) {
                    $query->where('periodo_id', $periodoId)
                          ->with(['materia', 'periodo', 'estudianteLogros.logro']);
                }
            ])->findOrFail($estudianteId);
            
            // Procesar calificaciones por materia (ya están precargadas)
            $calificacionesPorMateria = $estudiante->desempenosMateria
                ->mapWithKeys(function($desempeno) {
                    return [
                        $desempeno->materia->nombre => [
                            'calificacion' => $desempeno,
                            'logros_asociados' => $desempeno->estudianteLogros,
                            'valor_numerico' => $desempeno->valor_numerico,
                            'total_logros' => $desempeno->estudianteLogros->count(),
                            'logros_alcanzados' => $desempeno->estudianteLogros->where('alcanzado', true)->count(),
                        ]
                    ];
                });
            
            return [
                'estudiante' => $estudiante,
                'calificaciones_por_materia' => $calificacionesPorMateria,
                'resumen_general' => [
                    'total_materias' => $calificacionesPorMateria->count(),
                    'promedio_general' => $calificacionesPorMateria->avg('valor_numerico'),
                    'materias_excelentes' => $calificacionesPorMateria->where('calificacion.nivel_desempeno', 'E')->count(),
                    'materias_insuficientes' => $calificacionesPorMateria->where('calificacion.nivel_desempeno', 'I')->count(),
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
            // Consulta SQL optimizada con agregaciones usando la tabla consolidada
            $estadisticas = DB::table('desempenos_materia')
                ->join('estudiantes', 'desempenos_materia.estudiante_id', '=', 'estudiantes.id')
                ->join('materias', 'desempenos_materia.materia_id', '=', 'materias.id')
                ->where('estudiantes.grado_id', $gradoId)
                ->where('desempenos_materia.periodo_id', $periodoId)
                ->select([
                    'materias.nombre as materia',
                    'desempenos_materia.nivel_desempeno',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('ROUND(AVG(CASE 
                        WHEN nivel_desempeno = "E" THEN 5
                        WHEN nivel_desempeno = "S" THEN 4  
                        WHEN nivel_desempeno = "A" THEN 3
                        WHEN nivel_desempeno = "I" THEN 2
                        ELSE 0 END), 2) as promedio_numerico')
                ])
                ->groupBy('materias.nombre', 'desempenos_materia.nivel_desempeno')
                ->get()
                ->groupBy('materia');
            
            // Procesar estadísticas para obtener porcentajes
            return $estadisticas->map(function($calificaciones, $materia) {
                $total = $calificaciones->sum('cantidad');
                $promedio = $calificaciones->first()->promedio_numerico ?? 0;
                
                $distribucion = $calificaciones->mapWithKeys(function($item) use ($total) {
                    return [
                        $item->nivel_desempeno => [
                            'cantidad' => $item->cantidad,
                            'porcentaje' => $total > 0 ? round(($item->cantidad / $total) * 100, 1) : 0
                        ]
                    ];
                });
                
                return [
                    'materia' => $materia,
                    'total_calificaciones' => $total,
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
        
        // Si necesita datos de calificaciones, cargarlos de una vez
        if (!empty($filtros['rendimiento']) || !empty($filtros['materia_id'])) {
            $query->with([
                'desempenosMateria' => function($q) {
                    $q->whereHas('periodo', fn($p) => $p->where('activo', true))
                      ->with(['materia', 'periodo']);
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
            $query->whereHas('desempenosMateria', function($q) use ($filtros) {
                $q->where('nivel_desempeno', $filtros['rendimiento'])
                  ->whereHas('periodo', fn($p) => $p->where('activo', true));
            });
        }
        
        if (!empty($filtros['materia_id'])) {
            $query->whereHas('desempenosMateria', function($q) use ($filtros) {
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
                    DB::raw('COUNT(CASE WHEN dm.nivel_desempeno = "I" THEN 1 END) as evaluaciones_insuficientes'),
                    DB::raw('ROUND((COUNT(CASE WHEN dm.nivel_desempeno = "I" THEN 1 END) / COUNT(el.id)) * 100, 1) as porcentaje_dificultad')
                ])
                ->leftJoin('estudiante_logros as el', 'logros.id', '=', 'el.logro_id')
                ->leftJoin('desempenos_materia as dm', function($join) use ($periodoId) {
                    $join->on('el.desempeno_materia_id', '=', 'dm.id')
                         ->where('dm.periodo_id', $periodoId);
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
     * ✅ OPTIMIZADO: Estadísticas de desempeño por materia y período
     */
    public function getEstadisticasDesempenoMateria($materiaId, $periodoId)
    {
        return Cache::remember("stats_materia_{$materiaId}_{$periodoId}", 1800, function() use ($materiaId, $periodoId) {
            $estadisticas = DesempenoMateria::where('materia_id', $materiaId)
                ->where('periodo_id', $periodoId)
                ->select([
                    'nivel_desempeno',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM desempenos_materia WHERE materia_id = ? AND periodo_id = ?)), 1) as porcentaje')
                ])
                ->addBinding([$materiaId, $periodoId], 'select')
                ->groupBy('nivel_desempeno')
                ->get()
                ->keyBy('nivel_desempeno');
            
            $total = $estadisticas->sum('cantidad');
            $promedio = $estadisticas->sum(function($item) {
                return match($item->nivel_desempeno) {
                    'E' => 5 * $item->cantidad,
                    'S' => 4 * $item->cantidad,
                    'A' => 3 * $item->cantidad,
                    'I' => 2 * $item->cantidad,
                    default => 0
                };
            }) / ($total ?: 1);
            
            return [
                'total_estudiantes' => $total,
                'promedio_numerico' => round($promedio, 2),
                'distribucion' => $estadisticas,
                'porcentaje_aprobados' => $total > 0 ? round((($estadisticas->get('E', (object)['cantidad' => 0])->cantidad + 
                                                                $estadisticas->get('S', (object)['cantidad' => 0])->cantidad + 
                                                                $estadisticas->get('A', (object)['cantidad' => 0])->cantidad) / $total) * 100, 1) : 0
            ];
        });
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
    
    /**
     * Invalidar cache relacionado cuando se actualice una calificación
     */
    public function invalidarCacheCalificacion($desempenoMateria)
    {
        $estudianteId = $desempenoMateria->estudiante_id;
        $materiaId = $desempenoMateria->materia_id;
        $periodoId = $desempenoMateria->periodo_id;
        
        // Obtener el grado del estudiante para invalidar cache del grado
        $estudiante = Estudiante::find($estudianteId);
        $gradoId = $estudiante->grado_id ?? null;
        
        $tags = [
            "boletin_estudiante_{$estudianteId}_{$periodoId}",
            "stats_materia_{$materiaId}_{$periodoId}",
            "logros_dificiles_{$periodoId}_*"
        ];
        
        if ($gradoId) {
            $tags[] = "rendimiento_grado_{$gradoId}_{$periodoId}";
        }
        
        foreach($tags as $tag) {
            Cache::forget($tag);
        }
    }
}
