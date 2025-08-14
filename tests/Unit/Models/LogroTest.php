<?php

namespace Tests\Unit\Models;

use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Estudiante;
use App\Models\EstudianteLogro;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogroTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = [
            'codigo', 'titulo', 'descripcion', 'materia_id', 'nivel_dificultad', 'tipo',
            'activo', 'competencia', 'tema', 'indicador_desempeno', 'dimension', 'orden'
        ];
        $logro = new Logro();
        
        $this->assertHasFillable($logro, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'activo' => 'boolean',
            'id' => 'int',
        ];
        $logro = new Logro();
        
        $this->assertHasCasts($logro, $expectedCasts);
    }

    /** @test */
    public function it_has_materia_relationship()
    {
        $logro = Logro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $logro->materia());
    }

    /** @test */
    public function it_has_periodos_relationship()
    {
        $logro = Logro::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $logro->periodos());
    }

    /** @test */
    public function it_has_estudiantes_relationship()
    {
        $logro = Logro::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $logro->estudiantes());
    }

    /** @test */
    public function it_has_estudiante_logros_relationship()
    {
        $logro = Logro::factory()->create();
        $this->assertInstanceOf(HasMany::class, $logro->estudianteLogros());
    }

    /** @test */
    public function it_can_create_logro_with_factory()
    {
        $materia = Materia::factory()->create();
        $logro = Logro::factory()->create([
            'titulo' => 'Comprende operaciones básicas',
            'descripcion' => 'El estudiante realiza sumas y restas',
            'materia_id' => $materia->id,
            'nivel_dificultad' => 'bajo',
            'tipo' => 'conocimiento',
        ]);

        $this->assertEquals('Comprende operaciones básicas', $logro->titulo);
        $this->assertEquals('El estudiante realiza sumas y restas', $logro->descripcion);
        $this->assertEquals($materia->id, $logro->materia_id);
        $this->assertEquals('bajo', $logro->nivel_dificultad);
        $this->assertEquals('conocimiento', $logro->tipo);
    }

    /** @test */
    public function it_belongs_to_a_materia()
    {
        $materia = Materia::factory()->create(['nombre' => 'Matemáticas']);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);

        $this->assertNotNull($logro->materia);
        $this->assertEquals('Matemáticas', $logro->materia->nombre);
        $this->assertInstanceOf(Materia::class, $logro->materia);
    }

    /** @test */
    public function it_can_have_many_periodos()
    {
        $logro = Logro::factory()->create();
        $periodos = Periodo::factory(3)->create();
        
        $logro->periodos()->attach($periodos->pluck('id'));
        
        $this->assertCount(3, $logro->periodos);
        $this->assertTrue($logro->periodos->contains($periodos[0]));
    }

    /** @test */
    public function it_can_have_many_estudiantes()
    {
        $logro = Logro::factory()->create();
        $estudiantes = Estudiante::factory(2)->create();
        
        foreach ($estudiantes as $estudiante) {
            EstudianteLogro::factory()->create([
                'estudiante_id' => $estudiante->id,
                'logro_id' => $logro->id,
            ]);
        }
        
        $this->assertCount(2, $logro->estudiantes);
        $this->assertTrue($logro->estudiantes->contains($estudiantes[0]));
    }

    /** @test */
    public function estudiantes_relationship_includes_pivot_data()
    {
        $logro = Logro::factory()->create();
        $estudiante = Estudiante::factory()->create();
        
        EstudianteLogro::factory()->create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'fecha_asignacion' => '2024-01-15',
            'observaciones' => 'Excelente trabajo',
        ]);
        
        $estudianteWithPivot = $logro->estudiantes->first();
        $this->assertStringStartsWith('2024-01-15', $estudianteWithPivot->pivot->fecha_asignacion);
        $this->assertEquals('Excelente trabajo', $estudianteWithPivot->pivot->observaciones);
    }

    /** @test */
    public function scope_activos_returns_only_active_logros()
    {
        Logro::factory()->create(['activo' => true]);
        Logro::factory()->create(['activo' => false]);
        Logro::factory()->create(['activo' => true]);

        $activosCount = Logro::activos()->count();
        $totalCount = Logro::count();

        $this->assertEquals(2, $activosCount);
        $this->assertEquals(3, $totalCount);
    }

    /** @test */
    public function scope_por_grado_filters_by_grado()
    {
        $setup = $this->createAcademicSetup();
        $grado = $setup['grados']['primero'];
        
        // Los logros están asociados a materias que están asociadas al grado
        $logrosDelGrado = Logro::porGrado($grado->id)->get();
        
        foreach ($logrosDelGrado as $logro) {
            $materiasDelGrado = $grado->materias->pluck('id');
            $this->assertTrue($materiasDelGrado->contains($logro->materia_id));
        }
    }

    /** @test */
    public function scope_por_materia_filters_by_materia()
    {
        $materia = Materia::factory()->create();
        $logro1 = Logro::factory()->create(['materia_id' => $materia->id]);
        $logro2 = Logro::factory()->create(); // Different materia
        
        $logrosDeLaMateria = Logro::porMateria($materia->id)->get();
        
        $this->assertCount(1, $logrosDeLaMateria);
        $this->assertTrue($logrosDeLaMateria->contains($logro1));
        $this->assertFalse($logrosDeLaMateria->contains($logro2));
    }

    /** @test */
    public function scope_por_nivel_dificultad_filters_by_nivel()
    {
        Logro::factory()->bajo()->create();
        Logro::factory()->medio()->create();
        Logro::factory()->alto()->create();
        
        $bajosCount = Logro::porNivelDificultad('bajo')->count();
        $mediosCount = Logro::porNivelDificultad('medio')->count();
        $altosCount = Logro::porNivelDificultad('alto')->count();
        
        $this->assertEquals(1, $bajosCount);
        $this->assertEquals(1, $mediosCount);
        $this->assertEquals(1, $altosCount);
    }

    /** @test */
    public function scope_por_tipo_filters_by_tipo()
    {
        Logro::factory()->conocimiento()->create();
        Logro::factory()->habilidad()->create();
        Logro::factory()->actitud()->create();
        
        $conocimientoCount = Logro::porTipo('conocimiento')->count();
        $habilidadCount = Logro::porTipo('habilidad')->count();
        $actitudCount = Logro::porTipo('actitud')->count();
        
        $this->assertEquals(1, $conocimientoCount);
        $this->assertEquals(1, $habilidadCount);
        $this->assertEquals(1, $actitudCount);
    }

    /** @test */
    public function it_deletes_estudiante_logros_when_deleted()
    {
        $logro = Logro::factory()->create();
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);

        $this->assertDatabaseHas('estudiante_logros', ['id' => $estudianteLogro->id]);
        
        $logro->delete();
        
        $this->assertDatabaseMissing('estudiante_logros', ['id' => $estudianteLogro->id]);
    }

    /** @test */
    public function it_detaches_periodos_when_deleted()
    {
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        $logro->periodos()->attach($periodo->id);
        $this->assertDatabaseHas('logro_periodo', [
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
        ]);
        
        $logro->delete();
        
        $this->assertDatabaseMissing('logro_periodo', [
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
        ]);
    }

    /** @test */
    public function factory_creates_different_tipos()
    {
        $conocimiento = Logro::factory()->conocimiento()->create();
        $habilidad = Logro::factory()->habilidad()->create();
        $actitud = Logro::factory()->actitud()->create();

        $this->assertEquals('conocimiento', $conocimiento->tipo);
        $this->assertEquals('cognitiva', $conocimiento->dimension);
        
        $this->assertEquals('habilidad', $habilidad->tipo);
        $this->assertEquals('procedimental', $habilidad->dimension);
        
        $this->assertEquals('actitud', $actitud->tipo);
        $this->assertEquals('actitudinal', $actitud->dimension);
    }

    /** @test */
    public function factory_creates_different_niveles()
    {
        $bajo = Logro::factory()->bajo()->create();
        $medio = Logro::factory()->medio()->create();
        $alto = Logro::factory()->alto()->create();

        $this->assertEquals('bajo', $bajo->nivel_dificultad);
        $this->assertEquals('medio', $medio->nivel_dificultad);
        $this->assertEquals('alto', $alto->nivel_dificultad);
    }

    /** @test */
    public function factory_generates_unique_codigo()
    {
        $logro1 = Logro::factory()->create();
        $logro2 = Logro::factory()->create();

        $this->assertNotEquals($logro1->codigo, $logro2->codigo);
    }

    /** @test */
    public function activo_defaults_to_true()
    {
        $logro = Logro::factory()->create();
        $this->assertTrue($logro->activo);
    }

    /** @test */
    public function it_can_be_marked_as_inactive()
    {
        $logro = Logro::factory()->inactivo()->create();
        $this->assertFalse($logro->activo);
    }

    /** @test */
    public function it_requires_titulo_field()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Logro::create([
            'descripcion' => 'Test description',
            'materia_id' => Materia::factory()->create()->id,
        ]);
    }

    /** @test */
    public function it_can_be_assigned_to_specific_materia()
    {
        $materia = Materia::factory()->create();
        $logro = Logro::factory()->withMateria($materia)->create();

        $this->assertEquals($materia->id, $logro->materia_id);
    }
}
