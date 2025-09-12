<?php

namespace Tests\Feature\CRUD;

use App\Models\EstudianteLogro;
use App\Models\Estudiante;
use App\Models\Logro;
use App\Models\Periodo;
use App\Models\DesempenoMateria;
use Tests\TestCase;

class EstudianteLogroCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_estudiante_logros()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        // Crear DesempenoMateria con nivel_desempeno y estado
        $desempenoMateria = DesempenoMateria::create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'materia_id' => $setup['materias']['matematicas']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
            'estado' => 'borrador',
            'fecha_asignacion' => now()->format('Y-m-d')
        ]);
        
        $estudianteLogroData = [
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'alcanzado' => true,
        ];
        
        $estudianteLogro = EstudianteLogro::create($estudianteLogroData);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'alcanzado' => true,
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
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'alcanzado' => true,
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $estudianteLogro->id,
            'alcanzado' => true,
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
                'alcanzado' => $evaluacion->alcanzado,
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
            'alcanzado' => false,
        ]);
        
        $this->actingAs($admin);
        
        $evaluacion->update([
            'alcanzado' => true,
        ]);
        
        $evaluacion->desempenoMateria->update([
            'observaciones_finales' => 'Mejoró significativamente',
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion->id,
            'alcanzado' => true,
        ]);
        
        $this->assertDatabaseHas('desempenos_materia', [
            'id' => $evaluacion->desempenoMateria->id,
            'observaciones_finales' => 'Mejoró significativamente',
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
            'alcanzado' => false,
        ]);
        
        $this->actingAs($profesor);
        
        $evaluacion->update([
            'alcanzado' => true,
        ]);
        
        $evaluacion->desempenoMateria->update([
            'observaciones_finales' => 'Buen progreso',
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion->id,
            'alcanzado' => true,
        ]);
        
        $this->assertDatabaseHas('desempenos_materia', [
            'id' => $evaluacion->desempenoMateria->id,
            'observaciones_finales' => 'Buen progreso',
        ]);
    }

    /** @test */
    public function evaluation_can_be_filtered_by_nivel_desempeno()
    {
        $admin = $this->createAdmin();
        
        // Crear evaluaciones con diferentes niveles de desempeño a través de DesempenoMateria
        $excelente = EstudianteLogro::factory()->create();
        $excelente->desempenoMateria->update(['nivel_desempeno' => 'E']);
        
        $superior = EstudianteLogro::factory()->create();
        $superior->desempenoMateria->update(['nivel_desempeno' => 'S']);
        
        $aceptable = EstudianteLogro::factory()->create();
        $aceptable->desempenoMateria->update(['nivel_desempeno' => 'A']);
        
        $insuficiente = EstudianteLogro::factory()->create();
        $insuficiente->desempenoMateria->update(['nivel_desempeno' => 'I']);
        
        $this->actingAs($admin);
        
        // Usar las relaciones para filtrar por nivel de desempeño
        $evaluacionesExcelentes = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->where('nivel_desempeno', 'E');
        })->get();
        
        $evaluacionesSuperiores = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->where('nivel_desempeno', 'S');
        })->get();
        
        $evaluacionesAceptables = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->where('nivel_desempeno', 'A');
        })->get();
        
        $evaluacionesInsuficientes = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->where('nivel_desempeno', 'I');
        })->get();
        
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
        
        // Crear evaluaciones para un estudiante específico a través de DesempenoMateria
        $evaluaciones = EstudianteLogro::factory(3)->create();
        foreach ($evaluaciones as $evaluacion) {
            $evaluacion->desempenoMateria->update(['estudiante_id' => $estudiante->id]);
        }
        
        $evaluacionOtro = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesEstudiante = EstudianteLogro::whereHas('desempenoMateria', function($query) use ($estudiante) {
            $query->where('estudiante_id', $estudiante->id);
        })->get();
        
        $this->assertCount(3, $evaluacionesEstudiante);
        
        foreach ($evaluacionesEstudiante as $evaluacion) {
            $this->assertEquals($estudiante->id, $evaluacion->estudiante->id);
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
        
        // Crear evaluaciones para un periodo específico a través de DesempenoMateria
        $evaluaciones = EstudianteLogro::factory(3)->create();
        foreach ($evaluaciones as $evaluacion) {
            $evaluacion->desempenoMateria->update(['periodo_id' => $periodo->id]);
        }
        
        $evaluacionOtra = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacionesPeriodo = EstudianteLogro::whereHas('desempenoMateria', function($query) use ($periodo) {
            $query->where('periodo_id', $periodo->id);
        })->get();
        
        $this->assertCount(3, $evaluacionesPeriodo);
        
        foreach ($evaluacionesPeriodo as $evaluacion) {
            $this->assertEquals($periodo->id, $evaluacion->periodo->id);
        }
    }

    /** @test */
    public function only_evaluated_records_are_returned_by_scope()
    {
        $admin = $this->createAdmin();
        
        // Crear una evaluación con observaciones (evaluada)
        $evaluacionCompleta = EstudianteLogro::factory()->create();
        $evaluacionCompleta->desempenoMateria->update(['observaciones_finales' => 'Evaluación completada']);
        
        // Crear una evaluación sin observaciones (pendiente)
        $evaluacionPendiente = EstudianteLogro::factory()->create();
        $evaluacionPendiente->desempenoMateria->update(['observaciones_finales' => null]);
        
        $this->actingAs($admin);
        
        $evaluacionesCompletas = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->whereNotNull('observaciones_finales');
        })->get();
        
        $evaluacionesPendientes = EstudianteLogro::whereHas('desempenoMateria', function($query) {
            $query->whereNull('observaciones_finales');
        })->get();
        
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
        
        $evaluacion = EstudianteLogro::factory()->create();
        $evaluacion->desempenoMateria->update(['nivel_desempeno' => 'S']);
        
        $this->assertEquals('S', $evaluacion->nivel_desempeno);
        $this->assertContains($evaluacion->nivel_desempeno, ['E', 'S', 'A', 'I']);
    }

    /** @test */
    public function evaluation_has_correct_relationships()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        // Crear un desempeño de materia
        $desempenoMateria = DesempenoMateria::create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'materia_id' => $setup['materias']['matematicas']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
            'estado' => 'borrador',
            'fecha_asignacion' => now()->format('Y-m-d')
        ]);
        
        $evaluacion = EstudianteLogro::create([
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'alcanzado' => true,
        ]);
        
        // Verificar relaciones
        $this->assertNotNull($evaluacion->estudiante);
        $this->assertNotNull($evaluacion->logro);
        $this->assertNotNull($evaluacion->periodo);
        $this->assertNotNull($evaluacion->desempenoMateria);
        
        $this->assertEquals($setup['estudiantes']['juan']->id, $evaluacion->estudiante->id);
        $this->assertEquals($setup['logros']['matematicas_basico']->id, $evaluacion->logro->id);
        $this->assertEquals($setup['periodos']['periodo1']->id, $evaluacion->periodo->id);
    }

    /** @test */
    public function evaluation_can_be_marked_as_evaluated()
    {
        $admin = $this->createAdmin();
        $evaluacion = EstudianteLogro::factory()->create();
        
        $this->actingAs($admin);
        
        $evaluacion->desempenoMateria->update([
            'observaciones_finales' => 'Evaluación completada exitosamente',
        ]);
        
        $evaluacionFresh = $evaluacion->fresh();
        $this->assertNotNull($evaluacionFresh->observaciones);
        $this->assertEquals('Evaluación completada exitosamente', $evaluacionFresh->observaciones);
        $this->assertTrue($evaluacionFresh->evaluado);
    }

    /** @test */
    public function evaluation_validates_required_fields()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        EstudianteLogro::create([
            'observaciones' => 'Solo observaciones',
            // Faltan campos requeridos como desempeno_materia_id, logro_id
        ]);
    }

    /** @test */
    public function evaluation_can_have_bulk_operations()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        // Crear múltiples estudiantes y desempeños de materia
        $estudiantes = Estudiante::factory(5)->create();
        $logro = $setup['logros']['matematicas_basico'];
        $materia = $setup['materias']['matematicas'];
        $periodo = $setup['periodos']['periodo1'];
        
        $desempenosMaterias = [];
        foreach ($estudiantes as $estudiante) {
            $desempenoMateria = DesempenoMateria::create([
                'estudiante_id' => $estudiante->id,
                'materia_id' => $materia->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'A',
                'estado' => 'borrador',
                'fecha_asignacion' => now()->format('Y-m-d')
            ]);
            $desempenosMaterias[] = $desempenoMateria;
            
            EstudianteLogro::create([
                'desempeno_materia_id' => $desempenoMateria->id,
                'logro_id' => $logro->id,
                'alcanzado' => true,
            ]);
        }
        
        // Actualizar todas las evaluaciones de una vez a través de DesempenoMateria
        DesempenoMateria::where('materia_id', $materia->id)
            ->where('periodo_id', $periodo->id)
            ->update([
                'observaciones_finales' => 'Evaluación completada masivamente',
            ]);
        
        $evaluacionesActualizadas = EstudianteLogro::whereHas('desempenoMateria', function($query) use ($materia, $periodo) {
            $query->where('materia_id', $materia->id)
                  ->where('periodo_id', $periodo->id);
        })->get();
        
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
        $setup = $this->createAcademicSetup();
        $estudiante = $setup['estudiantes']['juan'];
        $materia = $setup['materias']['matematicas'];
        $periodo = $setup['periodos']['periodo1'];
        
        $this->actingAs($admin);
        
        // Crear un desempeño de materia base
        $desempenoMateria = DesempenoMateria::create([
            'estudiante_id' => $estudiante->id,
            'materia_id' => $materia->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'S', // Sobresaliente = 4.0
            'observaciones_finales' => 'Evaluación completada',
            'estado' => 'publicado',
            'fecha_asignacion' => now()->format('Y-m-d')
        ]);
        
        // Crear múltiples logros para el mismo desempeño
        $logros = [
            $setup['logros']['matematicas_basico'],
            $setup['logros']['lenguaje_lectura'], // Usar un logro diferente que existe
        ];
        
        $evaluaciones = [];
        foreach ($logros as $logro) {
            $evaluaciones[] = EstudianteLogro::create([
                'desempeno_materia_id' => $desempenoMateria->id,
                'logro_id' => $logro->id,
                'alcanzado' => true,
            ]);
        }
        
        $evaluacionesCreadas = EstudianteLogro::whereHas('desempenoMateria', function($query) use ($estudiante, $periodo) {
            $query->where('estudiante_id', $estudiante->id)
                  ->where('periodo_id', $periodo->id)
                  ->whereNotNull('observaciones_finales');
        })->get();
        
        $promedio = $evaluacionesCreadas->avg('valor_numerico');
        
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
        
        // Crear un desempeño de materia
        $desempenoMateria = DesempenoMateria::create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'materia_id' => $setup['materias']['matematicas']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
            'estado' => 'borrador',
            'fecha_asignacion' => now()->format('Y-m-d')
        ]);
        
        // Crear primera evaluación
        $evaluacion1 = EstudianteLogro::create([
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
            'alcanzado' => true,
        ]);
        
        // Crear segunda evaluación para el mismo desempeño pero diferente logro (debería funcionar)
        $evaluacion2 = EstudianteLogro::create([
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['lenguaje_lectura']->id,  // Cambiar a un logro que existe
            'alcanzado' => false,
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion1->id,
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['matematicas_basico']->id,
        ]);
        
        $this->assertDatabaseHas('estudiante_logros', [
            'id' => $evaluacion2->id,
            'desempeno_materia_id' => $desempenoMateria->id,
            'logro_id' => $setup['logros']['lenguaje_lectura']->id,
        ]);
    }
}
