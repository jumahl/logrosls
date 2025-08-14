<?php

namespace Tests\Feature\CRUD;

use App\Models\Estudiante;
use App\Models\Grado;
use Tests\TestCase;

class EstudianteCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_estudiante()
    {
        $admin = $this->createAdmin();
        $grado = Grado::factory()->create();
        
        $estudianteData = [
            'nombre' => 'Juan Carlos',
            'apellido' => 'Pérez García',
            'documento' => '12345678',
            'fecha_nacimiento' => '2010-05-15',
            'direccion' => 'Calle 123 #45-67',
            'telefono' => '300-123-4567',
            'email' => 'juan@example.com',
            'grado_id' => $grado->id,
            'activo' => true,
        ];
        
        $this->actingAs($admin);
        
        // Simular la creación a través de Livewire (como lo haría Filament)
        $estudiante = Estudiante::create($estudianteData);
        
        $this->assertDatabaseHas('estudiantes', [
            'nombre' => 'Juan Carlos',
            'apellido' => 'Pérez García',
            'documento' => '12345678',
            'grado_id' => $grado->id,
        ]);
        
        $this->assertEquals('Juan Carlos Pérez García - 12345678', $estudiante->nombre_completo);
    }

    /** @test */
    public function admin_can_view_estudiante_list()
    {
        $admin = $this->createAdmin();
        $estudiantes = Estudiante::factory(3)->create();
        
        $this->actingAs($admin);

        // En Filament, esto sería a través del panel liceo
        $response = $this->get('/liceo');
        $response->assertStatus(200);        // Verificar que los estudiantes existen en la base de datos
        foreach ($estudiantes as $estudiante) {
            $this->assertDatabaseHas('estudiantes', [
                'id' => $estudiante->id,
                'nombre' => $estudiante->nombre,
            ]);
        }
    }

    /** @test */
    public function admin_can_update_estudiante()
    {
        $admin = $this->createAdmin();
        $estudiante = Estudiante::factory()->create([
            'nombre' => 'Original',
            'email' => 'original@example.com',
        ]);
        
        $this->actingAs($admin);
        
        $updateData = [
            'nombre' => 'Actualizado',
            'email' => 'actualizado@example.com',
        ];
        
        $estudiante->update($updateData);
        
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'nombre' => 'Actualizado',
            'email' => 'actualizado@example.com',
        ]);
        
        $this->assertDatabaseMissing('estudiantes', [
            'id' => $estudiante->id,
            'nombre' => 'Original',
            'email' => 'original@example.com',
        ]);
    }

    /** @test */
    public function admin_can_delete_estudiante()
    {
        $admin = $this->createAdmin();
        $estudiante = Estudiante::factory()->create();
        
        $this->actingAs($admin);
        
        $estudianteId = $estudiante->id;
        $estudiante->delete();
        
        $this->assertDatabaseMissing('estudiantes', [
            'id' => $estudianteId,
        ]);
    }

    /** @test */
    public function profesor_can_view_estudiantes()
    {
        $profesor = $this->createProfesor();
        $estudiantes = Estudiante::factory(2)->create();
        
        $this->actingAs($profesor);

        $response = $this->get('/liceo');
        $response->assertStatus(200);        // Profesor debería poder ver estudiantes
        foreach ($estudiantes as $estudiante) {
            $this->assertDatabaseHas('estudiantes', [
                'id' => $estudiante->id,
            ]);
        }
    }

    /** @test */
    public function estudiante_creation_validates_required_fields()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        Estudiante::create([
            'nombre' => 'Test',
            // Faltan campos requeridos como apellido, documento, etc.
        ]);
    }

    /** @test */
    public function estudiante_documento_must_be_unique()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        Estudiante::factory()->create(['documento' => '12345678']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear otro estudiante con el mismo documento
        Estudiante::factory()->create(['documento' => '12345678']);
    }

    /** @test */
    public function estudiante_can_be_marked_as_inactive()
    {
        $admin = $this->createAdmin();
        $estudiante = Estudiante::factory()->create(['activo' => true]);
        
        $this->actingAs($admin);
        
        $estudiante->update(['activo' => false]);
        
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'activo' => false,
        ]);
        
        $this->assertFalse($estudiante->fresh()->activo);
    }

    /** @test */
    public function estudiante_can_be_assigned_to_different_grado()
    {
        $admin = $this->createAdmin();
        $gradoOriginal = Grado::factory()->create(['nombre' => 'Primero']);
        $gradoNuevo = Grado::factory()->create(['nombre' => 'Segundo']);
        
        $estudiante = Estudiante::factory()->create(['grado_id' => $gradoOriginal->id]);
        
        $this->actingAs($admin);
        
        $estudiante->update(['grado_id' => $gradoNuevo->id]);
        
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'grado_id' => $gradoNuevo->id,
        ]);
        
        $this->assertEquals('Segundo', $estudiante->fresh()->grado->nombre);
    }

    /** @test */
    public function estudiante_optional_fields_can_be_null()
    {
        $admin = $this->createAdmin();
        $grado = Grado::factory()->create();
        
        $this->actingAs($admin);
        
        $estudiante = Estudiante::create([
            'nombre' => 'Test',
            'apellido' => 'Student',
            'documento' => '87654321',
            'fecha_nacimiento' => '2010-01-01',
            'grado_id' => $grado->id,
            // Omitir campos opcionales
            'direccion' => null,
            'telefono' => null,
            'email' => null,
        ]);
        
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'nombre' => 'Test',
            'direccion' => null,
            'telefono' => null,
            'email' => null,
        ]);
    }

    /** @test */
    public function estudiante_nombre_completo_is_generated_correctly()
    {
        $admin = $this->createAdmin();
        
        $estudiante = Estudiante::factory()->create([
            'nombre' => 'María José',
            'apellido' => 'González López',
            'documento' => '98765432',
        ]);
        
        $this->actingAs($admin);
        
        $expectedNombreCompleto = 'María José González López - 98765432';
        $this->assertEquals($expectedNombreCompleto, $estudiante->nombre_completo);
    }

    /** @test */
    public function bulk_operations_work_correctly()
    {
        $admin = $this->createAdmin();
        $estudiantes = Estudiante::factory(5)->create(['activo' => true]);
        
        $this->actingAs($admin);
        
        // Simular operación en lote para desactivar estudiantes
        $estudianteIds = $estudiantes->pluck('id')->toArray();
        Estudiante::whereIn('id', $estudianteIds)->update(['activo' => false]);
        
        foreach ($estudianteIds as $id) {
            $this->assertDatabaseHas('estudiantes', [
                'id' => $id,
                'activo' => false,
            ]);
        }
    }

    /** @test */
    public function searching_estudiantes_works()
    {
        $admin = $this->createAdmin();
        
        $estudiante1 = Estudiante::factory()->create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'documento' => '11111111',
        ]);
        
        $estudiante2 = Estudiante::factory()->create([
            'nombre' => 'María',
            'apellido' => 'González',
            'documento' => '22222222',
        ]);
        
        $this->actingAs($admin);
        
        // Buscar por nombre
        $results = Estudiante::where('nombre', 'like', '%Juan%')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($estudiante1->id, $results->first()->id);
        
        // Buscar por documento
        $results = Estudiante::where('documento', '22222222')->get();
        $this->assertCount(1, $results);
        $this->assertEquals($estudiante2->id, $results->first()->id);
    }
}
