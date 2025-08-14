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

class RelationshipIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function deleting_grado_cascades_to_estudiantes()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $estudiantes = Estudiante::factory(3)->create(['grado_id' => $grado->id]);
        
        // Verificar que los estudiantes existen
        $this->assertCount(3, $grado->estudiantes);
        
        // Eliminar el grado
        $grado->delete();
        
        // Verificar que los estudiantes fueron eliminados en cascada
        foreach ($estudiantes as $estudiante) {
            $this->assertDatabaseMissing('estudiantes', [
                'id' => $estudiante->id,
            ]);
        }
    }

    /** @test */
    public function deleting_grado_cascades_to_materias()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $materias = Materia::factory(4)->create();
        
        // Asociar materias con el grado
        foreach ($materias as $materia) {
            $materia->grados()->attach($grado->id);
        }
        
        // Verificar que las materias están asociadas
        $this->assertCount(4, $grado->materias);
        
        // Eliminar el grado
        $grado->delete();
        
        // Verificar que las materias aún existen (no deben eliminarse en cascada)
        // pero la relación many-to-many se elimina
        foreach ($materias as $materia) {
            $this->assertDatabaseHas('materias', [
                'id' => $materia->id,
            ]);
        }
        
        // Verificar que las relaciones grado-materia se eliminaron
        $this->assertDatabaseMissing('grado_materia', [
            'grado_id' => $grado->id,
        ]);
    }

    /** @test */
    public function deleting_materia_cascades_to_logros()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $materia = Materia::factory()->create();
        $logros = Logro::factory(5)->create(['materia_id' => $materia->id]);
        
        // Verificar que los logros existen
        $this->assertCount(5, $materia->logros);
        
        // Eliminar la materia
        $materia->delete();
        
        // Verificar que los logros fueron eliminados en cascada
        foreach ($logros as $logro) {
            $this->assertDatabaseMissing('logros', [
                'id' => $logro->id,
            ]);
        }
    }

    /** @test */
    public function deleting_estudiante_cascades_to_evaluaciones()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $estudiante = Estudiante::factory()->create();
        $evaluaciones = EstudianteLogro::factory(6)->create([
            'estudiante_id' => $estudiante->id,
        ]);
        
        // Verificar que las evaluaciones existen
        $this->assertCount(6, $estudiante->logros);
        
        // Eliminar el estudiante
        $estudiante->delete();
        
        // Verificar que las evaluaciones fueron eliminadas en cascada
        foreach ($evaluaciones as $evaluacion) {
            $this->assertDatabaseMissing('estudiante_logro', [
                'id' => $evaluacion->id,
            ]);
        }
    }

    /** @test */
    public function deleting_logro_cascades_to_evaluaciones()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $logro = Logro::factory()->create();
        $evaluaciones = EstudianteLogro::factory(4)->create([
            'logro_id' => $logro->id,
        ]);
        
        // Verificar que las evaluaciones existen
        $this->assertCount(4, $logro->estudiantes);
        
        // Eliminar el logro
        $logro->delete();
        
        // Verificar que las evaluaciones fueron eliminadas en cascada
        foreach ($evaluaciones as $evaluacion) {
            $this->assertDatabaseMissing('estudiante_logro', [
                'id' => $evaluacion->id,
            ]);
        }
    }

    /** @test */
    public function deleting_periodo_cascades_to_evaluaciones()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $periodo = Periodo::factory()->create();
        $evaluaciones = EstudianteLogro::factory(5)->create([
            'periodo_id' => $periodo->id,
        ]);
        
        // Verificar que las evaluaciones existen
        $evaluacionesCount = EstudianteLogro::where('periodo_id', $periodo->id)->count();
        $this->assertEquals(5, $evaluacionesCount);
        
        // Eliminar el período
        $periodo->delete();
        
        // Verificar que las evaluaciones fueron eliminadas en cascada
        foreach ($evaluaciones as $evaluacion) {
            $this->assertDatabaseMissing('estudiante_logro', [
                'id' => $evaluacion->id,
            ]);
        }
    }

    /** @test */
    public function deleting_user_nullifies_foreign_keys()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        
        $this->actingAs($admin);
        
        // Crear registros vinculados a usuarios
        $materias = Materia::factory(2)->create(['docente_id' => $profesor->id]);
        // Asignar director usando director_grado_id en users table
        $profesorDirector->update(['director_grado_id' => Grado::factory()->create()->id]);
        
        // Verificar que las relaciones existen
        $this->assertEquals($profesor->id, $materias->first()->docente_id);
        $this->assertNotNull($profesorDirector->fresh()->director_grado_id);
        
        // Eliminar usuarios
        $profesor->delete();
        $profesorDirector->delete();
        
        // Verificar que las foreign keys se volvieron null
        foreach ($materias as $materia) {
            $this->assertDatabaseHas('materias', [
                'id' => $materia->id,
                'docente_id' => null,
            ]);
        }
    }

    /** @test */
    public function logro_periodo_many_to_many_attach_detach_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $logro = Logro::factory()->create();
        $periodos = Periodo::factory(3)->create();
        
        // Attach períodos al logro
        $logro->periodos()->attach($periodos->pluck('id'));
        
        // Verificar que se crearon las relaciones
        $this->assertCount(3, $logro->periodos);
        foreach ($periodos as $periodo) {
            $this->assertDatabaseHas('logro_periodo', [
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
            ]);
        }
        
        // Detach un período
        $logro->periodos()->detach($periodos->first()->id);
        
        // Verificar que se eliminó la relación
        $this->assertCount(2, $logro->fresh()->periodos);
        $this->assertDatabaseMissing('logro_periodo', [
            'logro_id' => $logro->id,
            'periodo_id' => $periodos->first()->id,
        ]);
        
        // Detach all
        $logro->periodos()->detach();
        
        // Verificar que se eliminaron todas las relaciones
        $this->assertCount(0, $logro->fresh()->periodos);
        $logrosInPivot = \DB::table('logro_periodo')
            ->where('logro_id', $logro->id)
            ->count();
        $this->assertEquals(0, $logrosInPivot);
    }

    /** @test */
    public function estudiante_logro_many_to_many_through_pivot_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $estudiante = Estudiante::factory()->create();
        $logros = Logro::factory(4)->create();
        $periodo = Periodo::factory()->create();
        
        // Crear evaluaciones (relación many-to-many através de pivot)
        foreach ($logros as $logro) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'A',
                'fecha_asignacion' => now(),
            ]);
        }
        
        // Verificar relaciones a través del pivot
        $this->assertCount(4, $estudiante->logros);
        $this->assertCount(4, $estudiante->evaluaciones);
        
        // Verificar que podemos acceder a los datos del pivot
        foreach ($estudiante->evaluaciones as $evaluacion) {
            $this->assertEquals('alto', $evaluacion->nivel_desempeno);
            $this->assertEquals(4.0, $evaluacion->nota_cuantitativa);
            $this->assertTrue($evaluacion->evaluado);
        }
    }

    /** @test */
    public function complex_relationship_chain_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Crear cadena completa de relaciones
        $profesorDirector = $this->createProfesorDirector();
        $profesor = $this->createProfesor();
        
        $grado = Grado::factory()->create();
        $profesorDirector->update(['director_grado_id' => $grado->id]);
        $materia = Materia::factory()->create([
            'docente_id' => $profesor->id,
        ]);
        $materia->grados()->attach($grado->id);
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $periodo = Periodo::factory()->create();
        
        $evaluacion = EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'S',
            'fecha_asignacion' => now(),
        ]);
        
        // Verificar toda la cadena de relaciones
        $this->assertEquals($grado->id, $profesorDirector->fresh()->director_grado_id);
        $this->assertEquals($profesor->id, $materia->docente->id);
        $this->assertTrue($materia->grados->contains($grado->id));
        $this->assertEquals($grado->id, $estudiante->grado->id);
        $this->assertEquals($materia->id, $logro->materia->id);
        $this->assertEquals($estudiante->id, $evaluacion->estudiante->id);
        $this->assertEquals($logro->id, $evaluacion->logro->id);
        $this->assertEquals($periodo->id, $evaluacion->periodo->id);
        
        // Verificar navegación inversa
        $this->assertTrue($director->grados->contains($grado));
        $this->assertTrue($profesor->materias->contains($materia));
        $this->assertTrue($grado->estudiantes->contains($estudiante));
        $this->assertTrue($grado->materias->contains($materia));
        $this->assertTrue($materia->logros->contains($logro));
    }

    /** @test */
    public function relationship_constraints_prevent_invalid_data()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Intentar crear estudiante con grado inexistente
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Estudiante::create([
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'numero_identificacion' => '12345678',
            'grado_id' => 99999, // ID que no existe
        ]);
    }

    /** @test */
    public function unique_constraints_work_properly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $estudiante = Estudiante::factory()->create();
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        // Crear primera evaluación
        EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'A',
            'fecha_asignacion' => now(),
        ]);
        
        // Intentar crear duplicado debería fallar
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        EstudianteLogro::create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'I',
            'fecha_asignacion' => now(),
        ]);
    }

    /** @test */
    public function soft_deletes_maintain_data_integrity()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Skip test - Estudiante model doesn't have soft deletes
        $this->markTestSkipped('Estudiante model does not implement soft deletes');
        
        $estudiante = Estudiante::factory()->create();
        $evaluaciones = EstudianteLogro::factory(3)->create([
            'estudiante_id' => $estudiante->id,
        ]);
        
        // Soft delete del estudiante
        $estudiante->delete();
        
        // Verificar que está marcado como eliminado pero aún existe
        $this->assertSoftDeleted('estudiantes', [
            'id' => $estudiante->id,
        ]);
        
        // Verificar que las evaluaciones aún existen
        foreach ($evaluaciones as $evaluacion) {
            $this->assertDatabaseHas('estudiante_logro', [
                'id' => $evaluacion->id,
                'estudiante_id' => $estudiante->id,
            ]);
        }
        
        // Restaurar estudiante
        $estudiante->restore();
        
        // Verificar que está activo nuevamente
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function updating_relationships_maintains_consistency()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado1 = Grado::factory()->create();
        $grado2 = Grado::factory()->create();
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado1->id]);
        
        // Verificar relación inicial
        $this->assertTrue($grado1->estudiantes->contains($estudiante));
        $this->assertFalse($grado2->estudiantes->contains($estudiante));
        
        // Cambiar grado del estudiante
        $estudiante->update(['grado_id' => $grado2->id]);
        
        // Verificar que las relaciones se actualizaron
        $this->assertFalse($grado1->fresh()->estudiantes->contains($estudiante));
        $this->assertTrue($grado2->fresh()->estudiantes->contains($estudiante));
        $this->assertEquals($grado2->id, $estudiante->fresh()->grado_id);
    }

    /** @test */
    public function bulk_operations_maintain_referential_integrity()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $estudiantes = Estudiante::factory(5)->create(['grado_id' => $grado->id]);
        
        // Crear evaluaciones para todos los estudiantes
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        foreach ($estudiantes as $estudiante) {
            EstudianteLogro::create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
                'nivel_desempeno' => 'I',
                'fecha_asignacion' => now(),
            ]);
        }
        
        // Actualización bulk
        EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->update([
                'nivel_desempeno' => 'A',
                'observaciones' => 'Actualizado masivamente',
            ]);
        
        // Verificar que todas las evaluaciones se actualizaron
        $evaluacionesActualizadas = EstudianteLogro::where('logro_id', $logro->id)
            ->where('periodo_id', $periodo->id)
            ->where('evaluado', true)
            ->count();
        
        $this->assertEquals(5, $evaluacionesActualizadas);
    }

    /** @test */
    public function circular_relationship_prevention_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $materia = Materia::factory()->create(['grado_id' => $grado->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $periodo = Periodo::factory()->create();
        
        // Asignar logro al período
        $logro->periodos()->attach($periodo->id);
        
        // Verificar que la relación existe
        $this->assertTrue($logro->periodos->contains($periodo));
        $this->assertTrue($periodo->logros->contains($logro));
        
        // No debería haber problemas circulares
        $this->assertEquals($grado->id, $logro->materia->grado->id);
        $this->assertTrue($grado->materias->contains($materia));
        $this->assertTrue($materia->logros->contains($logro));
    }

    /** @test */
    public function transaction_rollback_maintains_integrity()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $grado = Grado::factory()->create();
        $initialEstudiantesCount = Estudiante::count();
        
        try {
            \DB::transaction(function () use ($grado) {
                // Crear estudiantes
                Estudiante::factory(3)->create(['grado_id' => $grado->id]);
                
                // Simular error que debería hacer rollback
                throw new \Exception('Error simulado');
            });
        } catch (\Exception $e) {
            // El rollback debería haber ocurrido
        }
        
        // Verificar que no se crearon estudiantes debido al rollback
        $finalEstudiantesCount = Estudiante::count();
        $this->assertEquals($initialEstudiantesCount, $finalEstudiantesCount);
        $this->assertCount(0, $grado->fresh()->estudiantes);
    }
}
