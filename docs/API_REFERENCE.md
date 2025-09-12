# API REFERENCE - LOGROSLS

## 📋 **OVERVIEW**

LogrosLS expone una API interna através de los métodos de modelos Eloquent y servicios especializados. Aunque no tiene endpoints REST públicos actualmente, proporciona una interfaz programática robusta para el manejo de datos académicos.

---

## 🏗️ **ARQUITECTURA DE LA API INTERNA**

### **Capas de Acceso a Datos**

```
┌─────────────────┐
│  Filament UI    │ ──┐
├─────────────────┤   │
│   Resources     │   │
├─────────────────┤   ├──► Model API
│    Policies     │   │
├─────────────────┤   │
│    Services     │ ──┘
├─────────────────┤
│  Eloquent ORM   │ ──► Database
└─────────────────┘
```

---

## 📊 **MODELOS PRINCIPALES**

### **Estudiante**

#### **Atributos**

```php
class Estudiante extends Model
{
    protected $fillable = [
        'nombre',           // string
        'apellido',         // string
        'documento',        // string
        'fecha_nacimiento', // date
        'direccion',        // string
        'telefono',         // string
        'email',           // string
        'grado_id',        // foreign key
        'activo'           // boolean
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean'
    ];

    protected $appends = ['nombre_completo'];
}
```

#### **Relaciones**

```php
// Grado al que pertenece
public function grado(): BelongsTo
// Desempeños en materias
public function desempenosMateria(): HasMany
// Logros asignados (a través de desempeños)
public function estudianteLogros(): HasManyThrough
```

#### **Métodos de Consulta**

```php
// Buscar estudiantes activos
Estudiante::where('activo', true)->get();

// Estudiantes por grado
Estudiante::where('grado_id', $gradoId)->get();

// Con relaciones cargadas
Estudiante::with(['grado', 'desempenosMateria'])->get();

// Scope personalizado
Estudiante::activos()->porGrado($gradoId)->get();
```

#### **Ejemplo de Uso**

```php
$estudiante = Estudiante::create([
    'nombre' => 'Juan Carlos',
    'apellido' => 'Pérez González',
    'documento' => '1234567890',
    'fecha_nacimiento' => '2010-05-15',
    'grado_id' => 1,
    'activo' => true
]);

// Acceder a propiedades calculadas
echo $estudiante->nombre_completo; // "Juan Carlos Pérez González - 1234567890"
```

---

### **DesempenoMateria**

#### **Atributos**

```php
class DesempenoMateria extends Model
{
    protected $fillable = [
        'estudiante_id',        // foreign key
        'materia_id',          // foreign key
        'periodo_id',          // foreign key
        'nivel_desempeno',     // enum: E, S, A, I
        'observaciones_finales', // text
        'fecha_asignacion',    // date
        'estado',              // enum: borrador, publicado, revisado
        'locked_at',           // timestamp
        'locked_by'            // foreign key (user)
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
        'locked_at' => 'datetime'
    ];
}
```

#### **Relaciones**

```php
// Estudiante evaluado
public function estudiante(): BelongsTo
// Materia evaluada
public function materia(): BelongsTo
// Período de evaluación
public function periodo(): BelongsTo
// Logros específicos alcanzados
public function estudianteLogros(): HasMany
// Usuario que bloqueó la evaluación
public function lockedBy(): BelongsTo
```

#### **Métodos Especializados**

```php
// Obtener estadísticas de logros
public function getStatsLogros(): array
{
    $logros = $this->estudianteLogros;
    $total = $logros->count();
    $alcanzados = $logros->where('alcanzado', true)->count();

    return [
        'total_logros' => $total,
        'logros_alcanzados' => $alcanzados,
        'porcentaje_alcanzado' => $total > 0
            ? round(($alcanzados / $total) * 100, 1)
            : 0
    ];
}

// Verificar si es editable
public function esEditable(): bool
{
    return is_null($this->locked_at) && $this->estado === 'borrador';
}

// Bloquear evaluación
public function bloquear($userId = null): void
{
    $this->update([
        'locked_at' => now(),
        'locked_by' => $userId ?: auth()->id(),
        'estado' => 'publicado'
    ]);
}
```

#### **Scopes de Consulta**

```php
// Por estudiante
DesempenoMateria::porEstudiante($estudianteId)->get();

// Por materia y período
DesempenoMateria::porMateria($materiaId)
    ->porPeriodo($periodoId)
    ->get();

// Solo evaluaciones editables
DesempenoMateria::where('estado', 'borrador')
    ->whereNull('locked_at')
    ->get();

// Con nivel de desempeño específico
DesempenoMateria::where('nivel_desempeno', 'E')->get();
```

