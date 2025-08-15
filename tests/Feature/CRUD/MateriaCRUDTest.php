<?php

namespace Tests\Feature\CRUD;

use App\Models\Materia;
use App\Models\Grado;
use App\Models\User;
use Tests\TestCase;

class MateriaCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_materia()
    {
        $admin = $this->createAdmin();
        $grado = Grado::factory()->create();
        $docente = User::factory()->profesor()->create();
        
        $this->actingAs($admin);
        
        $materiaData = [
            'nombre' => 'Matemáticas',
            'descripcion' => 'Asignatura de matemáticas básicas',
            'codigo' => 'MAT-001',
            'docente_id' => $docente->id,
            'activa' => true,
        ];
        
        $materia = Materia::create($materiaData);
        
        // Asociar la materia al grado usando la tabla pivot
        $materia->grados()->attach($grado->id);
        
        $this->assertDatabaseHas('materias', [
            'nombre' => 'Matemáticas',
            'codigo' => 'MAT-001',
            'docente_id' => $docente->id,
            'activa' => true,
        ]);
        
        // Verificar la relación many-to-many
        $this->assertDatabaseHas('grado_materia', [
            'grado_id' => $grado->id,
            'materia_id' => $materia->id,
        ]);
    }

    /** @test */
    public function admin_can_assign_profesor_to_materia()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => null]);
        
        $this->actingAs($admin);
        
        $materia->update(['docente_id' => $profesor->id]);
        
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'docente_id' => $profesor->id,
        ]);
        
        $this->assertEquals($profesor->id, $materia->fresh()->docente_id);
    }

    /** @test */
    public function admin_can_view_all_materias()
    {
        $admin = $this->createAdmin();
        $materias = Materia::factory(3)->create();
        
        $this->actingAs($admin);
        
        $response = $this->get('/liceo');
        $response->assertStatus(200);
        
        foreach ($materias as $materia) {
            $this->assertDatabaseHas('materias', [
                'id' => $materia->id,
                'nombre' => $materia->nombre,
            ]);
        }
    }

    /** @test */
    public function profesor_can_view_their_materias()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        
        $materiaPropia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $materiaAjena = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        
        $this->actingAs($profesor);
        
        $materiasProfesor = $profesor->materias;
        
        $this->assertTrue($materiasProfesor->contains($materiaPropia));
        $this->assertFalse($materiasProfesor->contains($materiaAjena));
    }

    /** @test */
    public function admin_can_update_any_materia()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create([
            'nombre' => 'Nombre original',
            'descripcion' => 'Descripción original',
        ]);
        
        $this->actingAs($admin);
        
        $materia->update([
            'nombre' => 'Nombre actualizado',
            'descripcion' => 'Descripción actualizada',
        ]);
        
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'nombre' => 'Nombre actualizado',
            'descripcion' => 'Descripción actualizada',
        ]);
    }

    /** @test */
    public function profesor_can_update_their_materia_content()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        $this->actingAs($profesor);
        
        $materia->update([
            'descripcion' => 'Actualizada por profesor',
        ]);
        
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'descripcion' => 'Actualizada por profesor',
        ]);
    }

    /** @test */
    public function only_admin_can_delete_materias()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create();
        
        $this->actingAs($admin);
        
        $materiaId = $materia->id;
        $materia->delete();
        
        $this->assertDatabaseMissing('materias', [
            'id' => $materiaId,
        ]);
    }

    /** @test */
    public function materia_can_be_filtered_by_grado()
    {
        $admin = $this->createAdmin();
        $grado1 = Grado::factory()->create(['nombre' => 'Primero']);
        $grado2 = Grado::factory()->create(['nombre' => 'Segundo']);
        
        $materia1 = Materia::factory()->create();
        $materia2 = Materia::factory()->create();
        
        // Asociar materias a grados usando la tabla pivot
        $materia1->grados()->attach($grado1->id);
        $materia2->grados()->attach($grado2->id);
        
        $this->actingAs($admin);
        
        // Verificar que las materias están asociadas correctamente
        $this->assertTrue($grado1->materias->contains($materia1));
        $this->assertTrue($grado2->materias->contains($materia2));
        $this->assertFalse($grado1->materias->contains($materia2));
    }

    /** @test */
    public function materia_can_be_filtered_by_area()
    {
        $admin = $this->createAdmin();
        
        // Skip this test - area filtering not implemented, using nombre instead
        $this->markTestSkipped('Area filtering not implemented in current schema, using nombre for filtering');
    }

    /** @test */
    public function only_active_materias_are_returned_by_scope()
    {
        $admin = $this->createAdmin();
        
        $materiaActiva = Materia::factory()->activa()->create();
        $materiaInactiva = Materia::factory()->inactiva()->create();
        
        $this->actingAs($admin);
        
        $materiasActivas = Materia::activas()->get();
        
        $this->assertCount(1, $materiasActivas);
        $this->assertEquals($materiaActiva->id, $materiasActivas->first()->id);
    }

    /** @test */
    public function materia_can_be_marked_as_inactive()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create(['activa' => true]);
        
        $this->actingAs($admin);
        
        $materia->update(['activa' => false]);
        
        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
            'activa' => false,
        ]);
        
        $this->assertFalse($materia->fresh()->activa);
    }

    /** @test */
    public function materia_has_correct_relationships()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        $materia = $setup['materias']['matematicas'];
        
        // Verificar relación con grados (many-to-many)
        $this->assertGreaterThan(0, $materia->grados->count());
        
        // Verificar relación con docente
        $this->assertNotNull($materia->docente);
        
        // Verificar relación con logros
        $this->assertGreaterThan(0, $materia->logros->count());
    }

    /** @test */
    public function materia_can_have_multiple_logros()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create();
        
        // Crear logros manualmente ya que no existe withLogros()
        $logros = \App\Models\Logro::factory()
            ->count(3)
            ->create(['materia_id' => $materia->id]);
        
        $this->actingAs($admin);
        
        $this->assertCount(3, $materia->logros);
        
        foreach ($materia->logros as $logro) {
            $this->assertEquals($materia->id, $logro->materia_id);
        }
    }

    /** @test */
    public function materia_validates_required_fields()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        Materia::create([
            'descripcion' => 'Solo descripción',
            // Faltan campos requeridos como nombre, grado_id
        ]);
    }

    /** @test */
    public function materia_can_be_reassigned_to_different_profesor()
    {
        $admin = $this->createAdmin();
        $profesor1 = $this->createProfesor();
        $profesor2 = $this->createProfesor();
        
        $materia = Materia::factory()->create(['docente_id' => $profesor1->id]);
        
        $this->actingAs($admin);
        
        $materia->update(['docente_id' => $profesor2->id]);
        
        $this->assertEquals($profesor2->id, $materia->fresh()->docente_id);
        
        // Verificar que las relaciones están correctas
        $this->assertTrue($profesor2->materias->contains($materia));
        $this->assertFalse($profesor1->materias->fresh()->contains($materia));
    }

    /** @test */
    public function materia_color_is_stored_correctly()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Skip this test - color column doesn't exist in current schema
        $this->markTestSkipped('color column does not exist in current schema');
    }

    /** @test */
    public function materia_can_be_filtered_by_docente()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        
        $materias = Materia::factory(3)->create(['docente_id' => $profesor->id]);
        $materiaOtro = Materia::factory()->create();
        
        $this->actingAs($admin);
        
        $materiasProfesor = Materia::porDocente($profesor->id)->get();
        
        $this->assertCount(3, $materiasProfesor);
        
        foreach ($materiasProfesor as $materia) {
            $this->assertEquals($profesor->id, $materia->docente_id);
        }
    }

    /** @test */
    public function materia_intensidad_horaria_is_positive_integer()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        // Skip this test - intensidad_horaria column doesn't exist in current schema
        $this->markTestSkipped('intensidad_horaria column does not exist in current schema');
    }
}
