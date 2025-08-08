# Guía de API y Consultas Avanzadas

## Consultas Optimizadas por Caso de Uso

### Dashboard del Profesor

```php
// Obtener estadísticas del profesor autenticado
public function getDashboardStats()
{
    $user = auth()->user();

    // Materias que imparte
    $materias = $user->materias()->with('grados')->get();

    // Estudiantes en sus materias (optimizado)
    $gradoIds = $materias->pluck('grados')->flatten()->pluck('id')->unique();
    $estudiantes = Estudiante::whereIn('grado_id', $gradoIds)
        ->where('activo', true)
        ->count();

    // Evaluaciones pendientes (logros sin evaluar)
    $periodo = Periodo::where('activo', true)->first();
    $logrosDelProfesor = Logro::whereIn('materia_id', $materias->pluck('id'))->pluck('id');

    $evaluacionesPendientes = DB::table('estudiantes')
        ->join('grados', 'estudiantes.grado_id', '=', 'grados.id')
        ->join('grado_materia', 'grados.id', '=', 'grado_materia.grado_id')
        ->join('logros', 'grado_materia.materia_id', '=', 'logros.materia_id')
        ->leftJoin('estudiante_logros', function($join) use ($periodo) {
            $join->on('estudiantes.id', '=', 'estudiante_logros.estudiante_id')
                 ->on('logros.id', '=', 'estudiante_logros.logro_id')
                 ->where('estudiante_logros.periodo_id', $periodo->id);
        })
        ->whereIn('logros.id', $logrosDelProfesor)
        ->whereNull('estudiante_logros.id')
        ->count();

    return [
        'materias_count' => $materias->count(),
        'estudiantes_count' => $estudiantes,
        'evaluaciones_pendientes' => $evaluacionesPendientes
    ];
}
```

### Reporte de Rendimiento por Grado

```php
public function getRendimientoGrado($gradoId, $periodoId)
{
    return DB::table('estudiante_logros')
        ->join('estudiantes', 'estudiante_logros.estudiante_id', '=', 'estudiantes.id')
        ->join('logros', 'estudiante_logros.logro_id', '=', 'logros.id')
        ->join('materias', 'logros.materia_id', '=', 'materias.id')
        ->where('estudiantes.grado_id', $gradoId)
        ->where('estudiante_logros.periodo_id', $periodoId)
        ->select([
            'materias.nombre as materia',
            'estudiante_logros.nivel_desempeno',
            DB::raw('COUNT(*) as cantidad')
        ])
        ->groupBy('materias.nombre', 'estudiante_logros.nivel_desempeno')
        ->get()
        ->groupBy('materia')
        ->map(function($evaluaciones) {
            $total = $evaluaciones->sum('cantidad');
            return $evaluaciones->map(function($item) use ($total) {
                $item->porcentaje = round(($item->cantidad / $total) * 100, 1);
                return $item;
            });
        });
}
```

### Búsqueda Avanzada de Estudiantes

```php
public function buscarEstudiantes($filtros)
{
    $query = Estudiante::query()
        ->with(['grado', 'estudianteLogros' => function($q) {
            $q->whereHas('periodo', fn($p) => $p->where('activo', true));
        }]);

    // Filtro por texto (nombre, apellido, documento)
    if (!empty($filtros['busqueda'])) {
        $query->where(function($q) use ($filtros) {
            $q->where('nombre', 'LIKE', "%{$filtros['busqueda']}%")
              ->orWhere('apellido', 'LIKE', "%{$filtros['busqueda']}%")
              ->orWhere('documento', 'LIKE', "%{$filtros['busqueda']}%");
        });
    }

    // Filtro por grado
    if (!empty($filtros['grado_id'])) {
        $query->where('grado_id', $filtros['grado_id']);
    }

    // Filtro por rendimiento
    if (!empty($filtros['rendimiento'])) {
        $query->whereHas('estudianteLogros', function($q) use ($filtros) {
            $q->where('nivel_desempeno', $filtros['rendimiento'])
              ->whereHas('periodo', fn($p) => $p->where('activo', true));
        });
    }

    // Filtro por materia específica
    if (!empty($filtros['materia_id'])) {
        $query->whereHas('estudianteLogros.logro', function($q) use ($filtros) {
            $q->where('materia_id', $filtros['materia_id']);
        });
    }

    return $query->paginate(20);
}
```

## Scopes Avanzados

### Modelo Logro

```php
// Logros por dificultad y tipo
public function scopeComplexidad($query, $nivel, $tipo = null)
{
    $query->where('nivel_dificultad', $nivel);

    if ($tipo) {
        $query->where('tipo', $tipo);
    }

    return $query;
}

// Logros con estadísticas de evaluación
public function scopeConEstadisticas($query, $periodoId = null)
{
    $periodoCondition = $periodoId ? "AND el.periodo_id = {$periodoId}" : '';

    return $query->addSelect([
        'evaluaciones_total' => DB::raw("(
            SELECT COUNT(*)
            FROM estudiante_logros el
            WHERE el.logro_id = logros.id {$periodoCondition}
        )"),
        'promedio_nivel' => DB::raw("(
            SELECT AVG(
                CASE el.nivel_desempeno
                    WHEN 'E' THEN 5
                    WHEN 'S' THEN 4
                    WHEN 'A' THEN 3
                    WHEN 'I' THEN 2
                    ELSE 0
                END
            )
            FROM estudiante_logros el
            WHERE el.logro_id = logros.id {$periodoCondition}
        )")
    ]);
}
```

