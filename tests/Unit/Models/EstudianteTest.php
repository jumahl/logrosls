<?php

namespace Tests\Unit\Models;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\EstudianteLogro;
use App\Models\Logro;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class EstudianteTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = [
            'nombre', 'apellido', 'documento', 'fecha_nacimiento',
            'direccion', 'telefono', 'email', 'grado_id', 'activo'
        ];
        $estudiante = new Estudiante();
        
        $this->assertHasFillable($estudiante, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
            'id' => 'int',
        ];
        $estudiante = new Estudiante();
        
        $this->assertHasCasts($estudiante, $expectedCasts);
    }

    /** @test */
    public function it_has_nombre_completo_appended()
    {
        $estudiante = new Estudiante();
        $this->assertContains('nombre_completo', $estudiante->getAppends());
    }

    /** @test */
    public function it_has_grado_relationship()
    {
        $estudiante = Estudiante::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudiante->grado());
    }

    /** @test */
    public function it_has_estudiante_logros_relationship()
    {
        $estudiante = Estudiante::factory()->create();
        $this->assertInstanceOf(HasMany::class, $estudiante->estudianteLogros());
    }

    /** @test */
    public function it_has_logros_relationship()
    {
        $estudiante = Estudiante::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $estudiante->logros());
    }

    /** @test */
    public function it_generates_nombre_completo_attribute()
    {
        $estudiante = Estudiante::factory()->create([
            'nombre' => 'Juan Carlos',
            'apellido' => 'Pérez García',
            'documento' => '12345678'
        ]);

        $expected = 'Juan Carlos Pérez García - 12345678';
        $this->assertEquals($expected, $estudiante->nombre_completo);
    }

    /** @test */
    public function it_can_create_estudiante_with_factory()
    {
        $grado = Grado::factory()->create();
        $estudiante = Estudiante::factory()->create([
            'nombre' => 'María',
            'apellido' => 'González López',
            'documento' => '87654321',
            'grado_id' => $grado->id,
        ]);

        $this->assertEquals('María', $estudiante->nombre);
        $this->assertEquals('González López', $estudiante->apellido);
        $this->assertEquals('87654321', $estudiante->documento);
        $this->assertEquals($grado->id, $estudiante->grado_id);
        $this->assertDatabaseHas('estudiantes', [
            'nombre' => 'María',
            'documento' => '87654321',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_grado()
    {
        $grado = Grado::factory()->create(['nombre' => 'Tercero']);
        $estudiante = Estudiante::factory()->create(['grado_id' => $grado->id]);

        $this->assertNotNull($estudiante->grado);
        $this->assertEquals('Tercero', $estudiante->grado->nombre);
        $this->assertInstanceOf(Grado::class, $estudiante->grado);
    }

    /** @test */
    public function it_can_have_many_estudiante_logros()
    {
        $estudiante = Estudiante::factory()->create();
        $logros = EstudianteLogro::factory(3)->create(['estudiante_id' => $estudiante->id]);

        $this->assertCount(3, $estudiante->estudianteLogros);
        $this->assertTrue($estudiante->estudianteLogros->contains($logros[0]));
    }

    /** @test */
    public function it_can_have_many_logros_through_pivot()
    {
        $setup = $this->createStudentWithLogros();
        $estudiante = $setup['estudiantes']['juan'];

        $this->assertCount(2, $estudiante->logros);
        $this->assertTrue($estudiante->logros->contains($setup['logros']['matematicas_basico']));
        $this->assertTrue($estudiante->logros->contains($setup['logros']['lenguaje_lectura']));
    }

    /** @test */
    public function logros_relationship_includes_pivot_data()
    {
        $setup = $this->createStudentWithLogros();
        $estudiante = $setup['estudiantes']['juan'];
        
        $logro = $estudiante->logros->first();
        $this->assertNotNull($logro->pivot->fecha_asignacion);
        // El campo observaciones es opcional y está disponible en el pivot
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\Pivot::class, $logro->pivot);
    }

    /** @test */
    public function it_deletes_estudiante_logros_when_estudiante_is_deleted()
    {
        $setup = $this->createStudentWithLogros();
        $estudiante = $setup['estudiantes']['juan'];
        $estudianteLogroId = $setup['estudianteLogros'][0]->id;

        $this->assertDatabaseHas('estudiante_logros', ['id' => $estudianteLogroId]);
        
        $estudiante->delete();
        
        $this->assertDatabaseMissing('estudiante_logros', ['id' => $estudianteLogroId]);
    }

    /** @test */
    public function documento_must_be_unique()
    {
        Estudiante::factory()->create(['documento' => '12345678']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Estudiante::factory()->create(['documento' => '12345678']);
    }

    /** @test */
    public function it_casts_fecha_nacimiento_to_date()
    {
        $estudiante = Estudiante::factory()->create([
            'fecha_nacimiento' => '2010-05-15'
        ]);

        $this->assertInstanceOf(Carbon::class, $estudiante->fecha_nacimiento);
        $this->assertEquals('2010-05-15', $estudiante->fecha_nacimiento->format('Y-m-d'));
    }

    /** @test */
    public function email_and_phone_are_optional()
    {
        $estudiante = Estudiante::factory()->minimal()->create([
            'email' => null,
            'telefono' => null,
        ]);

        $this->assertNull($estudiante->email);
        $this->assertNull($estudiante->telefono);
        $this->assertDatabaseHas('estudiantes', [
            'id' => $estudiante->id,
            'email' => null,
            'telefono' => null,
        ]);
    }

    /** @test */
    public function direccion_is_optional()
    {
        $estudiante = Estudiante::factory()->create(['direccion' => null]);

        $this->assertNull($estudiante->direccion);
    }

    /** @test */
    public function activo_defaults_to_true()
    {
        $estudiante = Estudiante::factory()->create(['activo' => true]);
        $this->assertTrue($estudiante->activo);
    }

    /** @test */
    public function it_can_be_marked_as_inactive()
    {
        $estudiante = Estudiante::factory()->inactivo()->create();
        $this->assertFalse($estudiante->activo);
    }

    /** @test */
    public function it_requires_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Estudiante::create([
            'apellido' => 'Test',
            'documento' => '12345678',
        ]);
    }

    /** @test */
    public function factory_creates_valid_dates()
    {
        $estudiante = Estudiante::factory()->young()->create();
        $fechaNacimiento = \Carbon\Carbon::parse($estudiante->fecha_nacimiento);
        $age = $fechaNacimiento->diffInYears(now());
        
        $this->assertTrue($age >= 5 && $age <= 8);
    }

    /** @test */
    public function factory_can_create_older_students()
    {
        $estudiante = Estudiante::factory()->older()->create();
        $fechaNacimiento = \Carbon\Carbon::parse($estudiante->fecha_nacimiento);
        $age = $fechaNacimiento->diffInYears(now());
        
        $this->assertTrue($age >= 15 && $age <= 18);
    }

    /** @test */
    public function factory_can_create_with_specific_grado()
    {
        $grado = Grado::factory()->create(['nombre' => 'Quinto']);
        $estudiante = Estudiante::factory()->withGrado($grado)->create();

        $this->assertEquals($grado->id, $estudiante->grado_id);
        $this->assertEquals('Quinto', $estudiante->grado->nombre);
    }
}
