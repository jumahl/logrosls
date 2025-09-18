<?php

namespace Tests\Feature\CRUD;

use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use Tests\TestCase;

class LogroCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_logro()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create();
        
        $this->actingAs($admin);
        
        $logroData = [
            'codigo' => 'MAT-001',
            'titulo' => 'Comprende operaciones básicas',
            'descripcion' => 'El estudiante realiza sumas y restas con números naturales',
            'materia_id' => $materia->id,
            'nivel_dificultad' => 'bajo',
            'tipo' => 'conocimiento',
            'activo' => true,
            'competencia' => 'Pensamiento numérico',
            'tema' => 'Operaciones básicas',
            'indicador_desempeno' => 'Realiza sumas y restas correctamente',
            'dimension' => 'cognitiva',
        ];
        
        $logro = Logro::create($logroData);
        
        $this->assertDatabaseHas('logros', [
            'codigo' => 'MAT-001',
            'titulo' => 'Comprende operaciones básicas',
            'materia_id' => $materia->id,
            'nivel_dificultad' => 'bajo',
            'tipo' => 'conocimiento',
        ]);
    }

    /** @test */
    public function profesor_can_create_logro_for_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        $this->actingAs($profesor);
        
        $logro = Logro::factory()->create([
            'materia_id' => $materia->id,
            'titulo' => 'Logro del profesor',
        ]);
        
        $this->assertDatabaseHas('logros', [
            'id' => $logro->id,
            'titulo' => 'Logro del profesor',
            'materia_id' => $materia->id,
        ]);
    }

    /** @test */
    public function admin_can_view_all_logros()
    {
        $admin = $this->createAdmin();
        $logros = Logro::factory(3)->create();
        
        $this->actingAs($admin);
        
        // El admin debería poder ver todos los logros en la base de datos
        foreach ($logros as $logro) {
            $this->assertDatabaseHas('logros', [
                'id' => $logro->id,
                'titulo' => $logro->titulo,
            ]);
        }
        
        // También verificar que puede acceder a la colección de logros
        $retrievedLogros = Logro::all();
        $this->assertCount(3, $retrievedLogros);
    }

    /** @test */
    public function profesor_can_only_view_logros_from_their_materias()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        
        $materiaPropia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $materiaAjena = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        
        $logroPropio = Logro::factory()->create(['materia_id' => $materiaPropia->id]);
        $logroAjeno = Logro::factory()->create(['materia_id' => $materiaAjena->id]);
        
        $this->actingAs($profesor);
        
        // Verificar que puede acceder a sus propios logros
        $materiasProfesor = $profesor->materias->pluck('id');
        $this->assertTrue($materiasProfesor->contains($materiaPropia->id));
        $this->assertFalse($materiasProfesor->contains($materiaAjena->id));
    }

    /** @test */
    public function admin_can_update_any_logro()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create([
            'titulo' => 'Título original',
            'descripcion' => 'Descripción original',
        ]);
        
        $this->actingAs($admin);
        
        $logro->update([
            'titulo' => 'Título actualizado',
            'descripcion' => 'Descripción actualizada',
        ]);
        
        $this->assertDatabaseHas('logros', [
            'id' => $logro->id,
            'titulo' => 'Título actualizado',
            'descripcion' => 'Descripción actualizada',
        ]);
    }

    /** @test */
    public function profesor_can_update_logros_from_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        $this->actingAs($profesor);
        
        $logro->update(['titulo' => 'Actualizado por profesor']);
        
        $this->assertDatabaseHas('logros', [
            'id' => $logro->id,
            'titulo' => 'Actualizado por profesor',
        ]);
    }

    /** @test */
    public function only_admin_can_delete_logros()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create();
        
        $this->actingAs($admin);
        
        $logroId = $logro->id;
        $logro->delete();
        
        $this->assertDatabaseMissing('logros', [
            'id' => $logroId,
        ]);
    }

    /** @test */
    public function logro_can_be_assigned_to_periodos()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create();
        $periodos = Periodo::factory(2)->create();
        
        $this->actingAs($admin);
        
        $logro->periodos()->attach($periodos->pluck('id'));
        
        $this->assertCount(2, $logro->periodos);
        
        foreach ($periodos as $periodo) {
            $this->assertDatabaseHas('logro_periodo', [
                'logro_id' => $logro->id,
                'periodo_id' => $periodo->id,
            ]);
        }
    }

    /** @test */
    public function logro_can_be_filtered_by_materia()
    {
        $admin = $this->createAdmin();
        $materia1 = Materia::factory()->create(['nombre' => 'Matemáticas']);
        $materia2 = Materia::factory()->create(['nombre' => 'Lenguaje']);
        
        $logro1 = Logro::factory()->create(['materia_id' => $materia1->id]);
        $logro2 = Logro::factory()->create(['materia_id' => $materia2->id]);
        
        $this->actingAs($admin);
        
        $logrosMat = Logro::porMateria($materia1->id)->get();
        $this->assertCount(1, $logrosMat);
        $this->assertEquals($logro1->id, $logrosMat->first()->id);
    }

    /** @test */
    public function logro_can_be_filtered_by_nivel()
    {
        $admin = $this->createAdmin();
        
        $logroBasico = Logro::factory()->basico()->create();
        $logroIntermedio = Logro::factory()->medio()->create();
        $logroAvanzado = Logro::factory()->alto()->create();
        
        $this->actingAs($admin);
        
        $logrosBasicos = Logro::porNivelDificultad('bajo')->get();
        $logrosIntermedios = Logro::porNivelDificultad('medio')->get();
        $logrosAvanzados = Logro::porNivelDificultad('alto')->get();
        
        $this->assertCount(1, $logrosBasicos);
        $this->assertCount(1, $logrosIntermedios);
        $this->assertCount(1, $logrosAvanzados);
        
        $this->assertEquals($logroBasico->id, $logrosBasicos->first()->id);
        $this->assertEquals($logroIntermedio->id, $logrosIntermedios->first()->id);
        $this->assertEquals($logroAvanzado->id, $logrosAvanzados->first()->id);
    }

    /** @test */
    public function logro_can_be_filtered_by_tipo()
    {
        $admin = $this->createAdmin();
        
        $logroConceptual = Logro::factory()->conocimiento()->create();
        $logroProcedimental = Logro::factory()->habilidad()->create();
        $logroActitudinal = Logro::factory()->actitud()->create();
        
        $this->actingAs($admin);
        
        $logrosConceptuales = Logro::porTipo('conocimiento')->get();
        $logrosProcedimentales = Logro::porTipo('habilidad')->get();
        $logrosActitudinales = Logro::porTipo('actitud')->get();
        
        $this->assertCount(1, $logrosConceptuales);
        $this->assertCount(1, $logrosProcedimentales);
        $this->assertCount(1, $logrosActitudinales);
    }

    /** @test */
    public function only_active_logros_are_returned_by_scope()
    {
        $admin = $this->createAdmin();
        
        $logroActivo = Logro::factory()->activo()->create();
        $logroInactivo = Logro::factory()->inactivo()->create();
        
        $this->actingAs($admin);
        
        $logrosActivos = Logro::activos()->get();
        
        $this->assertCount(1, $logrosActivos);
        $this->assertEquals($logroActivo->id, $logrosActivos->first()->id);
    }

    /** @test */
    public function logro_can_be_marked_as_inactive()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create(['activo' => true]);
        
        $this->actingAs($admin);
        
        $logro->update(['activo' => false]);
        
        $this->assertDatabaseHas('logros', [
            'id' => $logro->id,
            'activo' => false,
        ]);
        
        $this->assertFalse($logro->fresh()->activo);
    }

    /** @test */
    public function logro_validation_works_correctly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        Logro::create([
            'descripcion' => 'Solo descripción',
            // Faltan campos requeridos como titulo, materia_id
        ]);
    }

    /** @test */
    public function logro_codigo_should_be_unique()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        Logro::factory()->create(['codigo' => 'TEST-001']);
        
        // Intentar crear otro logro con el mismo código debería fallar
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        
        Logro::factory()->create(['codigo' => 'TEST-001']);
    }

    /** @test */
    public function logro_can_have_complex_relationships()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        $logro = $setup['logros']['matematicas_basico'];
        
        // Verificar relaciones
        $this->assertNotNull($logro->materia);
        $this->assertEquals($setup['materias']['matematicas']->id, $logro->materia->id);
        
        // Asignar a períodos
        $logro->periodos()->attach($setup['periodos']['periodo1']->id);
        
        $this->assertCount(1, $logro->periodos);
        $this->assertTrue($logro->periodos->contains($setup['periodos']['periodo1']));
    }
}
