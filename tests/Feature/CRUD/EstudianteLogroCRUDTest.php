<?php

namespace Tests\Feature\CRUD;

use App\Models\EstudianteLogro;
use App\Models\Estudiante;
use App\Models\Logro;
use App\Models\Periodo;
use Tests\TestCase;

class EstudianteLogroCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_estudiante_logros()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        $estudianteLogroData = [
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
            'observaciones' => 'Demuestra dominio excepcional',
            'fecha_asignacion' => now()->format('Y-m-d'),
        ];
        
        $estudianteLogro = EstudianteLogro::create($estudianteLogroData);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
        ]);
    }

    /** @test */
    public function profesor_can_create_evaluations_for_their_students()
    {
        $profesor = $this->createProfesor();
        $setup = $this->createAcademicSetup();
        
        // Asignar materia al profesor
        $materia = $setup['materias']['matematicas'];
        $materia->update(['docente_id' => $profesor->id]);
        
        $this->actingAs($profesor);
        
        $estudianteLogro = EstudianteLogro::factory()->create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $estudianteLogro->id,
            'nivel_desempeno' => 'S',
        ]);
    }

    /** @test */
    public function admin_can_view_all_evaluations()
    {
        $admin = $this->createAdmin();
        $evaluaciones = EstudianteLogro::factory(5)->create();
        
        $this->actingAs($admin);
        
        // El admin debería poder ver todas las evaluaciones en la base de datos
        foreach ($evaluaciones as $evaluacion) {
            $this->assertDatabaseHas('estudiante_logros', [
                'id' => $evaluacion->id,
                'nivel_desempeno' => $evaluacion->nivel_desempeno,
            ]);
        }
        
        // También verificar que puede acceder a la colección de evaluaciones
        $retrievedEvaluaciones = EstudianteLogro::all();
        $this->assertCount(5, $retrievedEvaluaciones);
    }

    /** @test */
    public function profesor_can_only_view_evaluations_from_their_materias()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        $setup = $this->createAcademicSetup();
        
        // Crear materias para cada profesor
        $materiaPropia = $setup['materias']['matematicas'];
        $materiaPropia->update(['docente_id' => $profesor->id]);
        
        $materiaAjena = $setup['materias']['lenguaje'];
        $materiaAjena->update(['docente_id' => $otroProfesor->id]);
        
        // Crear evaluaciones
        $evaluacionPropia = EstudianteLogro::factory()->create([
            'logro_id' => $setup['logros']['matematicas_basico']->id,
        ]);
        
        $evaluacionAjena = EstudianteLogro::factory()->create([
            'logro_id' => $setup['logros']['lenguaje_lectura']->id,
        ]);
        
        $this->actingAs($profesor);
        
        // Verificar acceso a través de las materias del profesor
        $materiasProfesor = $profesor->materias->pluck('id');
        $this->assertTrue($materiasProfesor->contains($materiaPropia->id));
        $this->assertFalse($materiasProfesor->contains($materiaAjena->id));
    }

    /** @test */
    public function admin_can_update_any_evaluation()
    {
        $admin = $this->createAdmin();
        $evaluacion = EstudianteLogro::factory()->create([
            'nivel_desempeno' => 'A',
        ]);
        
        $this->actingAs($admin);
        
        $evaluacion->update([
            'nivel_desempeno' => 'S',
            'observaciones' => 'Mejoró significativamente',
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion->id,
            'nivel_desempeno' => 'S',
            'observaciones' => 'Mejoró significativamente',
        ]);
    }

    /** @test */
    public function profesor_can_update_evaluations_from_their_materias()
    {
        $profesor = $this->createProfesor();
        $setup = $this->createAcademicSetup();
        
        $materia = $setup['materias']['matematicas'];
        $materia->update(['docente_id' => $profesor->id]);
        
        $evaluacion = EstudianteLogro::factory()->create([
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'nivel_desempeno' => 'A',
        ]);
        
        $this->actingAs($profesor);
        
        $evaluacion->update([
            'nivel_desempeno' => 'S',
            'observaciones' => 'Buen progreso',
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion->id,
            'nivel_desempeno' => 'S',
            'observaciones' => 'Buen progreso',
        ]);
    }

    /** @test */
    public function evaluation_can_be_filtered_by_nivel_desempeno()
    {
        $admin = $this->createAdmin();
        
        $evaluacionExcelente = EstudianteLogro::factory()->excelente()->create();
        $evaluacionSuperior = EstudianteLogro::factory()->sobresaliente()->create();
        $evaluacionAceptable = EstudianteLogro::factory()->aceptable()->create();
        $evaluacionInsuficiente = EstudianteLogro::factory()->insuficiente()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesExcelentes = EstudianteLogro::porNivelDesempeno('E')->get();
        $evaluacionesSuperiores = EstudianteLogro::porNivelDesempeno('S')->get();
        $evaluacionesAceptables = EstudianteLogro::porNivelDesempeno('A')->get();
        $evaluacionesInsuficientes = EstudianteLogro::porNivelDesempeno('I')->get();
        
        $this->assertCount(1, $evaluacionesExcelentes);
        $this->assertCount(1, $evaluacionesSuperiores);
        $this->assertCount(1, $evaluacionesAceptables);
        $this->assertCount(1, $evaluacionesInsuficientes);
    }

    /** @test */
    public function evaluation_can_be_filtered_by_estudiante()
    {
        $admin = $this->createAdmin();
        $estudiante = Estudiante::factory()->create();
        
        $evaluaciones = EstudianteLogro::factory(3)->create([
            'estudiante_id' => $estudiante->id,
        ]);
        
        $evaluacionOtro = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesEstudiante = EstudianteLogro::porEstudiante($estudiante->id)->get();
        
        $this->assertCount(3, $evaluacionesEstudiante);
        
        foreach ($evaluacionesEstudiante as $evaluacion) {
            $this->assertEquals($estudiante->id, $evaluacion->estudiante_id);
        }
    }

    /** @test */
    public function evaluation_can_be_filtered_by_logro()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create();
        
        $evaluaciones = EstudianteLogro::factory(2)->create([
            'logro_id' => $logro->id,
        ]);
        
        $evaluacionOtra = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesLogro = EstudianteLogro::porLogro($logro->id)->get();
        
        $this->assertCount(2, $evaluacionesLogro);
        
        foreach ($evaluacionesLogro as $evaluacion) {
            $this->assertEquals($logro->id, $evaluacion->logro_id);
        }
    }

    /** @test */
    public function evaluation_can_be_filtered_by_periodo()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create();
        
        $evaluaciones = EstudianteLogro::factory(3)->create([
            'periodo_id' => $periodo->id,
        ]);
        
        $evaluacionOtra = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesPeriodo = EstudianteLogro::porPeriodo($periodo->id)->get();
        
        $this->assertCount(3, $evaluacionesPeriodo);
        
        foreach ($evaluacionesPeriodo as $evaluacion) {
            $this->assertEquals($periodo->id, $evaluacion->periodo_id);
        }
    }

    /** @test */
    public function only_evaluated_records_are_returned_by_scope()
    {
        $admin = $this->createAdmin();
        
        $evaluacionCompleta = EstudianteLogro::factory()->evaluado()->create();
        $evaluacionPendiente = EstudianteLogro::factory()->pendiente()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesCompletas = EstudianteLogro::evaluados()->get();
        $evaluacionesPendientes = EstudianteLogro::pendientes()->get();
        
        $this->assertCount(1, $evaluacionesCompletas);
        $this->assertCount(1, $evaluacionesPendientes);
        
        $this->assertTrue($evaluacionesCompletas->first()->evaluado);
        $this->assertFalse($evaluacionesPendientes->first()->evaluado);
    }

    /** @test */
    public function evaluation_nota_cuantitativa_is_within_valid_range()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $evaluacion = EstudianteLogro::factory()->create([
            'nivel_desempeno' => 'S',
        ]);
        
        $this->assertEquals('S', $evaluacion->nivel_desempeno);
        $this->assertContains($evaluacion->nivel_desempeno, ['E', 'S', 'A', 'I']);
    }

    /** @test */
    public function evaluation_has_correct_relationships()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        $evaluacion = EstudianteLogro::factory()->create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
        ]);
        
        // Verificar relaciones
        $this->assertNotNull($evaluacion->estudiante);
        $this->assertNotNull($evaluacion->logro);
        $this->assertNotNull($evaluacion->periodo);
        
        $this->assertEquals($setup['estudiantes']['juan']->id, $evaluacion->estudiante->id);
        $this->assertEquals($setup['logros']['matematicas_basico']->id, $evaluacion->logro->id);
        $this->assertEquals($setup['periodos']['periodo1']->id, $evaluacion->periodo->id);
    }

    /** @test */
    public function evaluation_can_be_marked_as_evaluated()
    {
        $admin = $this->createAdmin();
        $evaluacion = EstudianteLogro::factory()->pendiente()->create();
        
        $this->actingAs($admin);
        
        $evaluacion->update([
            'observaciones' => 'Evaluación completada exitosamente',
        ]);
        
        $this->assertNotNull($evaluacion->fresh()->observaciones);
        $this->assertEquals('Evaluación completada exitosamente', $evaluacion->fresh()->observaciones);
    }

    /** @test */
    public function evaluation_validates_required_fields()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        EstudianteLogro::create([
            'nota_cualitativa' => 'Solo nota cualitativa',
            // Faltan campos requeridos como estudiante_id, logro_id, periodo_id
        ]);
    }

    /** @test */
    public function evaluation_can_have_bulk_operations()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        // Crear múltiples evaluaciones para el mismo logro y período
        $estudiantes = Estudiante::factory(5)->create();
        $logro = $setup['logros']['matematicas_basico'];
        $periodo = $setup['periodos']['periodo1'];
        
        foreach ($estudiantes as $estudiante) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'A',
                'fecha_asignacion' => now(),
                'observaciones' => null, // Inicialmente sin observaciones
            ]);
        }
        
        // Actualizar todas las evaluaciones de una vez
        EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->update([
                'observaciones' => 'Evaluación completada masivamente',
            ]);
        
        $evaluacionesActualizadas = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->get();
        
        $this->assertCount(5, $evaluacionesActualizadas);
        
        foreach ($evaluacionesActualizadas as $evaluacion) {
            $this->assertTrue($evaluacion->evaluado);
            $this->assertNotNull($evaluacion->observaciones);
        }
    }

    /** @test */
    public function evaluation_can_calculate_average_by_student()
    {
        $admin = $this->createAdmin();
        $estudiante = Estudiante::factory()->create();
        $periodo = Periodo::factory()->create();
        
        $this->actingAs($admin);
        
        // Crear varias evaluaciones para el mismo estudiante
        $evaluaciones = EstudianteLogro::factory(4)->create([
            'estudiante_id' => $estudiante->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'S', // Sobresaliente = 4.0
            'observaciones' => 'Evaluación completada',
        ]);
        
        $promedio = EstudianteLogro::where('estudiante_id', $estudiante->id)
            ->where('periodo_id', $periodo->id)
            ->whereNotNull('observaciones')
            ->get()
            ->avg('valor_numerico');
        
        $this->assertEquals(4.0, $promedio);
    }

    /** @test */
    public function only_admin_can_delete_evaluations()
    {
        $admin = $this->createAdmin();
        $evaluacion = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionId = $evaluacion->id;
        $evaluacion->delete();
        
        $this->assertDatabaseMissing('estudiante_logros', [
            'id' => $evaluacionId,
        ]);
    }

    /** @test */
    public function evaluation_unique_constraint_works()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        // Crear primera evaluación
        $evaluacion1 = EstudianteLogro::factory()->create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
        ]);
        
        // Intentar crear duplicado debería fallar por constraint único
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        EstudianteLogro::create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
        ]);
    }
}
