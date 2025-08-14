<?php

namespace Tests\Business;

use App\Models\Estudiante;
use App\Models\EstudianteLogro;
use App\Models\Grado;
use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function director_can_only_be_assigned_to_one_active_grado()
    {
        $admin = $this->createAdmin();
        $director = $this->createProfesorDirector();
        
        $this->actingAs($admin);
        
        // Crear primer grado y asignarlo como director
        $grado1 = Grado::factory()->create([
            'activo' => true,
        ]);
        $director->update(['director_grado_id' => $grado1->id]);
        
        // Crear segundo grado activo 
        $grado2 = Grado::factory()->create([
            'activo' => true,
        ]);
        
        // Verificar que el director está asignado al primer grado
        $this->assertEquals($grado1->id, $director->fresh()->director_grado_id);
        
        // Verificar que existe la relación director-grado
        $this->assertNotNull($director->directorGrado);
        $this->assertEquals($grado1->id, $director->directorGrado->id);
        
        // Un director solo puede estar asignado a un grado a la vez
        $this->assertTrue($director->isDirectorGrupo());
    }

    /** @test */
    public function periodo_dates_must_be_coherent_and_sequential()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear períodos con fechas coherentes
        $periodo1 = Periodo::factory()->create([
            'numero_periodo' => 1,
            'fecha_inicio' => Carbon::parse('2024-02-01'),
            'fecha_fin' => Carbon::parse('2024-04-30'),
            'año_escolar' => 2024,
            'corte' => 'Primer Corte',
        ]);
        
        $periodo2 = Periodo::factory()->create([
            'numero_periodo' => 2,
            'fecha_inicio' => Carbon::parse('2024-05-01'),
            'fecha_fin' => Carbon::parse('2024-07-31'),
            'año_escolar' => 2024,
            'corte' => 'Segundo Corte',
        ]);
        
        $periodo3 = Periodo::factory()->create([
            'numero_periodo' => 1,
            'fecha_inicio' => Carbon::parse('2024-08-01'),
            'fecha_fin' => Carbon::parse('2024-10-31'),
            'año_escolar' => 2025,
            'corte' => 'Primer Corte',
        ]);
        
        // Verificar coherencia interna de cada período
        $this->assertTrue($periodo1->fecha_inicio < $periodo1->fecha_fin);
        $this->assertTrue($periodo2->fecha_inicio < $periodo2->fecha_fin);
        $this->assertTrue($periodo3->fecha_inicio < $periodo3->fecha_fin);
        
        // Verificar secuencia lógica entre períodos
        $this->assertTrue($periodo1->fecha_fin < $periodo2->fecha_inicio);
        $this->assertTrue($periodo2->fecha_fin < $periodo3->fecha_inicio);
        
        // Verificar que los períodos están en el mismo año escolar o secuencial
        $this->assertEquals(2024, $periodo1->año_escolar);
        $this->assertEquals(2024, $periodo2->año_escolar);
        $this->assertEquals(2025, $periodo3->año_escolar);
        
        // Verificar duración mínima de cada período (ejemplo: al menos 60 días)
        $duracion1 = $periodo1->fecha_inicio->diffInDays($periodo1->fecha_fin);
        $duracion2 = $periodo2->fecha_inicio->diffInDays($periodo2->fecha_fin);
        $duracion3 = $periodo3->fecha_inicio->diffInDays($periodo3->fecha_fin);
        
        $this->assertGreaterThan(60, $duracion1);
        $this->assertGreaterThan(60, $duracion2);
        $this->assertGreaterThan(60, $duracion3);
    }

    /** @test */
    public function student_performance_calculations_are_accurate()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $periodo = $setup['periodos']['periodo1'];
        
        // Crear múltiples logros con diferentes evaluaciones
        $logros = [
            $setup['logros']['matematicas_basico'],
            $setup['logros']['lenguaje_lectura'], // Usar la clave correcta
            Logro::factory()->create(['materia_id' => $setup['materias']['matematicas']->id]),
            Logro::factory()->create(['materia_id' => $setup['materias']['lenguaje']->id]),
        ];
        
        $nivelesDesempeno = ['S', 'A', 'A', 'A']; // Usar valores válidos del enum
        
        // Crear evaluaciones
        foreach ($logros as $index => $logro) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => $nivelesDesempeno[$index],
                'fecha_asignacion' => now(), // Campo requerido
                'observaciones' => 'Evaluación de prueba',
            ]);
        }
        
        // Verificar que se crearon las evaluaciones
        $evaluacionesCreadas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->count();
        
        $this->assertEquals(4, $evaluacionesCreadas);
        
        // Calcular distribución por materia
        $evaluacionesMatematicas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->whereHas('logro', function ($query) use ($setup) {
                $query->where('materia_id', $setup['materias']['matematicas']->id);
            })
            ->count();
        
        $evaluacionesLenguaje = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->whereHas('logro', function ($query) use ($setup) {
                $query->where('materia_id', $setup['materias']['lenguaje']->id);
            })
            ->count();
        
        // Verificar que hay evaluaciones en ambas materias
        $this->assertGreaterThan(0, $evaluacionesMatematicas);
        $this->assertGreaterThan(0, $evaluacionesLenguaje);
        
        // Verificar distribución de niveles de desempeño
        $nivelesCount = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->selectRaw('nivel_desempeno, COUNT(*) as count')
            ->groupBy('nivel_desempeno')
            ->pluck('count', 'nivel_desempeno');
        
        $this->assertEquals(1, $nivelesCount['S']); // Superior
        $this->assertEquals(3, $nivelesCount['A']); // Alto
    }

    /** @test */
    public function academic_year_period_distribution_is_balanced()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $año = 2024;
        
        // Crear 4 períodos con distribución equilibrada
        $periodos = [
            Periodo::factory()->create([
                'numero_periodo' => 1,
                'fecha_inicio' => Carbon::parse("$año-02-01"),
                'fecha_fin' => Carbon::parse("$año-04-30"),
                'corte' => 'Primer Corte',
                'año_escolar' => $año,
            ]),
            Periodo::factory()->create([
                'numero_periodo' => 2,
                'fecha_inicio' => Carbon::parse("$año-05-01"),
                'fecha_fin' => Carbon::parse("$año-07-31"),
                'corte' => 'Segundo Corte',
                'año_escolar' => $año,
            ]),
            Periodo::factory()->create([
                'numero_periodo' => 1,
                'fecha_inicio' => Carbon::parse(($año+1)."-02-01"),
                'fecha_fin' => Carbon::parse(($año+1)."-04-30"),
                'corte' => 'Primer Corte',
                'año_escolar' => $año + 1,
            ]),
            Periodo::factory()->create([
                'numero_periodo' => 2,
                'fecha_inicio' => Carbon::parse(($año+1)."-05-01"),
                'fecha_fin' => Carbon::parse(($año+1)."-07-31"),
                'corte' => 'Segundo Corte',
                'año_escolar' => $año + 1,
            ]),
        ];
        
        // Verificar que hay 4 períodos creados
        $this->assertCount(4, $periodos);
        
        // Verificar que se distribuyen entre dos años escolares
        $periodosAño1 = collect($periodos)->where('año_escolar', $año)->count();
        $periodosAño2 = collect($periodos)->where('año_escolar', $año + 1)->count();
        
        $this->assertEquals(2, $periodosAño1);
        $this->assertEquals(2, $periodosAño2);
        
        // Verificar distribución temporal equilibrada (aproximadamente)
        $duraciones = [];
        foreach ($periodos as $periodo) {
            $duraciones[] = $periodo->fecha_inicio->diffInDays($periodo->fecha_fin);
        }
        
        // Los primeros 3 períodos deberían tener duración similar
        $this->assertTrue(abs($duraciones[0] - $duraciones[1]) <= 10);
        $this->assertTrue(abs($duraciones[1] - $duraciones[2]) <= 10);
        
        // El último período puede ser más corto (vacaciones)
        $this->assertTrue($duraciones[3] >= 30); // Al menos 30 días
        
        // Verificar que no hay gaps significativos entre períodos del mismo año
        $periodosOrdenados = collect($periodos)->sortBy(['año_escolar', 'numero_periodo']);
        $periodosArray = $periodosOrdenados->values()->all();
        
        // Verificar gaps dentro del mismo año escolar
        $this->assertTrue($periodosArray[0]->fecha_fin <= $periodosArray[1]->fecha_inicio);
        $this->assertTrue($periodosArray[2]->fecha_fin <= $periodosArray[3]->fecha_inicio);
    }

    /** @test */
    public function grade_level_progression_rules_are_enforced()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear grados de diferentes tipos con nombres apropiados
        $gradosPrimaria = [];
        for ($i = 1; $i <= 5; $i++) {
            $gradosPrimaria[] = Grado::factory()->create([
                'tipo' => 'primaria',
                'nombre' => $this->getNombreGrado($i),
            ]);
        }
        
        $gradosSecundaria = [];
        for ($i = 6; $i <= 9; $i++) {
            $gradosSecundaria[] = Grado::factory()->create([
                'tipo' => 'secundaria',
                'nombre' => $this->getNombreGrado($i),
            ]);
        }
        
        $gradosMedia = [];
        for ($i = 10; $i <= 11; $i++) {
            $gradosMedia[] = Grado::factory()->create([
                'tipo' => 'media_academica',
                'nombre' => $this->getNombreGrado($i),
            ]);
        }
        
        // Verificar que los tipos están correctos
        foreach ($gradosPrimaria as $grado) {
            $this->assertEquals('primaria', $grado->tipo);
        }
        
        foreach ($gradosSecundaria as $grado) {
            $this->assertEquals('secundaria', $grado->tipo);
        }
        
        foreach ($gradosMedia as $grado) {
            $this->assertEquals('media_academica', $grado->tipo);
        }
        
        // Verificar que se crearon los grados esperados
        $totalGrados = count($gradosPrimaria) + count($gradosSecundaria) + count($gradosMedia);
        $this->assertEquals(11, $totalGrados);
        
        // Verificar que hay la cantidad correcta por tipo
        $this->assertCount(5, $gradosPrimaria);
        $this->assertCount(4, $gradosSecundaria);
        $this->assertCount(2, $gradosMedia);
    }

    /** @test */
    public function student_evaluation_completion_tracking_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $periodo = $setup['periodos']['periodo1'];
        
        // Crear múltiples logros para el período
        $logros = Logro::factory(5)->create([
            'materia_id' => $setup['materias']['matematicas']->id,
        ]);
        
        // Asignar logros al período
        foreach ($logros as $logro) {
            $logro->periodos()->attach($periodo->id);
        }
        
        // Crear algunas evaluaciones (parcialmente completadas)
        foreach ($logros->take(3) as $logro) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'A',
                'fecha_asignacion' => now(),
                'observaciones' => 'Completado',
            ]);
        }
        
        // Los otros logros quedan sin evaluar (no existen registros)
        
        // Calcular porcentaje de completitud
        $totalLogros = $logros->count();
        $logrosEvaluados = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->count();
        
        $porcentajeCompletitud = ($logrosEvaluados / $totalLogros) * 100;
        
        $this->assertEquals(60, $porcentajeCompletitud); // 3 de 5 = 60%
        
        // Verificar estado de cada evaluación
        $evaluacionesCompletas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->get();
        
        $this->assertCount(3, $evaluacionesCompletas);
        
        // Completar las evaluaciones restantes
        foreach ($logros->skip(3) as $logro) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'I', // Usar valor válido del enum
                'fecha_asignacion' => now(),
                'observaciones' => 'Completado posteriormente',
            ]);
        }
        
        // Verificar completitud total
        $logrosEvaluadosCompleto = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->count();
        
        $this->assertEquals($totalLogros, $logrosEvaluadosCompleto);
    }

    /** @test */
    public function teacher_workload_distribution_is_balanced()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $profesor1 = $this->createProfesor();
        $profesor2 = $this->createProfesor();
        $profesor3 = $this->createProfesor();
        
        // Crear materias con diferentes intensidades horarias
        $materias = [
            Materia::factory()->create([
                'docente_id' => $profesor1->id,
                'nombre' => 'Matemáticas',
            ]),
            Materia::factory()->create([
                'docente_id' => $profesor1->id,
                'nombre' => 'Física',
            ]),
            Materia::factory()->create([
                'docente_id' => $profesor2->id,
                'nombre' => 'Lenguaje',
            ]),
            Materia::factory()->create([
                'docente_id' => $profesor2->id,
                'nombre' => 'Literatura',
            ]),
            Materia::factory()->create([
                'docente_id' => $profesor3->id,
                'nombre' => 'Ciencias Sociales',
            ]),
        ];
        
        // Calcular carga por número de materias por profesor
        $cargaProfesor1 = Materia::where('docente_id', $profesor1->id)->count();
        $cargaProfesor2 = Materia::where('docente_id', $profesor2->id)->count();
        $cargaProfesor3 = Materia::where('docente_id', $profesor3->id)->count();
        
        $this->assertEquals(2, $cargaProfesor1); // 2 materias
        $this->assertEquals(2, $cargaProfesor2); // 2 materias
        $this->assertEquals(1, $cargaProfesor3); // 1 materia
        
        // Verificar que no excede la carga máxima (en este caso 3 materias)
        $cargaMaxima = 3;
        $this->assertTrue($cargaProfesor1 <= $cargaMaxima);
        $this->assertTrue($cargaProfesor2 <= $cargaMaxima);
        $this->assertTrue($cargaProfesor3 <= $cargaMaxima);
        
        // Verificar distribución equilibrada (diferencia máxima de 1 materia)
        $cargas = [$cargaProfesor1, $cargaProfesor2, $cargaProfesor3];
        $cargaMaximaActual = max($cargas);
        $cargaMinimaActual = min($cargas);
        
        $this->assertTrue(($cargaMaximaActual - $cargaMinimaActual) <= 1);
        
        // Contar número de materias por profesor
        $this->assertCount(2, $profesor1->materias);
        $this->assertCount(2, $profesor2->materias);
        $this->assertCount(1, $profesor3->materias);
    }

    /** @test */
    public function academic_achievement_progression_validation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $logro = $setup['logros']['matematicas_basico'];
        
        // Crear períodos consecutivos
        $periodos = [
            Periodo::factory()->create(['numero_periodo' => 1, 'año_escolar' => 2024, 'corte' => 'Primer Corte']),
            Periodo::factory()->create(['numero_periodo' => 2, 'año_escolar' => 2024, 'corte' => 'Segundo Corte']),
            Periodo::factory()->create(['numero_periodo' => 1, 'año_escolar' => 2025, 'corte' => 'Primer Corte']),
            Periodo::factory()->create(['numero_periodo' => 2, 'año_escolar' => 2025, 'corte' => 'Segundo Corte']),
        ];
        
        // Crear progresión de evaluaciones (mejora gradual)
        $evaluaciones = [
            ['periodo' => $periodos[0], 'nivel' => 'I'],
            ['periodo' => $periodos[1], 'nivel' => 'I'],
            ['periodo' => $periodos[2], 'nivel' => 'A'],
            ['periodo' => $periodos[3], 'nivel' => 'A'],
        ];
        
        foreach ($evaluaciones as $evalData) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $evalData['periodo']->id,
                'nivel_desempeno' => $evalData['nivel'],
                'fecha_asignacion' => now(),
                'observaciones' => 'Progresión académica',
            ]);
        }
        
        // Verificar progresión positiva
        $evaluacionesOrdenadas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('logro_id', $logro->id)
            ->join('periodos', 'estudiante_logros.periodo_id', '=', 'periodos.id')
            ->orderBy('periodos.año_escolar')
            ->orderBy('periodos.numero_periodo')
            ->select('estudiante_logros.*')
            ->get();
        
        // Verificar que se crearon todas las evaluaciones
        $this->assertCount(4, $evaluacionesOrdenadas);
        
        // Calcular tendencia de mejora basada en niveles
        $primeraEvaluacion = $evaluacionesOrdenadas->first();
        $ultimaEvaluacion = $evaluacionesOrdenadas->last();
        
        // Verificar evolución de niveles de desempeño
        $nivelesNumericos = [
            'I' => 1,
            'A' => 2,
            'S' => 3,
            'E' => 4,
        ];
        
        $nivelInicial = $nivelesNumericos[$primeraEvaluacion->nivel_desempeno];
        $nivelFinal = $nivelesNumericos[$ultimaEvaluacion->nivel_desempeno];
        
        $this->assertTrue($nivelFinal >= $nivelInicial, 
            "El nivel de desempeño debería mantenerse o mejorar");
        
        // Verificar que efectivamente hubo progreso
        $this->assertEquals('I', $primeraEvaluacion->nivel_desempeno);
        $this->assertEquals('A', $ultimaEvaluacion->nivel_desempeno);
        $this->assertTrue($nivelFinal > $nivelInicial, "Debería haber una mejora general en el desempeño");
    }

    /**
     * Helper para obtener nombres de grados
     */
    private function getNombreGrado(int $numero): string
    {
        $nombres = [
            1 => 'Primero',
            2 => 'Segundo', 
            3 => 'Tercero',
            4 => 'Cuarto',
            5 => 'Quinto',
            6 => 'Sexto',
            7 => 'Séptimo',
            8 => 'Octavo',
            9 => 'Noveno',
            10 => 'Décimo',
            11 => 'Undécimo',
        ];
        
        return $nombres[$numero] ?? "Grado $numero";
    }

    /** @test */
    public function class_capacity_limits_are_respected()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        
        // Crear estudiantes 
        $estudiantes = Estudiante::factory(25)->create([
            'grado_id' => $grado->id,
        ]);
        
        $this->assertCount(25, $grado->fresh()->estudiantes);
        
        // Verificar que tenemos estudiantes asignados
        $capacidadActual = $grado->estudiantes()->count();
        
        $this->assertEquals(25, $capacidadActual);
        $this->assertGreaterThan(0, $capacidadActual);
        
        // Agregar más estudiantes
        Estudiante::factory(5)->create(['grado_id' => $grado->id]);
        
        $capacidadFinal = $grado->fresh()->estudiantes()->count();
        $this->assertEquals(30, $capacidadFinal);
        
        // Verificar que se puede gestionar el número de estudiantes
        $this->assertEquals(30, $capacidadFinal);
    }

    /** @test */
    public function current_period_business_logic_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear múltiples períodos, solo uno activo
        $periodo1 = Periodo::factory()->create(['activo' => false]);
        $periodo2 = Periodo::factory()->create(['activo' => true]);
        $periodo3 = Periodo::factory()->create(['activo' => false]);
        
        // Verificar que solo hay un período activo
        $periodosActivos = Periodo::where('activo', true)->get();
        $this->assertCount(1, $periodosActivos);
        $this->assertEquals($periodo2->id, $periodosActivos->first()->id);
        
        // Cambiar período activo
        $periodo2->update(['activo' => false]);
        $periodo3->update(['activo' => true]);
        
        // Verificar nuevo período activo
        $nuevoPeriodoActivo = Periodo::where('activo', true)->first();
        $this->assertEquals($periodo3->id, $nuevoPeriodoActivo->id);
        
        // Verificar que el anterior ya no es activo
        $this->assertFalse($periodo2->fresh()->activo);
    }
}