---

### **EstudianteLogro**

#### **Atributos**

```php
class EstudianteLogro extends Model
{
    protected $fillable = [
        'logro_id',             // foreign key
        'desempeno_materia_id', // foreign key
        'alcanzado'             // boolean
    ];

    protected $casts = [
        'alcanzado' => 'boolean'
    ];
}
```

#### **Relaciones**

```php
// Logro específico
public function logro(): BelongsTo
// Desempeño de materia contenedor
public function desempenoMateria(): BelongsTo
```

#### **Accessors Delegados**

```php
// Acceso indirecto a través de DesempenoMateria
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
```

#### **Scopes Especializados**

```php
// Logros alcanzados
EstudianteLogro::alcanzados()->get();

// Logros no alcanzados
EstudianteLogro::noAlcanzados()->get();

// Por logro específico
EstudianteLogro::porLogro($logroId)->get();

// Por desempeño de materia
EstudianteLogro::porDesempenoMateria($desempenoId)->get();
```

---

### **Logro**

#### **Atributos**

```php
class Logro extends Model
{
    protected $fillable = [
        'codigo',      // string - único
        'titulo',      // string - opcional
        'desempeno',   // text - descripción del logro
        'materia_id',  // foreign key
        'activo',      // boolean
        'orden'        // integer - para ordenamiento
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];
}
```

#### **Relaciones**

```php
// Materia a la que pertenece
public function materia(): BelongsTo
// Períodos en los que se usa
public function periodos(): BelongsToMany
// Evaluaciones de estudiantes
public function estudianteLogros(): HasMany
```

#### **Scopes**

```php
// Logros activos
Logro::activos()->get();

// Por materia
Logro::porMateria($materiaId)->get();

// Por grado (a través de materia)
Logro::porGrado($gradoId)->get();
```

---

## 🔧 **SERVICIOS ESPECIALIZADOS**

### **ReporteMateria (Utility Class)**

#### **Métodos Estáticos**

```php
class ReporteMateria extends Model
{
    // Obtener desempeño específico
    public static function getDesempenoMateria($estudianteId, $materiaId, $periodoId)
    {
        return DesempenoMateria::where('estudiante_id', $estudianteId)
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->first();
    }

    // Obtener logros de una materia
    public static function getLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        return $desempeno?->estudianteLogros()->with('logro')->get() ?? collect([]);
    }

    // Contar logros
    public static function countLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        return $desempeno?->estudianteLogros()->count() ?? 0;
    }

    // Obtener estadísticas de materia
    public static function getEstadisticasMateria($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);

        if (!$desempeno) {
            return [
                'nivel_desempeno' => null,
                'total_logros' => 0,
                'logros_alcanzados' => 0,
                'porcentaje_alcanzado' => 0,
                'observaciones' => null,
                'estado' => null,
                'bloqueado' => false
            ];
        }

        $logros = $desempeno->estudianteLogros;
        $totalLogros = $logros->count();
        $logrosAlcanzados = $logros->where('alcanzado', true)->count();

        return [
            'nivel_desempeno' => $desempeno->nivel_desempeno,
            'total_logros' => $totalLogros,
            'logros_alcanzados' => $logrosAlcanzados,
            'porcentaje_alcanzado' => $totalLogros > 0
                ? round(($logrosAlcanzados / $totalLogros) * 100, 1)
                : 0,
            'observaciones' => $desempeno->observaciones_finales,
            'estado' => $desempeno->estado,
            'bloqueado' => !is_null($desempeno->locked_at)
        ];
    }

    // Resumen por grado y período
    public static function getResumenGradoPeriodo($gradoId, $periodoId)
    {
        return DesempenoMateria::join('estudiantes', 'desempenos_materia.estudiante_id', '=', 'estudiantes.id')
            ->join('materias', 'desempenos_materia.materia_id', '=', 'materias.id')
            ->where('estudiantes.grado_id', $gradoId)
            ->where('desempenos_materia.periodo_id', $periodoId)
            ->select([
                'materias.nombre as materia_nombre',
                'desempenos_materia.nivel_desempeno',
                DB::raw('COUNT(*) as cantidad_estudiantes'),
                DB::raw('ROUND(AVG(CASE
                    WHEN nivel_desempeno = "E" THEN 5
                    WHEN nivel_desempeno = "S" THEN 4
                    WHEN nivel_desempeno = "A" THEN 3
                    WHEN nivel_desempeno = "I" THEN 2
                    ELSE 0 END), 2) as promedio_numerico')
            ])
            ->groupBy('materias.nombre', 'desempenos_materia.nivel_desempeno')
            ->orderBy('materias.nombre')
            ->orderBy('desempenos_materia.nivel_desempeno')
            ->get()
            ->groupBy('materia_nombre');
    }
}
```

