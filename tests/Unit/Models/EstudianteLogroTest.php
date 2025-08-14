<?php

namespace Tests\Unit\Models;

use App\Models\EstudianteLogro;
use App\Models\Estudiante;
use App\Models\Logro;
use App\Models\Periodo;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EstudianteLogroTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = [
            'estudiante_id', 'logro_id', 'periodo_id', 'nivel_desempeno',
            'observaciones', 'fecha_asignacion'
        ];
        $estudianteLogro = new EstudianteLogro();
        
        $this->assertHasFillable($estudianteLogro, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'fecha_asignacion' => 'date',
            'id' => 'int',
        ];
        $estudianteLogro = new EstudianteLogro();
        
        $this->assertHasCasts($estudianteLogro, $expectedCasts);
    }

    /** @test */
    public function it_has_estudiante_relationship()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudianteLogro->estudiante());
    }

    /** @test */
    public function it_has_logro_relationship()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudianteLogro->logro());
    }

    /** @test */
    public function it_has_periodo_relationship()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudianteLogro->periodo());
    }

    /** @test */
    public function it_can_create_estudiante_logro_with_factory()
    {
        $estudiante = Estudiante::factory()->create();
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        $estudianteLogro = EstudianteLogro::factory()->create([
            'estudiante_id' => $estudiante->id,
            'logro_id' => $logro->id,
            'periodo_id' => $periodo->id,
            'nivel_desempeno' => 'E',
            'observaciones' => 'Excelente trabajo',
        ]);

        $this->assertEquals($estudiante->id, $estudianteLogro->estudiante_id);
        $this->assertEquals($logro->id, $estudianteLogro->logro_id);
        $this->assertEquals($periodo->id, $estudianteLogro->periodo_id);
        $this->assertEquals('E', $estudianteLogro->nivel_desempeno);
        $this->assertEquals('Excelente trabajo', $estudianteLogro->observaciones);
    }

    /** @test */
    public function it_belongs_to_estudiante()
    {
        $estudiante = Estudiante::factory()->create(['nombre' => 'Juan']);
        $estudianteLogro = EstudianteLogro::factory()->create(['estudiante_id' => $estudiante->id]);

        $this->assertNotNull($estudianteLogro->estudiante);
        $this->assertEquals('Juan', $estudianteLogro->estudiante->nombre);
        $this->assertInstanceOf(Estudiante::class, $estudianteLogro->estudiante);
    }

    /** @test */
    public function it_belongs_to_logro()
    {
        $logro = Logro::factory()->create(['titulo' => 'Comprende matemáticas']);
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);

        $this->assertNotNull($estudianteLogro->logro);
        $this->assertEquals('Comprende matemáticas', $estudianteLogro->logro->titulo);
        $this->assertInstanceOf(Logro::class, $estudianteLogro->logro);
    }

    /** @test */
    public function it_belongs_to_periodo()
    {
        $periodo = Periodo::factory()->create(['numero_periodo' => 2]);
        $estudianteLogro = EstudianteLogro::factory()->create(['periodo_id' => $periodo->id]);

        $this->assertNotNull($estudianteLogro->periodo);
        $this->assertEquals(2, $estudianteLogro->periodo->numero_periodo);
        $this->assertInstanceOf(Periodo::class, $estudianteLogro->periodo);
    }

    /** @test */
    public function it_calculates_valor_numerico_correctly()
    {
        $excelente = EstudianteLogro::factory()->excelente()->create();
        $sobresaliente = EstudianteLogro::factory()->sobresaliente()->create();
        $aceptable = EstudianteLogro::factory()->aceptable()->create();
        $insuficiente = EstudianteLogro::factory()->insuficiente()->create();

        $this->assertEquals(5.0, $excelente->valor_numerico);
        $this->assertEquals(4.0, $sobresaliente->valor_numerico);
        $this->assertEquals(3.0, $aceptable->valor_numerico);
        $this->assertEquals(2.0, $insuficiente->valor_numerico);
    }

    /** @test */
    public function it_returns_correct_color_nivel()
    {
        $excelente = EstudianteLogro::factory()->excelente()->create();
        $sobresaliente = EstudianteLogro::factory()->sobresaliente()->create();
        $aceptable = EstudianteLogro::factory()->aceptable()->create();
        $insuficiente = EstudianteLogro::factory()->insuficiente()->create();

        $this->assertEquals('success', $excelente->color_nivel);
        $this->assertEquals('info', $sobresaliente->color_nivel);
        $this->assertEquals('warning', $aceptable->color_nivel);
        $this->assertEquals('danger', $insuficiente->color_nivel);
    }

    /** @test */
    public function it_returns_full_nivel_desempeno_name()
    {
        $excelente = EstudianteLogro::factory()->excelente()->create();
        $sobresaliente = EstudianteLogro::factory()->sobresaliente()->create();
        $aceptable = EstudianteLogro::factory()->aceptable()->create();
        $insuficiente = EstudianteLogro::factory()->insuficiente()->create();

        $this->assertEquals('Excelente', $excelente->nivel_desempeno_completo);
        $this->assertEquals('Sobresaliente', $sobresaliente->nivel_desempeno_completo);
        $this->assertEquals('Aceptable', $aceptable->nivel_desempeno_completo);
        $this->assertEquals('Insuficiente', $insuficiente->nivel_desempeno_completo);
    }

    /** @test */
    public function factory_can_create_different_performance_levels()
    {
        $excelente = EstudianteLogro::factory()->excelente()->create();
        $sobresaliente = EstudianteLogro::factory()->sobresaliente()->create();
        $aceptable = EstudianteLogro::factory()->aceptable()->create();
        $insuficiente = EstudianteLogro::factory()->insuficiente()->create();

        $this->assertEquals('E', $excelente->nivel_desempeno);
        $this->assertEquals('S', $sobresaliente->nivel_desempeno);
        $this->assertEquals('A', $aceptable->nivel_desempeno);
        $this->assertEquals('I', $insuficiente->nivel_desempeno);
    }

    /** @test */
    public function factory_can_create_with_specific_relationships()
    {
        $estudiante = Estudiante::factory()->create();
        $logro = Logro::factory()->create();
        $periodo = Periodo::factory()->create();
        
        $estudianteLogro = EstudianteLogro::factory()
            ->withEstudiante($estudiante)
            ->withLogro($logro)
            ->withPeriodo($periodo)
            ->create();

        $this->assertEquals($estudiante->id, $estudianteLogro->estudiante_id);
        $this->assertEquals($logro->id, $estudianteLogro->logro_id);
        $this->assertEquals($periodo->id, $estudianteLogro->periodo_id);
    }

    /** @test */
    public function it_can_create_without_observations()
    {
        $estudianteLogro = EstudianteLogro::factory()->withoutObservations()->create();
        
        $this->assertNull($estudianteLogro->observaciones);
    }

    /** @test */
    public function it_can_create_with_detailed_observations()
    {
        $estudianteLogro = EstudianteLogro::factory()->withDetailedObservations()->create();
        
        $this->assertNotNull($estudianteLogro->observaciones);
        $this->assertGreaterThan(20, strlen($estudianteLogro->observaciones));
    }

    /** @test */
    public function it_can_create_recent_assignments()
    {
        $estudianteLogro = EstudianteLogro::factory()->recent()->create();
        
        $diasAtras = now()->diffInDays($estudianteLogro->fecha_asignacion);
        $this->assertLessThanOrEqual(30, $diasAtras);
    }

    /** @test */
    public function it_can_create_old_assignments()
    {
        $estudianteLogro = EstudianteLogro::factory()->old()->create();
        
        $fechaAsignacion = \Carbon\Carbon::parse($estudianteLogro->fecha_asignacion);
        $diasAtras = $fechaAsignacion->diffInDays(now());
        $this->assertGreaterThanOrEqual(180, $diasAtras);
    }

    /** @test */
    public function it_can_create_todays_assignment()
    {
        $estudianteLogro = EstudianteLogro::factory()->today()->create();
        
        $this->assertEquals(now()->format('Y-m-d'), $estudianteLogro->fecha_asignacion->format('Y-m-d'));
    }

    /** @test */
    public function it_casts_fecha_asignacion_to_date()
    {
        $estudianteLogro = EstudianteLogro::factory()->create([
            'fecha_asignacion' => '2024-03-15'
        ]);

        $this->assertInstanceOf(Carbon::class, $estudianteLogro->fecha_asignacion);
        $this->assertEquals('2024-03-15', $estudianteLogro->fecha_asignacion->format('Y-m-d'));
    }

    /** @test */
    public function observaciones_are_optional()
    {
        $estudianteLogro = EstudianteLogro::factory()->create(['observaciones' => null]);
        
        $this->assertNull($estudianteLogro->observaciones);
    }

    /** @test */
    public function nivel_desempeno_methods_work_with_valid_values()
    {
        $estudianteLogro = EstudianteLogro::factory()->create(['nivel_desempeno' => 'I']);
        
        $this->assertEquals(2.0, $estudianteLogro->valor_numerico);
        $this->assertEquals('danger', $estudianteLogro->color_nivel);
        $this->assertEquals('Insuficiente', $estudianteLogro->nivel_desempeno_completo);
    }

    /** @test */
    public function it_requires_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        EstudianteLogro::create([
            'nivel_desempeno' => 'E',
            'fecha_asignacion' => now(),
        ]);
    }

    /** @test */
    public function nivel_desempeno_accepts_valid_values()
    {
        $validLevels = ['E', 'S', 'A', 'I'];
        
        foreach ($validLevels as $level) {
            $estudianteLogro = EstudianteLogro::factory()->create(['nivel_desempeno' => $level]);
            $this->assertEquals($level, $estudianteLogro->nivel_desempeno);
        }
    }
}
