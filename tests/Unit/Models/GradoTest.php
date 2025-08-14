<?php

namespace Tests\Unit\Models;

use App\Models\Grado;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GradoTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = ['nombre', 'tipo', 'activo'];
        $grado = new Grado();
        
        $this->assertHasFillable($grado, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'activo' => 'boolean',
            'id' => 'int',
        ];
        $grado = new Grado();
        
        $this->assertHasCasts($grado, $expectedCasts);
    }

    /** @test */
    public function it_has_estudiantes_relationship()
    {
        $grado = Grado::factory()->create();
        $this->assertInstanceOf(HasMany::class, $grado->estudiantes());
    }

    /** @test */
    public function it_has_materias_relationship()
    {
        $grado = Grado::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $grado->materias());
    }

    /** @test */
    public function it_has_logros_relationship()
    {
        $grado = Grado::factory()->create();
        $this->assertInstanceOf(HasManyThrough::class, $grado->logros());
    }

    /** @test */
    public function it_has_director_grupo_relationship()
    {
        $grado = Grado::factory()->create();
        $this->assertInstanceOf(HasOne::class, $grado->directorGrupo());
    }

    /** @test */
    public function it_can_create_grado_with_factory()
    {
        $grado = Grado::factory()->create([
            'nombre' => 'Primero',
            'tipo' => 'primaria',
            'activo' => true,
        ]);

        $this->assertEquals('Primero', $grado->nombre);
        $this->assertEquals('primaria', $grado->tipo);
        $this->assertTrue($grado->activo);
        $this->assertDatabaseHas('grados', [
            'nombre' => 'Primero',
            'tipo' => 'primaria',
        ]);
    }

    /** @test */
    public function it_can_create_different_types_of_grados()
    {
        $preescolar = Grado::factory()->preescolar()->create();
        $primaria = Grado::factory()->primaria()->create();
        $secundaria = Grado::factory()->secundaria()->create();
        $media = Grado::factory()->mediaAcademica()->create();

        $this->assertEquals('preescolar', $preescolar->tipo);
        $this->assertEquals('primaria', $primaria->tipo);
        $this->assertEquals('secundaria', $secundaria->tipo);
        $this->assertEquals('media_academica', $media->tipo);
    }

    /** @test */
    public function it_can_have_many_estudiantes()
    {
        $grado = Grado::factory()->create();
        $estudiantes = Estudiante::factory(3)->create(['grado_id' => $grado->id]);

        $this->assertCount(3, $grado->estudiantes);
        $this->assertTrue($grado->estudiantes->contains($estudiantes[0]));
    }

    /** @test */
    public function it_can_have_many_materias()
    {
        $grado = Grado::factory()->create();
        $materias = Materia::factory(2)->create();
        
        $grado->materias()->attach($materias->pluck('id'));
        
        $this->assertCount(2, $grado->materias);
        $this->assertTrue($grado->materias->contains($materias[0]));
    }

    /** @test */
    public function it_can_have_director_grupo()
    {
        $grado = Grado::factory()->create();
        $director = User::factory()->create(['director_grado_id' => $grado->id]);

        $this->assertNotNull($grado->directorGrupo);
        $this->assertEquals($director->id, $grado->directorGrupo->id);
    }

    /** @test */
    public function it_deletes_estudiantes_when_grado_is_deleted()
    {
        $grado = Grado::factory()->create();
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado->id]);

        $this->assertDatabaseHas('estudiantes', ['id' => $estudiante->id]);
        
        $grado->delete();
        
        $this->assertDatabaseMissing('estudiantes', ['id' => $estudiante->id]);
    }

    /** @test */
    public function activo_scope_returns_only_active_grados()
    {
        Grado::factory()->create(['activo' => true]);
        Grado::factory()->create(['activo' => false]);

        $this->assertEquals(1, Grado::where('activo', true)->count());
        $this->assertEquals(2, Grado::count());
    }

    /** @test */
    public function it_can_toggle_activo_status()
    {
        $grado = Grado::factory()->create(['activo' => true]);
        
        $grado->update(['activo' => false]);
        
        $this->assertFalse($grado->fresh()->activo);
    }

    /** @test */
    public function it_requires_nombre_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Grado::create([
            'tipo' => 'primaria',
            'activo' => true,
        ]);
    }

    /** @test */
    public function it_requires_tipo_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Grado::create([
            'nombre' => 'Test Grado',
            'activo' => true,
        ]);
    }

    /** @test */
    public function tipo_field_accepts_valid_enum_values()
    {
        $validTypes = ['preescolar', 'primaria', 'secundaria', 'media_academica'];
        
        foreach ($validTypes as $type) {
            $grado = Grado::factory()->create(['tipo' => $type]);
            $this->assertEquals($type, $grado->tipo);
        }
    }

    /** @test */
    public function activo_defaults_to_true()
    {
        $grado = Grado::factory()->create();
        $this->assertTrue($grado->activo);
    }
}