---

## 🔐 **SISTEMA DE PERMISOS (API)**

### **Verificación de Permisos**

```php
// Verificar si el usuario puede realizar una acción
$user = auth()->user();

// Verificación por rol
if ($user->hasRole('admin')) {
    // Usuario administrador
}

if ($user->hasRole('profesor')) {
    // Usuario profesor
}

// Verificación por permiso específico
if ($user->can('view', $estudiante)) {
    // Puede ver este estudiante
}

if ($user->can('update', $desempenoMateria)) {
    // Puede actualizar este desempeño
}
```

### **Filtrado Según Contexto**

```php
// En EstudianteResource
public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    $query = parent::getEloquentQuery()->with(['grado']);

    if ($user->hasRole('profesor')) {
        // Profesor solo ve estudiantes de sus materias o de su grupo
        if ($user->isDirectorGrupo()) {
            $query->where('grado_id', $user->director_grado_id);
        } else {
            $gradoIds = $user->materias()
                ->with('grados')
                ->get()
                ->pluck('grados')
                ->flatten()
                ->pluck('id')
                ->unique();
            $query->whereIn('grado_id', $gradoIds);
        }
    }

    return $query;
}
```

---

## 📊 **CONSULTAS COMPLEJAS**

### **Reportes de Rendimiento**

```php
// Estudiantes con bajo rendimiento
$estudiantesBajoRendimiento = DesempenoMateria::where('periodo_id', $periodoId)
    ->where('nivel_desempeno', 'I')
    ->with(['estudiante.grado', 'materia'])
    ->get();

// Estadísticas por materia
$estadisticasMateria = DesempenoMateria::join('materias', 'desempenos_materia.materia_id', '=', 'materias.id')
    ->where('desempenos_materia.periodo_id', $periodoId)
    ->select([
        'materias.nombre as materia_nombre',
        'desempenos_materia.nivel_desempeno',
        DB::raw('COUNT(*) as cantidad_estudiantes')
    ])
    ->groupBy('materias.nombre', 'desempenos_materia.nivel_desempeno')
    ->get();

// Logros más difíciles de alcanzar
$logrosDificiles = EstudianteLogro::join('logros', 'estudiante_logros.logro_id', '=', 'logros.id')
    ->join('desempenos_materia', 'estudiante_logros.desempeno_materia_id', '=', 'desempenos_materia.id')
    ->where('desempenos_materia.periodo_id', $periodoId)
    ->select([
        'logros.codigo',
        'logros.desempeno',
        DB::raw('COUNT(*) as total_evaluaciones'),
        DB::raw('SUM(CASE WHEN alcanzado = 1 THEN 1 ELSE 0 END) as total_alcanzados'),
        DB::raw('ROUND((SUM(CASE WHEN alcanzado = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as porcentaje_exito')
    ])
    ->groupBy('logros.id', 'logros.codigo', 'logros.desempeno')
    ->having('porcentaje_exito', '<', 50)
    ->orderBy('porcentaje_exito', 'asc')
    ->get();
```

### **Consultas de Boletines**

```php
// Datos completos para boletín de estudiante
$datosBoletinEstudiante = function($estudianteId, $periodoId) {
    $estudiante = Estudiante::with('grado')->find($estudianteId);

    $desempenos = DesempenoMateria::where('estudiante_id', $estudianteId)
        ->where('periodo_id', $periodoId)
        ->with([
            'materia',
            'estudianteLogros.logro' => function($query) {
                $query->orderBy('orden');
            }
        ])
        ->get();

    $promedio = $desempenos->avg(function($desempeno) {
        return match($desempeno->nivel_desempeno) {
            'E' => 5,
            'S' => 4,
            'A' => 3,
            'I' => 2,
            default => 0
        };
    });

    return [
        'estudiante' => $estudiante,
        'desempenos' => $desempenos,
        'promedio_general' => round($promedio, 2),
        'total_materias' => $desempenos->count(),
        'materias_excelentes' => $desempenos->where('nivel_desempeno', 'E')->count(),
        'materias_insuficientes' => $desempenos->where('nivel_desempeno', 'I')->count()
    ];
};
```

