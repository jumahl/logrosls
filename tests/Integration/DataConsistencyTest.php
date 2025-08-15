<?php

namespace Tests\Integration;

use App\Models\Estudiante;
use App\Models\EstudianteLogro;
use App\Models\Grado;
use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function academic_hierarchy_consistency_is_maintained()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $profesorDirector = $this->createProfesorDirector();
        $profesor = $this->createProfesor();
        
        // Crear jerarquía académica completa
        $grado = Grado::factory()->create([
            'tipo' => 'primaria',
        ]);
        
        // Asignar director mediante director_grado_id
        $profesorDirector->update(['director_grado_id' => $grado->id]);
        
        $materia = Materia::factory()->create([
            'docente_id' => $profesor->id,
        ]);
        
        // Asociar materia con grado
        $materia->grados()->attach($grado->id);
        
        $estudiantes = Estudiante::factory(5)->create([
            'grado_id' => $grado->id,
        ]);
        
        $logros = Logro::factory(3)->create([
            'materia_id' => $materia->id,
        ]);
        
        $periodo = Periodo::factory()->create();
        
        // Verificar consistencia de la jerarquía
        $this->assertEquals($grado->id, $profesorDirector->fresh()->director_grado_id);
        $this->assertEquals($profesor->id, $materia->docente_id);
        $this->assertTrue($materia->grados->contains($grado->id));
        
        foreach ($estudiantes as $estudiante) {
            $this->assertEquals($grado->id, $estudiante->grado_id);
        }
        
        foreach ($logros as $logro) {
            $this->assertEquals($materia->id, $logro->materia_id);
        }
        
        // Verificar navegación en ambas direcciones
        $this->assertCount(1, $director->grados);
        $this->assertCount(1, $profesor->materias);
        $this->assertCount(1, $grado->materias);
        $this->assertCount(5, $grado->estudiantes);
        $this->assertCount(3, $materia->logros);
    }

    /** @test */
    public function evaluation_consistency_across_periods()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $logro = $setup['logros']['matematicas_basico'];
        
        // Crear múltiples períodos
        $periodo1 = Periodo::factory()->create(['numero' => 1, 'año' => 2024]);
        $periodo2 = Periodo::factory()->create(['numero' => 2, 'año' => 2024]);
        $periodo3 = Periodo::factory()->create(['numero' => 3, 'año' => 2024]);
        
        // Crear evaluaciones progresivas
        $eval1 = EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo1->id,
            'nivel_desempeno' => 'básico',
            'nota_cuantitativa' => 3.0,
            'evaluado' => true,
        ]);
        
        $eval2 = EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo2->id,
            'nivel_desempeno' => 'alto',
            'nota_cuantitativa' => 4.0,
            'evaluado' => true,
        ]);
        
        $eval3 = EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo3->id,
            'nivel_desempeno' => 'superior',
            'nota_cuantitativa' => 4.5,
            'evaluado' => true,
        ]);
        
        // Verificar consistencia temporal
        $evaluacionesOrdenadas = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('logro_id', $logro->id)
            ->join('periodos', 'estudiante_logro.periodo_id', '=', 'periodos.id')
            ->orderBy('periodos.numero')
            ->select('estudiante_logro.*')
            ->get();
        
        $this->assertCount(3, $evaluacionesOrdenadas);
        $this->assertEquals(3.0, $evaluacionesOrdenadas[0]->nota_cuantitativa);
        $this->assertEquals(4.0, $evaluacionesOrdenadas[1]->nota_cuantitativa);
        $this->assertEquals(4.5, $evaluacionesOrdenadas[2]->nota_cuantitativa);
        
        // Verificar progresión lógica
        $this->assertTrue($evaluacionesOrdenadas[0]->nota_cuantitativa <= $evaluacionesOrdenadas[1]->nota_cuantitativa);
        $this->assertTrue($evaluacionesOrdenadas[1]->nota_cuantitativa <= $evaluacionesOrdenadas[2]->nota_cuantitativa);
    }

    /** @test */
    public function grade_level_and_subjects_consistency()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear grados de diferentes niveles
        $gradoPrimaria = Grado::factory()->create([
            'tipo' => 'primaria',
            'nombre' => 'Tercero',
        ]);
        
        $gradoSecundaria = Grado::factory()->create([
            'tipo' => 'secundaria',
            'nombre' => 'Octavo',
        ]);
        
        $gradoMedia = Grado::factory()->create([
            'tipo' => 'media_academica',
            'nombre' => 'Undécimo',
        ]);
        
        // Crear materias apropiadas para cada nivel (sin intensidad_horaria)
        $materiaPrimariaBasica = Materia::factory()->create([
            'nombre' => 'Matemáticas Básicas',
        ]);
        $materiaPrimariaBasica->grados()->attach($gradoPrimaria->id);
        
        $materiaSecundariaIntermedia = Materia::factory()->create([
            'nombre' => 'Álgebra',
        ]);
        $materiaSecundariaIntermedia->grados()->attach($gradoSecundaria->id);
        
        $materiaMediaAvanzada = Materia::factory()->create([
            'nombre' => 'Cálculo',
        ]);
        $materiaMediaAvanzada->grados()->attach($gradoMedia->id);
        
        // Crear logros de complejidad apropiada
        $logroPrimaria = Logro::factory()->create([
            'materia_id' => $materiaPrimariaBasica->id,
            'nivel' => 'básico',
            'tipo' => 'conceptual',
        ]);
        
        $logroSecundaria = Logro::factory()->create([
            'materia_id' => $materiaSecundariaIntermedia->id,
            'nivel' => 'intermedio',
            'tipo' => 'procedimental',
        ]);
        
        $logroMedia = Logro::factory()->create([
            'materia_id' => $materiaMediaAvanzada->id,
            'nivel' => 'avanzado',
            'tipo' => 'actitudinal',
        ]);
        
        // Verificar consistencia nivel-materia-logro
        $this->assertEquals('primaria', $logroPrimaria->materia->grado->nivel);
        $this->assertEquals('básico', $logroPrimaria->nivel);
        
        $this->assertEquals('secundaria', $logroSecundaria->materia->grado->nivel);
        $this->assertEquals('intermedio', $logroSecundaria->nivel);
        
        $this->assertEquals('media', $logroMedia->materia->grado->nivel);
        $this->assertEquals('avanzado', $logroMedia->nivel);
        
        // Verificar que las materias están asociadas con los grados correctos
        $this->assertTrue($materiaPrimariaBasica->grados->contains($gradoPrimaria));
        $this->assertTrue($materiaSecundariaIntermedia->grados->contains($gradoSecundaria));
        $this->assertTrue($materiaMediaAvanzada->grados->contains($gradoMedia));
    }

    /** @test */
    public function student_age_and_grade_consistency()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear estudiantes de diferentes edades en grados apropiados
        $gradoPrimero = Grado::factory()->create(['tipo' => 'primaria', 'nombre' => 'Primero']);
        $gradoQuinto = Grado::factory()->create(['tipo' => 'primaria', 'nombre' => 'Quinto']);
        $gradoNoveno = Grado::factory()->create(['tipo' => 'secundaria', 'nombre' => 'Noveno']);
        $gradoOnce = Grado::factory()->create(['tipo' => 'media_academica', 'nombre' => 'Once']);
        
        $estudiantePrimero = Estudiante::factory()->niño()->create([
            'grado_id' => $gradoPrimero->id,
        ]);
        
        $estudianteQuinto = Estudiante::factory()->niño()->create([
            'grado_id' => $gradoQuinto->id,
        ]);
        
        $estudianteNoveno = Estudiante::factory()->adolescente()->create([
            'grado_id' => $gradoNoveno->id,
        ]);
        
        $estudianteOnce = Estudiante::factory()->joven()->create([
            'grado_id' => $gradoOnce->id,
        ]);
        
        // Verificar coherencia edad-grado
        $edadPrimero = $estudiantePrimero->fecha_nacimiento->age;
        $edadQuinto = $estudianteQuinto->fecha_nacimiento->age;
        $edadNoveno = $estudianteNoveno->fecha_nacimiento->age;
        $edadOnce = $estudianteOnce->fecha_nacimiento->age;
        
        // Rangos típicos de edad por grado
        $this->assertTrue($edadPrimero >= 5 && $edadPrimero <= 8);  // Primero: 6-7 años típicamente
        $this->assertTrue($edadQuinto >= 8 && $edadQuinto <= 12);   // Quinto: 10-11 años típicamente
        $this->assertTrue($edadNoveno >= 12 && $edadNoveno <= 16);  // Noveno: 14-15 años típicamente
        $this->assertTrue($edadOnce >= 15 && $edadOnce <= 19);      // Once: 16-17 años típicamente
        
        // Verificar progresión lógica
        $this->assertTrue($edadPrimero <= $edadQuinto);
        $this->assertTrue($edadQuinto <= $edadNoveno);
        $this->assertTrue($edadNoveno <= $edadOnce);
    }

    /** @test */
    public function period_date_ranges_do_not_overlap()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear períodos consecutivos sin solapamiento
        $periodo1 = Periodo::factory()->create([
            'numero' => 1,
            'fecha_inicio' => '2024-02-01',
            'fecha_fin' => '2024-04-30',
            'año' => 2024,
        ]);
        
        $periodo2 = Periodo::factory()->create([
            'numero' => 2,
            'fecha_inicio' => '2024-05-01',
            'fecha_fin' => '2024-07-31',
            'año' => 2024,
        ]);
        
        $periodo3 = Periodo::factory()->create([
            'numero' => 3,
            'fecha_inicio' => '2024-08-01',
            'fecha_fin' => '2024-10-31',
            'año' => 2024,
        ]);
        
        $periodo4 = Periodo::factory()->create([
            'numero' => 4,
            'fecha_inicio' => '2024-11-01',
            'fecha_fin' => '2024-12-15',
            'año' => 2024,
        ]);
        
        $periodos = [$periodo1, $periodo2, $periodo3, $periodo4];
        
        // Verificar que no hay solapamientos
        for ($i = 0; $i < count($periodos) - 1; $i++) {
            $periodoActual = $periodos[$i];
            $periodoSiguiente = $periodos[$i + 1];
            
            $this->assertTrue(
                $periodoActual->fecha_fin < $periodoSiguiente->fecha_inicio,
                "El período {$periodoActual->numero} se solapa con el período {$periodoSiguiente->numero}"
            );
        }
        
        // Verificar que cada período es válido internamente
        foreach ($periodos as $periodo) {
            $this->assertTrue(
                $periodo->fecha_inicio < $periodo->fecha_fin,
                "El período {$periodo->numero} tiene fechas inválidas"
            );
        }
    }

    /** @test */
    public function evaluation_notes_are_consistent_with_performance_levels()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $setup = $this->createAcademicSetup();
        
        // Crear evaluaciones con notas consistentes con niveles
        $evaluaciones = [
            ['nivel' => 'bajo', 'nota' => 2.5],
            ['nivel' => 'básico', 'nota' => 3.2],
            ['nivel' => 'alto', 'nota' => 4.1],
            ['nivel' => 'superior', 'nota' => 4.8],
        ];
        
        foreach ($evaluaciones as $index => $evalData) {
            $estudiante = Estudiante::factory()->create();
            
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $setup['logros']['matematicas_basico']->id,
                'periodo_id' => $setup['periodos']['periodo1']->id,
                'nivel_desempeno' => $evalData['nivel'],
                'nota_cuantitativa' => $evalData['nota'],
                'evaluado' => true,
            ]);
        }
        
        // Verificar rangos típicos por nivel de desempeño
        $evaluacionBajo = EstudianteLogro::where('nivel_desempeno', 'bajo')->first();
        $evaluacionBasico = EstudianteLogro::where('nivel_desempeno', 'básico')->first();
        $evaluacionAlto = EstudianteLogro::where('nivel_desempeno', 'alto')->first();
        $evaluacionSuperior = EstudianteLogro::where('nivel_desempeno', 'superior')->first();
        
        // Rangos esperados (ajustar según el sistema de evaluación)
        $this->assertTrue($evaluacionBajo->nota_cuantitativa >= 1.0 && $evaluacionBajo->nota_cuantitativa < 3.0);
        $this->assertTrue($evaluacionBasico->nota_cuantitativa >= 3.0 && $evaluacionBasico->nota_cuantitativa < 4.0);
        $this->assertTrue($evaluacionAlto->nota_cuantitativa >= 4.0 && $evaluacionAlto->nota_cuantitativa < 4.6);
        $this->assertTrue($evaluacionSuperior->nota_cuantitativa >= 4.6 && $evaluacionSuperior->nota_cuantitativa <= 5.0);
        
        // Verificar orden progresivo
        $this->assertTrue($evaluacionBajo->nota_cuantitativa < $evaluacionBasico->nota_cuantitativa);
        $this->assertTrue($evaluacionBasico->nota_cuantitativa < $evaluacionAlto->nota_cuantitativa);
        $this->assertTrue($evaluacionAlto->nota_cuantitativa < $evaluacionSuperior->nota_cuantitativa);
    }

    /** @test */
    public function teacher_subject_assignment_consistency()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $profesor1 = $this->createProfesor();
        $profesor2 = $this->createProfesor();
        
        // Crear materias asignadas a profesores específicos
        $materiaMatematicas = Materia::factory()->create([
            'docente_id' => $profesor1->id,
            'area' => 'Matemáticas',
            'nombre' => 'Álgebra',
        ]);
        
        $materiaLenguaje = Materia::factory()->create([
            'docente_id' => $profesor2->id,
            'area' => 'Lenguaje',
            'nombre' => 'Literatura',
        ]);
        
        // Crear logros para cada materia
        $logroMatematicas = Logro::factory()->create([
            'materia_id' => $materiaMatematicas->id,
        ]);
        
        $logroLenguaje = Logro::factory()->create([
            'materia_id' => $materiaLenguaje->id,
        ]);
        
        // Verificar que cada profesor está asociado correctamente
        $this->assertTrue($profesor1->materias->contains($materiaMatematicas));
        $this->assertFalse($profesor1->materias->contains($materiaLenguaje));
        
        $this->assertTrue($profesor2->materias->contains($materiaLenguaje));
        $this->assertFalse($profesor2->materias->contains($materiaMatematicas));
        
        // Verificar navegación a través de logros
        $this->assertEquals($profesor1->id, $logroMatematicas->materia->docente_id);
        $this->assertEquals($profesor2->id, $logroLenguaje->materia->docente_id);
        
        // Un profesor no debería tener acceso a logros de otra materia
        $materiasProfesor1 = $profesor1->materias->pluck('id');
        $materiasProfesor2 = $profesor2->materias->pluck('id');
        
        $this->assertTrue($materiasProfesor1->contains($materiaMatematicas->id));
        $this->assertFalse($materiasProfesor1->contains($materiaLenguaje->id));
        
        $this->assertTrue($materiasProfesor2->contains($materiaLenguaje->id));
        $this->assertFalse($materiasProfesor2->contains($materiaMatematicas->id));
    }

    /** @test */
    public function data_integrity_after_bulk_updates()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $estudiantes = Estudiante::factory(10)->create(['grado_id' => $grado->id]);
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        // Crear evaluaciones para todos los estudiantes
        foreach ($estudiantes as $estudiante) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'básico',
                'nota_cuantitativa' => 3.0,
                'evaluado' => false,
            ]);
        }
        
        // Realizar actualización bulk
        $updated = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->update([
                'evaluado' => true,
                'fecha_evaluacion' => now(),
                'nivel_desempeno' => 'alto',
                'nota_cuantitativa' => 4.0,
            ]);
        
        $this->assertEquals(10, $updated);
        
        // Verificar integridad después de la actualización
        $evaluacionesActualizadas = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->get();
        
        $this->assertCount(10, $evaluacionesActualizadas);
        
        foreach ($evaluacionesActualizadas as $evaluacion) {
            $this->assertTrue($evaluacion->evaluado);
            $this->assertEquals('alto', $evaluacion->nivel_desempeno);
            $this->assertEquals(4.0, $evaluacion->nota_cuantitativa);
            $this->assertNotNull($evaluacion->fecha_evaluacion);
            
            // Verificar que las relaciones siguen intactas
            $this->assertNotNull($evaluacion->estudiante);
            $this->assertNotNull($evaluacion->logro);
            $this->assertNotNull($evaluacion->periodo);
            $this->assertEquals($grado->id, $evaluacion->estudiante->grado_id);
        }
    }

    /** @test */
    public function orphaned_records_prevention()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado->id]);
        $materia = Materia::factory()->create(['grado_id' => $grado->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $periodo = Periodo::factory()->create();
        
        $evaluacion = EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'alto',
        ]);
        
        // Intentar eliminar el grado debería eliminar en cascada
        $grado->delete();
        
        // Verificar que no quedan registros huérfanos
        $this->assertDatabaseMissing('estudiantes', ['id' => $estudiante->id]);
        $this->assertDatabaseMissing('materias', ['id' => $materia->id]);
        $this->assertDatabaseMissing('logros', ['id' => $logro->id]);
        $this->assertDatabaseMissing('estudiante_logro', ['id' => $evaluacion->id]);
        
        // El período debería mantenerse ya que no depende del grado
        $this->assertDatabaseHas('periodos', ['id' => $periodo->id]);
    }
}
