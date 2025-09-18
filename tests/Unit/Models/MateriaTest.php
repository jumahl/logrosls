<?php

namespace Tests\Unit\Models;

use App\Models\Materia;
use App\Models\User;
use App\Models\Grado;
use App\Models\Logro;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MateriaTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = ['nombre', 'codigo', 'descripcion', 'docente_id', 'activa', 'area'];
        $materia = new Materia();
        
        $this->assertHasFillable($materia, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'activa' => 'boolean',
            'id' => 'int',
        ];
        $materia = new Materia();
        
        $this->assertHasCasts($materia, $expectedCasts);
    }

    /** @test */
    public function it_has_grados_relationship()
    {
        $materia = Materia::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $materia->grados());
    }

    /** @test */
    public function it_has_docente_relationship()
    {
        $materia = Materia::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $materia->docente());
    }

    /** @test */
    public function it_has_logros_relationship()
    {
        $materia = Materia::factory()->create();
        $this->assertInstanceOf(HasMany::class, $materia->logros());
    }

    /** @test */
    public function it_can_create_materia_with_factory()
    {
        $docente = User::factory()->create();
        $materia = Materia::factory()->create([
            'nombre' => 'Matemáticas',
            'codigo' => 'MAT-001',
            'descripcion' => 'Materia de matemáticas básicas',
            'docente_id' => $docente->id,
            'activa' => true,
        ]);

        $this->assertEquals('Matemáticas', $materia->nombre);
        $this->assertEquals('MAT-001', $materia->codigo);
        $this->assertEquals('Materia de matemáticas básicas', $materia->descripcion);
        $this->assertEquals($docente->id, $materia->docente_id);
        $this->assertTrue($materia->activa);
    }

    /** @test */
    public function it_belongs_to_a_docente()
    {
        $docente = User::factory()->create(['name' => 'Prof. García']);
        $materia = Materia::factory()->create(['docente_id' => $docente->id]);

        $this->assertNotNull($materia->docente);
        $this->assertEquals('Prof. García', $materia->docente->name);
        $this->assertInstanceOf(User::class, $materia->docente);
    }

    /** @test */
    public function it_can_have_many_grados()
    {
        $materia = Materia::factory()->create();
        $grados = Grado::factory(3)->create();
        
        $materia->grados()->attach($grados->pluck('id'));
        
        $this->assertCount(3, $materia->grados);
        $this->assertTrue($materia->grados->contains($grados[0]));
    }

    /** @test */
    public function it_can_have_many_logros()
    {
        $materia = Materia::factory()->create();
        $logros = Logro::factory(5)->create(['materia_id' => $materia->id]);

        $this->assertCount(5, $materia->logros);
        $this->assertTrue($materia->logros->contains($logros[0]));
    }

    /** @test */
    public function it_deletes_logros_when_materia_is_deleted()
    {
        $materia = Materia::factory()->create();
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);

        $this->assertDatabaseHas('logros', ['id' => $logro->id]);
        
        $materia->delete();
        
        $this->assertDatabaseMissing('logros', ['id' => $logro->id]);
    }

    /** @test */
    public function factory_generates_unique_codigo()
    {
        $materia1 = Materia::factory()->create(['nombre' => 'Matemáticas']);
        $materia2 = Materia::factory()->create(['nombre' => 'Matemáticas']);

        $this->assertNotEquals($materia1->codigo, $materia2->codigo);
    }

    /** @test */
    public function factory_can_create_primaria_subjects()
    {
        $materia = Materia::factory()->primaria()->create();
        
        $materiasPermitidas = [
            'Matemáticas', 'Lenguaje', 'Ciencias Naturales', 'Ciencias Sociales',
            'Inglés', 'Educación Física', 'Artística', 'Ética y Valores', 'Religión'
        ];
        
        $this->assertContains($materia->nombre, $materiasPermitidas);
    }

    /** @test */
    public function factory_can_create_secundaria_subjects()
    {
        $materia = Materia::factory()->secundaria()->create();
        
        $materiasPermitidas = [
            'Matemáticas', 'Lenguaje', 'Ciencias Naturales', 'Ciencias Sociales',
            'Inglés', 'Educación Física', 'Artística', 'Tecnología e Informática',
            'Ética y Valores', 'Religión'
        ];
        
        $this->assertContains($materia->nombre, $materiasPermitidas);
    }

    /** @test */
    public function factory_can_create_media_academica_subjects()
    {
        $materia = Materia::factory()->mediaAcademica()->create();
        
        $materiasPermitidas = [
            'Matemáticas', 'Lenguaje', 'Química', 'Física', 'Biología',
            'Ciencias Sociales', 'Inglés', 'Filosofía', 'Economía', 'Política',
            'Educación Física'
        ];
        
        $this->assertContains($materia->nombre, $materiasPermitidas);
    }

    /** @test */
    public function it_can_be_assigned_to_specific_docente()
    {
        $docente = User::factory()->create();
        $materia = Materia::factory()->withDocente($docente)->create();

        $this->assertEquals($docente->id, $materia->docente_id);
    }

    /** @test */
    public function activa_defaults_to_true_in_factory()
    {
        $materia = Materia::factory()->create();
        $this->assertTrue($materia->activa);
    }

    /** @test */
    public function it_can_be_marked_as_inactive()
    {
        $materia = Materia::factory()->inactiva()->create();
        $this->assertFalse($materia->activa);
    }

    /** @test */
    public function it_requires_nombre_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Materia::create([
            'codigo' => 'TEST-001',
            'descripcion' => 'Test description',
            'docente_id' => User::factory()->create()->id,
        ]);
    }

    /** @test */
    public function descripcion_is_optional()
    {
        $materia = Materia::factory()->create(['descripcion' => null]);
        $this->assertNull($materia->descripcion);
    }

    /** @test */
    public function many_to_many_with_grados_works_correctly()
    {
        $materia = Materia::factory()->create();
        $grado1 = Grado::factory()->create(['nombre' => 'Primero']);
        $grado2 = Grado::factory()->create(['nombre' => 'Segundo']);
        
        $materia->grados()->attach([$grado1->id, $grado2->id]);
        
        // Verificar desde materia
        $this->assertCount(2, $materia->grados);
        $this->assertTrue($materia->grados->pluck('nombre')->contains('Primero'));
        $this->assertTrue($materia->grados->pluck('nombre')->contains('Segundo'));
        
        // Verificar desde grado
        $this->assertTrue($grado1->materias->contains($materia));
        $this->assertTrue($grado2->materias->contains($materia));
    }

    /** @test */
    public function it_can_be_detached_from_grados()
    {
        $materia = Materia::factory()->create();
        $grado = Grado::factory()->create();
        
        $materia->grados()->attach($grado->id);
        $this->assertCount(1, $materia->grados);
        
        $materia->grados()->detach($grado->id);
        $this->assertCount(0, $materia->fresh()->grados);
    }
}