---

## 🔄 **TRANSACCIONES Y CONSISTENCIA**

### **Operaciones Atómicas**

```php
use Illuminate\Support\Facades\DB;

// Crear desempeño con logros asociados
DB::transaction(function () use ($data) {
    $desempeno = DesempenoMateria::create([
        'estudiante_id' => $data['estudiante_id'],
        'materia_id' => $data['materia_id'],
        'periodo_id' => $data['periodo_id'],
        'nivel_desempeno' => $data['nivel_desempeno'],
        'observaciones_finales' => $data['observaciones'],
        'fecha_asignacion' => now(),
        'estado' => 'borrador'
    ]);

    // Crear logros asociados
    foreach ($data['logros'] as $logroData) {
        EstudianteLogro::create([
            'logro_id' => $logroData['logro_id'],
            'desempeno_materia_id' => $desempeno->id,
            'alcanzado' => $logroData['alcanzado']
        ]);
    }
});
```

### **Validaciones de Integridad**

```php
// Verificar que no exista duplicado
$existeDesempeno = DesempenoMateria::where('estudiante_id', $estudianteId)
    ->where('materia_id', $materiaId)
    ->where('periodo_id', $periodoId)
    ->exists();

if ($existeDesempeno) {
    throw new \Exception('Ya existe una evaluación para este estudiante en esta materia y período');
}

// Verificar que el período esté activo
$periodo = Periodo::find($periodoId);
if (!$periodo || !$periodo->activo) {
    throw new \Exception('El período no está activo para evaluaciones');
}
```

---

## 📈 **OPTIMIZACIÓN DE CONSULTAS**

### **Eager Loading Patterns**

```php
// Patrón optimizado para listados
$estudiantes = Estudiante::with([
    'grado:id,nombre,grupo',
    'desempenosMateria' => function($query) use ($periodoId) {
        $query->where('periodo_id', $periodoId)
            ->with('materia:id,nombre,codigo');
    }
])->get();

// Patrón para reportes detallados
$boletinData = Estudiante::with([
    'grado',
    'desempenosMateria.materia',
    'desempenosMateria.periodo',
    'desempenosMateria.estudianteLogros.logro' => function($query) {
        $query->orderBy('orden');
    }
])->find($estudianteId);
```

### **Consultas con Agregaciones**

```php
// Estadísticas con subconsultas
$gradosConEstadisticas = Grado::withCount([
    'estudiantes',
    'estudiantes as estudiantes_activos_count' => function($query) {
        $query->where('activo', true);
    }
])->with([
    'estudiantes.desempenosMateria' => function($query) use ($periodoId) {
        $query->where('periodo_id', $periodoId)
            ->selectRaw('estudiante_id, AVG(CASE
                WHEN nivel_desempeno = "E" THEN 5
                WHEN nivel_desempeno = "S" THEN 4
                WHEN nivel_desempeno = "A" THEN 3
                WHEN nivel_desempeno = "I" THEN 2
                ELSE 0 END) as promedio_numerico')
            ->groupBy('estudiante_id');
    }
])->get();
```

---

## 🛠️ **UTILIDADES Y HELPERS**

### **Funciones de Cálculo**

```php
// Calcular promedio numérico de nivel de desempeño
function calcularPromedioNumerico($nivelDesempeno): float
{
    return match($nivelDesempeno) {
        'E' => 5.0,
        'S' => 4.0,
        'A' => 3.0,
        'I' => 2.0,
        default => 0.0
    };
}

// Convertir promedio numérico a nivel de desempeño
function convertirANivelDesempeno(float $promedio): string
{
    return match(true) {
        $promedio >= 4.5 => 'E',
        $promedio >= 3.5 => 'S',
        $promedio >= 2.5 => 'A',
        default => 'I'
    };
}

// Obtener color para UI según nivel
function getColorNivel(string $nivel): string
{
    return match($nivel) {
        'E' => 'success',
        'S' => 'info',
        'A' => 'warning',
        'I' => 'danger',
        default => 'gray'
    };
}
```

### **Validadores Personalizados**

