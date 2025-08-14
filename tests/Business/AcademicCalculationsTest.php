<?php

namespace Tests\Business;

use App\Models\Estudiante;
use App\Models\EstudianteLogro;
use App\Models\Grado;
use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalculationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function student_gpa_calculation_is_accurate()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $periodo = $setup['periodos']['periodo1'];
        
        // Crear múltiples evaluaciones con diferentes pesos
        $evaluaciones = [
            ['logro' => $setup['logros']['matematicas_basico'], 'nota' => 4.5, 'peso' => 1.0],
            ['logro' => $setup['logros']['lenguaje_basico'], 'nota' => 4.0, 'peso' => 1.0],
            ['logro' => Logro::factory()->create(['materia_id' => $setup['materias']['matematicas']->id]), 'nota' => 3.8, 'peso' => 1.0],
            ['logro' => Logro::factory()->create(['materia_id' => $setup['materias']['lenguaje']->id]), 'nota' => 4.2, 'peso' => 1.0],
        ];
        
        foreach ($evaluaciones as $evalData) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $evalData['logro']->id,
                'periodo_id' => $periodo->id,
                'nota_cuantitativa' => $evalData['nota'],
                'evaluado' => true,
            ]);
        }
        
        // Calcular GPA simple (promedio aritmético)
        $gpaSimple = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->avg('nota_cuantitativa');
        
        $promedioEsperado = (4.5 + 4.0 + 3.8 + 4.2) / 4;
        $this->assertEquals($promedioEsperado, $gpaSimple);
        $this->assertEquals(4.125, $gpaSimple);
        
        // Calcular GPA por materia
        $gpaMaterias = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->join('logros', 'estudiante_logro.logro_id', '=', 'logros.id')
            ->join('materias', 'logros.materia_id', '=', 'materias.id')
            ->selectRaw('materias.nombre, AVG(estudiante_logro.nota_cuantitativa) as promedio')
            ->groupBy('materias.id', 'materias.nombre')
            ->get();
        
        $this->assertCount(2, $gpaMaterias);
        
        foreach ($gpaMaterias as $gpaMateria) {
            if ($gpaMateria->nombre === 'Matemáticas') {
                $this->assertEquals((4.5 + 3.8) / 2, $gpaMateria->promedio);
            } elseif ($gpaMateria->nombre === 'Lenguaje') {
                $this->assertEquals((4.0 + 4.2) / 2, $gpaMateria->promedio);
            }
        }
    }

    /** @test */
    public function academic_performance_trend_analysis()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $logro = $setup['logros']['matematicas_basico'];
        
        // Crear 4 períodos con tendencia de mejora
        $periodos = [];
        for ($i = 1; $i <= 4; $i++) {
            $periodos[] = Periodo::factory()->create([
                'numero' => $i,
                'año' => 2024,
            ]);
        }
        
        // Crear evaluaciones con tendencia creciente
        $notas = [3.0, 3.5, 4.0, 4.3];
        foreach ($periodos as $index => $periodo) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nota_cuantitativa' => $notas[$index],
                'evaluado' => true,
            ]);
        }
        
        // Calcular tendencia
        $evaluacionesOrdenadas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('logro_id', $logro->id)
            ->join('periodos', 'estudiante_logro.periodo_id', '=', 'periodos.id')
            ->orderBy('periodos.numero')
            ->select('estudiante_logro.nota_cuantitativa', 'periodos.numero')
            ->get();
        
        // Calcular pendiente de la tendencia (regresión lineal simple)
        $n = count($evaluacionesOrdenadas);
        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        
        foreach ($evaluacionesOrdenadas as $eval) {
            $x = $eval->numero;
            $y = $eval->nota_cuantitativa;
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }
        
        $pendiente = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercepto = ($sumY - $pendiente * $sumX) / $n;
        
        // Verificar tendencia positiva
        $this->assertGreaterThan(0, $pendiente, 'La tendencia debería ser positiva');
        
        // Calcular mejora total
        $notaInicial = $evaluacionesOrdenadas->first()->nota_cuantitativa;
        $notaFinal = $evaluacionesOrdenadas->last()->nota_cuantitativa;
        $mejoraPorcentual = (($notaFinal - $notaInicial) / $notaInicial) * 100;
        
        $this->assertGreaterThan(40, $mejoraPorcentual, 'Debería haber al menos 40% de mejora');
        
        // Verificar consistencia en la mejora
        $mejorasPorPeriodo = [];
        for ($i = 1; $i < count($evaluacionesOrdenadas); $i++) {
            $anterior = $evaluacionesOrdenadas[$i - 1]->nota_cuantitativa;
            $actual = $evaluacionesOrdenadas[$i]->nota_cuantitativa;
            $mejorasPorPeriodo[] = $actual - $anterior;
        }
        
        // Todas las mejoras deberían ser positivas o neutras
        foreach ($mejorasPorPeriodo as $mejora) {
            $this->assertGreaterThanOrEqual(0, $mejora, 'No debería haber retrocesos');
        }
    }

    /** @test */
    public function class_performance_statistics_calculation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $materia = Materia::factory()->create(['grado_id' => $grado->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $periodo = Periodo::factory()->create();
        
        // Crear múltiples estudiantes con diferentes rendimientos
        $estudiantes = Estudiante::factory(10)->create(['grado_id' => $grado->id]);
        $notas = [2.5, 3.0, 3.2, 3.5, 3.8, 4.0, 4.2, 4.5, 4.7, 5.0];
        
        foreach ($estudiantes as $index => $estudiante) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nota_cuantitativa' => $notas[$index],
                'evaluado' => true,
            ]);
        }
        
        // Calcular estadísticas del curso
        $estadisticas = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->selectRaw('
                COUNT(*) as total_estudiantes,
                AVG(nota_cuantitativa) as promedio,
                MIN(nota_cuantitativa) as nota_minima,
                MAX(nota_cuantitativa) as nota_maxima,
                STDDEV(nota_cuantitativa) as desviacion_estandar
            ')
            ->first();
        
        // Verificar estadísticas básicas
        $this->assertEquals(10, $estadisticas->total_estudiantes);
        $this->assertEquals(3.74, round($estadisticas->promedio, 2));
        $this->assertEquals(2.5, $estadisticas->nota_minima);
        $this->assertEquals(5.0, $estadisticas->nota_maxima);
        
        // Calcular distribución por niveles de desempeño
        $distribucionNiveles = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->selectRaw('
                CASE 
                    WHEN nota_cuantitativa >= 4.6 THEN "superior"
                    WHEN nota_cuantitativa >= 4.0 THEN "alto"
                    WHEN nota_cuantitativa >= 3.0 THEN "básico"
                    ELSE "bajo"
                END as nivel,
                COUNT(*) as cantidad
            ')
            ->groupByRaw('
                CASE 
                    WHEN nota_cuantitativa >= 4.6 THEN "superior"
                    WHEN nota_cuantitativa >= 4.0 THEN "alto"
                    WHEN nota_cuantitativa >= 3.0 THEN "básico"
                    ELSE "bajo"
                END
            ')
            ->get();
        
        // Verificar distribución esperada
        $distribucionArray = $distribucionNiveles->pluck('cantidad', 'nivel')->toArray();
        
        $this->assertEquals(1, $distribucionArray['bajo'] ?? 0);      // 2.5
        $this->assertEquals(6, $distribucionArray['básico'] ?? 0);    // 3.0-3.8
        $this->assertEquals(2, $distribucionArray['alto'] ?? 0);      // 4.0-4.2
        $this->assertEquals(1, $distribucionArray['superior'] ?? 0);  // 4.7, 5.0
        
        // Calcular percentiles
        $notasOrdenadas = collect($notas)->sort()->values();
        $mediana = $notasOrdenadas->count() % 2 === 0 
            ? ($notasOrdenadas[4] + $notasOrdenadas[5]) / 2 
            : $notasOrdenadas[5];
        
        $this->assertEquals(3.9, $mediana);
    }

    /** @test */
    public function achievement_completion_percentage_calculation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $grado = $setup['grados']['primero'];
        $periodo = $setup['periodos']['periodo1'];
        
        // Crear múltiples materias y logros
        $materias = Materia::factory(3)->create(['grado_id' => $grado->id]);
        $logros = [];
        
        foreach ($materias as $materia) {
            $logros = array_merge($logros, Logro::factory(4)->create([
                'materia_id' => $materia->id
            ])->toArray());
        }
        
        $totalLogros = count($logros);
        $estudiante = $setup['estudiantes']['juan'];
        
        // Evaluar solo algunos logros (75% de completitud)
        $logrosAEvaluar = array_slice($logros, 0, (int)($totalLogros * 0.75));
        
        foreach ($logrosAEvaluar as $logro) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro['id'],
                'periodo_id' => $periodo->id,
                'nota_cuantitativa' => rand(35, 50) / 10, // 3.5 - 5.0
                'evaluado' => true,
            ]);
        }
        
        // Calcular porcentaje de completitud
        $logrosEvaluados = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->count();
        
        $porcentajeCompletitud = ($logrosEvaluados / $totalLogros) * 100;
        
        $this->assertEquals(75, $porcentajeCompletitud);
        $this->assertEquals(9, $logrosEvaluados); // 75% de 12 logros
        
        // Calcular completitud por materia
        foreach ($materias as $materia) {
            $logrosMateria = Logro::where('materia_id', $materia->id)->count();
            $logrosEvaluadosMateria = EstudianteLogro::where('estudiante_id', $estudiante->id)
                ->where('periodo_id', $periodo->id)
                ->where('evaluado', true)
                ->whereHas('logro', function ($query) use ($materia) {
                    $query->where('materia_id', $materia->id);
                })
                ->count();
            
            $completitudMateria = ($logrosEvaluadosMateria / $logrosMateria) * 100;
            
            // Cada materia debería tener aproximadamente 75% de completitud
            $this->assertTrue($completitudMateria >= 50 && $completitudMateria <= 100);
        }
    }

    /** @test */
    public function weighted_grade_calculation_with_periods()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $logro = $setup['logros']['matematicas_basico'];
        
        // Crear períodos con diferentes pesos
        $periodos = [
            Periodo::factory()->create(['numero' => 1, 'porcentaje_nota' => 20.0, 'año' => 2024]),
            Periodo::factory()->create(['numero' => 2, 'porcentaje_nota' => 25.0, 'año' => 2024]),
            Periodo::factory()->create(['numero' => 3, 'porcentaje_nota' => 25.0, 'año' => 2024]),
            Periodo::factory()->create(['numero' => 4, 'porcentaje_nota' => 30.0, 'año' => 2024]),
        ];
        
        // Crear evaluaciones para cada período
        $notasPorPeriodo = [3.5, 4.0, 4.2, 4.5];
        
        foreach ($periodos as $index => $periodo) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nota_cuantitativa' => $notasPorPeriodo[$index],
                'evaluado' => true,
            ]);
        }
        
        // Calcular nota ponderada final
        $notaPonderada = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('logro_id', $logro->id)
            ->where('evaluado', true)
            ->join('periodos', 'estudiante_logro.periodo_id', '=', 'periodos.id')
            ->selectRaw('SUM(nota_cuantitativa * porcentaje_nota / 100) as nota_final')
            ->value('nota_final');
        
        // Calcular manualmente para verificar
        $notaEsperada = (3.5 * 0.20) + (4.0 * 0.25) + (4.2 * 0.25) + (4.5 * 0.30);
        $this->assertEquals(round($notaEsperada, 2), round($notaPonderada, 2));
        $this->assertEquals(4.15, round($notaPonderada, 2));
        
        // Verificar que los porcentajes suman 100%
        $totalPorcentaje = collect($periodos)->sum('porcentaje_nota');
        $this->assertEquals(100.0, $totalPorcentaje);
    }

    /** @test */
    public function subject_area_performance_comparison()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $periodo = Periodo::factory()->create();
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado->id]);
        
        // Crear materias de diferentes áreas
        $areas = ['Matemáticas', 'Lenguaje', 'Ciencias Naturales', 'Ciencias Sociales'];
        $materiasYLogros = [];
        
        foreach ($areas as $area) {
            $materia = Materia::factory()->create([
                'grado_id' => $grado->id,
                'area' => $area,
                'nombre' => $area,
            ]);
            
            $logros = Logro::factory(3)->create(['materia_id' => $materia->id]);
            $materiasYLogros[$area] = ['materia' => $materia, 'logros' => $logros];
        }
        
        // Crear evaluaciones con diferentes rendimientos por área
        $notasPorArea = [
            'Matemáticas' => [4.5, 4.3, 4.7],        // Promedio: 4.5
            'Lenguaje' => [3.8, 4.0, 3.9],           // Promedio: 3.9
            'Ciencias Naturales' => [4.2, 4.1, 4.3], // Promedio: 4.2
            'Ciencias Sociales' => [3.5, 3.7, 3.6],  // Promedio: 3.6
        ];
        
        foreach ($areas as $area) {
            $logros = $materiasYLogros[$area]['logros'];
            $notas = $notasPorArea[$area];
            
            foreach ($logros as $index => $logro) {
                EstudianteLogro::create([
                    'estudiante_id' => $estudiante->id,
                    'logro_id' => $logro->id,
                    'periodo_id' => $periodo->id,
                    'nota_cuantitativa' => $notas[$index],
                    'evaluado' => true,
                ]);
            }
        }
        
        // Calcular promedios por área
        $promediosPorArea = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->join('logros', 'estudiante_logro.logro_id', '=', 'logros.id')
            ->join('materias', 'logros.materia_id', '=', 'materias.id')
            ->selectRaw('materias.area, AVG(estudiante_logro.nota_cuantitativa) as promedio')
            ->groupBy('materias.area')
            ->orderBy('promedio', 'desc')
            ->get();
        
        // Verificar ordenamiento por rendimiento
        $this->assertEquals('Matemáticas', $promediosPorArea[0]->area);
        $this->assertEquals(4.5, round($promediosPorArea[0]->promedio, 1));
        
        $this->assertEquals('Ciencias Naturales', $promediosPorArea[1]->area);
        $this->assertEquals(4.2, round($promediosPorArea[1]->promedio, 1));
        
        $this->assertEquals('Lenguaje', $promediosPorArea[2]->area);
        $this->assertEquals(3.9, round($promediosPorArea[2]->promedio, 1));
        
        $this->assertEquals('Ciencias Sociales', $promediosPorArea[3]->area);
        $this->assertEquals(3.6, round($promediosPorArea[3]->promedio, 1));
        
        // Identificar área de mayor y menor rendimiento
        $areaMayorRendimiento = $promediosPorArea->first();
        $areaMenorRendimiento = $promediosPorArea->last();
        
        $this->assertEquals('Matemáticas', $areaMayorRendimiento->area);
        $this->assertEquals('Ciencias Sociales', $areaMenorRendimiento->area);
        
        // Calcular brecha de rendimiento
        $brechaRendimiento = $areaMayorRendimiento->promedio - $areaMenorRendimiento->promedio;
        $this->assertEquals(0.9, round($brechaRendimiento, 1));
    }

    /** @test */
    public function academic_risk_level_calculation()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $periodo = $setup['periodos']['periodo1'];
        
        // Crear estudiantes con diferentes niveles de riesgo
        $estudiantes = [
            ['nombre' => 'Excelente', 'promedio' => 4.8, 'riesgo_esperado' => 'bajo'],
            ['nombre' => 'Bueno', 'promedio' => 4.2, 'riesgo_esperado' => 'bajo'],
            ['nombre' => 'Regular', 'promedio' => 3.5, 'riesgo_esperado' => 'medio'],
            ['nombre' => 'En riesgo', 'promedio' => 2.8, 'riesgo_esperado' => 'alto'],
            ['nombre' => 'Crítico', 'promedio' => 2.2, 'riesgo_esperado' => 'crítico'],
        ];
        
        foreach ($estudiantes as $estudianteData) {
            $estudiante = Estudiante::factory()->create([
                'nombres' => $estudianteData['nombre'],
                'grado_id' => $setup['grados']['primero']->id,
            ]);
            
            // Crear evaluaciones para simular el promedio deseado
            $logros = Logro::factory(4)->create();
            
            foreach ($logros as $logro) {
                EstudianteLogro::create([
                    'estudiante_id' => $estudiante->id,
                    'logro_id' => $logro->id,
                    'periodo_id' => $periodo->id,
                    'nota_cuantitativa' => $estudianteData['promedio'],
                    'evaluado' => true,
                ]);
            }
        }
        
        // Calcular niveles de riesgo
        $estudiantesConRiesgo = EstudianteLogro::where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->selectRaw('
                estudiante_id,
                AVG(nota_cuantitativa) as promedio,
                CASE 
                    WHEN AVG(nota_cuantitativa) >= 4.5 THEN "bajo"
                    WHEN AVG(nota_cuantitativa) >= 3.8 THEN "bajo"
                    WHEN AVG(nota_cuantitativa) >= 3.0 THEN "medio"
                    WHEN AVG(nota_cuantitativa) >= 2.5 THEN "alto"
                    ELSE "crítico"
                END as nivel_riesgo
            ')
            ->groupBy('estudiante_id')
            ->get();
        
        // Verificar clasificación de riesgo
        $this->assertCount(5, $estudiantesConRiesgo);
        
        foreach ($estudiantesConRiesgo as $estudianteRiesgo) {
            $estudiante = Estudiante::find($estudianteRiesgo->estudiante_id);
            $estudianteOriginal = collect($estudiantes)->firstWhere('nombre', $estudiante->nombres);
            
            if ($estudianteOriginal) {
                $this->assertEquals(
                    $estudianteOriginal['riesgo_esperado'], 
                    $estudianteRiesgo->nivel_riesgo,
                    "Riesgo incorrecto para {$estudiante->nombres}"
                );
            }
        }
        
        // Contar estudiantes por nivel de riesgo
        $distribucionRiesgo = $estudiantesConRiesgo->groupBy('nivel_riesgo')->map->count();
        
        $this->assertEquals(2, $distribucionRiesgo['bajo'] ?? 0);
        $this->assertEquals(1, $distribucionRiesgo['medio'] ?? 0);
        $this->assertEquals(1, $distribucionRiesgo['alto'] ?? 0);
        $this->assertEquals(1, $distribucionRiesgo['crítico'] ?? 0);
        
        // Identificar estudiantes que requieren intervención
        $estudiantesEnRiesgo = $estudiantesConRiesgo->filter(function ($item) {
            return in_array($item->nivel_riesgo, ['alto', 'crítico']);
        });
        
        $this->assertCount(2, $estudiantesEnRiesgo);
    }
}