### Modelo Estudiante

```php
// Estudiantes con riesgo académico
public function scopeEnRiesgo($query, $periodoId = null)
{
    $periodo = $periodoId ?? Periodo::where('activo', true)->first()?->id;

    return $query->whereHas('estudianteLogros', function($q) use ($periodo) {
        $q->where('periodo_id', $periodo)
          ->havingRaw('
              (COUNT(CASE WHEN nivel_desempeno = "I" THEN 1 END) / COUNT(*)) > 0.3
          ');
    }, '>=', 5); // Al menos 5 evaluaciones para ser considerado
}

// Estudiantes destacados
public function scopeDestacados($query, $periodoId = null)
{
    $periodo = $periodoId ?? Periodo::where('activo', true)->first()?->id;

    return $query->whereHas('estudianteLogros', function($q) use ($periodo) {
        $q->where('periodo_id', $periodo)
          ->havingRaw('
              (COUNT(CASE WHEN nivel_desempeno IN ("E", "S") THEN 1 END) / COUNT(*)) > 0.8
          ');
    }, '>=', 5);
}
```

## Consultas de Reportería

### Top Logros con Mayor Dificultad

```php
public function getLogrosDificiles($periodoId)
{
    return Logro::withCount([
        'estudianteLogros as insuficientes' => function($q) use ($periodoId) {
            $q->where('periodo_id', $periodoId)
              ->where('nivel_desempeno', 'I');
        },
        'estudianteLogros as total_evaluaciones' => function($q) use ($periodoId) {
            $q->where('periodo_id', $periodoId);
        }
    ])
    ->having('total_evaluaciones', '>=', 10) // Mínimo 10 evaluaciones
    ->get()
    ->map(function($logro) {
        $logro->porcentaje_dificultad = $logro->total_evaluaciones > 0
            ? round(($logro->insuficientes / $logro->total_evaluaciones) * 100, 1)
            : 0;
        return $logro;
    })
    ->sortByDesc('porcentaje_dificultad')
    ->take(10);
}
```

### Progreso Temporal del Estudiante

```php
public function getProgresoEstudiante($estudianteId)
{
    return EstudianteLogro::where('estudiante_id', $estudianteId)
        ->with(['logro.materia', 'periodo'])
        ->get()
        ->groupBy('logro.materia.nombre')
        ->map(function($evaluaciones) {
            return $evaluaciones->groupBy('periodo.nombre')
                ->map(function($evaluacionesPeriodo) {
                    $promedio = $evaluacionesPeriodo->avg(function($eval) {
                        return match($eval->nivel_desempeno) {
                            'E' => 5, 'S' => 4, 'A' => 3, 'I' => 2, default => 0
                        };
                    });

                    return [
                        'evaluaciones' => $evaluacionesPeriodo->count(),
                        'promedio_numerico' => round($promedio, 2),
                        'nivel_predominante' => $evaluacionesPeriodo
                            ->groupBy('nivel_desempeno')
                            ->sortByDesc(fn($grupo) => $grupo->count())
                            ->keys()
                            ->first()
                    ];
                });
        });
}
```

## Optimizaciones con Cache

### Cache de Consultas Frecuentes

```php
// En un Service Provider o trait
public function getCachedPeriodoActivo()
{
    return Cache::remember('periodo_activo', 3600, function() {
        return Periodo::where('activo', true)->first();
    });
}

public function getCachedEstadisticasGrado($gradoId)
{
    return Cache::remember("stats_grado_{$gradoId}", 1800, function() use ($gradoId) {
        return [
            'total_estudiantes' => Estudiante::where('grado_id', $gradoId)->count(),
            'promedio_evaluaciones' => $this->getPromedioEvaluaciones($gradoId),
            'materias_count' => Grado::find($gradoId)->materias()->count()
        ];
    });
}

// Invalidar cache cuando sea necesario
public function invalidarCacheGrado($gradoId)
{
    Cache::forget("stats_grado_{$gradoId}");
    Cache::tags(['grado', "grado_{$gradoId}"])->flush();
}
```

## Eventos y Listeners

### Eventos Importantes

```php
// Cuando se evalúa un estudiante
event(new EstudianteEvaluado($estudianteLogro));

// Cuando se cierra un período
event(new PeriodoCerrado($periodo));

// Cuando un estudiante está en riesgo
event(new EstudianteEnRiesgo($estudiante, $evaluaciones));
```

### Listeners para Automatización

```php
class GenerarAlertaRiesgo
{
    public function handle(EstudianteEvaluado $event)
    {
        $estudiante = $event->estudianteLogro->estudiante;
        $evaluacionesInsuficientes = $estudiante->estudianteLogros()
            ->where('nivel_desempeno', 'I')
            ->whereHas('periodo', fn($q) => $q->where('activo', true))
            ->count();

        if ($evaluacionesInsuficientes >= 3) {
            event(new EstudianteEnRiesgo($estudiante));
        }
    }
}
```

---

> **Nota**: Estas consultas están optimizadas para el dominio específico del sistema de logros académicos. Usar índices apropiados en producción.