```php
// app/Rules/FechaNoPosterior.php
class FechaNoPosterior implements Rule
{
    public function passes($attribute, $value)
    {
        return Carbon::parse($value)->lte(now());
    }

    public function message()
    {
        return 'La fecha no puede ser posterior a la fecha actual.';
    }
}

// app/Rules/PeriodoUnicoActivo.php
class PeriodoUnicoActivo implements Rule
{
    public function passes($attribute, $value)
    {
        if (!$value) return true;

        return !Periodo::where('activo', true)
            ->where('id', '!=', request()->route('record'))
            ->exists();
    }
}
```

---

## 📚 **EJEMPLOS DE USO COMPLETOS**

### **Crear Evaluación Completa**

```php
function crearEvaluacionCompleta($estudianteId, $materiaId, $periodoId, $evaluacionData)
{
    return DB::transaction(function () use ($estudianteId, $materiaId, $periodoId, $evaluacionData) {
        // 1. Verificar prerrequisitos
        $periodo = Periodo::findOrFail($periodoId);
        if (!$periodo->activo) {
            throw new \Exception('Período no activo');
        }

        $estudiante = Estudiante::findOrFail($estudianteId);
        if (!$estudiante->activo) {
            throw new \Exception('Estudiante no activo');
        }

        // 2. Crear desempeño consolidado
        $desempeno = DesempenoMateria::create([
            'estudiante_id' => $estudianteId,
            'materia_id' => $materiaId,
            'periodo_id' => $periodoId,
            'nivel_desempeno' => $evaluacionData['nivel_desempeno'],
            'observaciones_finales' => $evaluacionData['observaciones'] ?? null,
            'fecha_asignacion' => now(),
            'estado' => 'borrador'
        ]);

        // 3. Crear logros específicos
        $logrosCreados = [];
        foreach ($evaluacionData['logros'] as $logroData) {
            $estudianteLogro = EstudianteLogro::create([
                'logro_id' => $logroData['logro_id'],
                'desempeno_materia_id' => $desempeno->id,
                'alcanzado' => $logroData['alcanzado']
            ]);
            $logrosCreados[] = $estudianteLogro;
        }

        // 4. Recalcular estadísticas si es necesario
        $stats = $desempeno->getStatsLogros();

        return [
            'desempeno' => $desempeno,
            'logros' => $logrosCreados,
            'estadisticas' => $stats
        ];
    });
}
```

### **Generar Reporte de Grado**

```php
function generarReporteGrado($gradoId, $periodoId)
{
    $grado = Grado::with(['estudiantes' => function($query) {
        $query->where('activo', true)->orderBy('apellido', 'nombre');
    }])->findOrFail($gradoId);

    $periodo = Periodo::findOrFail($periodoId);

    $materias = Materia::whereHas('grados', function($query) use ($gradoId) {
        $query->where('grados.id', $gradoId);
    })->with('docente')->get();

    $reporteEstudiantes = [];

    foreach ($grado->estudiantes as $estudiante) {
        $desempenosEstudiante = [];
        $promedioTotal = 0;
        $countMaterias = 0;

        foreach ($materias as $materia) {
            $desempeno = DesempenoMateria::where('estudiante_id', $estudiante->id)
                ->where('materia_id', $materia->id)
                ->where('periodo_id', $periodoId)
                ->with('estudianteLogros.logro')
                ->first();

            if ($desempeno) {
                $stats = $desempeno->getStatsLogros();
                $valorNumerico = calcularPromedioNumerico($desempeno->nivel_desempeno);
                $promedioTotal += $valorNumerico;
                $countMaterias++;

                $desempenosEstudiante[] = [
                    'materia' => $materia->nombre,
                    'nivel_desempeno' => $desempeno->nivel_desempeno,
                    'valor_numerico' => $valorNumerico,
                    'logros_stats' => $stats,
                    'observaciones' => $desempeno->observaciones_finales
                ];
            }
        }

        $reporteEstudiantes[] = [
            'estudiante' => $estudiante,
            'desempenos' => $desempenosEstudiante,
            'promedio_general' => $countMaterias > 0 ? round($promedioTotal / $countMaterias, 2) : 0,
            'total_materias' => $countMaterias
        ];
    }

    return [
        'grado' => $grado,
        'periodo' => $periodo,
        'materias' => $materias,
        'estudiantes' => $reporteEstudiantes,
        'resumen' => [
            'total_estudiantes' => count($reporteEstudiantes),
            'promedio_grado' => collect($reporteEstudiantes)->avg('promedio_general')
        ]
    ];
}
```

---

**Última actualización**: Septiembre 2025  
**Versión**: 1.0  
**Mantenido por**: Equipo de Desarrollo LogrosLS
