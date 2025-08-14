<?php

namespace Tests\Unit\Models;

use App\Models\Periodo;
use App\Models\Logro;
use App\Models\EstudianteLogro;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PeriodoTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = [
            'corte', 'año_escolar', 'numero_periodo', 'fecha_inicio', 'fecha_fin', 'activo'
        ];
        $periodo = new Periodo();
        
        $this->assertHasFillable($periodo, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
            'año_escolar' => 'integer',
            'numero_periodo' => 'integer',
            'id' => 'int',
        ];
        $periodo = new Periodo();
        
        $this->assertHasCasts($periodo, $expectedCasts);
    }

    /** @test */
    public function it_has_logros_relationship()
    {
        $periodo = Periodo::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $periodo->logros());
    }

    /** @test */
    public function it_has_estudiante_logros_relationship()
    {
        $periodo = Periodo::factory()->create();
        $this->assertInstanceOf(HasMany::class, $periodo->estudianteLogros());
    }

    /** @test */
    public function it_can_create_periodo_with_factory()
    {
        $periodo = Periodo::factory()->create([
            'corte' => 'Primer Corte',
            'año_escolar' => 2024,
            'numero_periodo' => 1,
            'activo' => true,
        ]);

        $this->assertEquals('Primer Corte', $periodo->corte);
        $this->assertEquals(2024, $periodo->año_escolar);
        $this->assertEquals(1, $periodo->numero_periodo);
        $this->assertTrue($periodo->activo);
    }

    /** @test */
    public function it_generates_nombre_attribute()
    {
        $periodo = Periodo::factory()->create(['numero_periodo' => 2]);
        
        $this->assertEquals('Período 2', $periodo->nombre);
    }

    /** @test */
    public function it_generates_periodo_completo_attribute()
    {
        $periodo = Periodo::factory()->create([
            'numero_periodo' => 1,
            'corte' => 'Primer Corte',
            'año_escolar' => 2024,
        ]);
        
        $expected = 'Período 1 - Primer Corte 2024';
        $this->assertEquals($expected, $periodo->periodo_completo);
    }

    /** @test */
    public function it_can_have_many_logros()
    {
        $periodo = Periodo::factory()->create();
        $logros = Logro::factory(3)->create();
        
        $periodo->logros()->attach($logros->pluck('id'));
        
        $this->assertCount(3, $periodo->logros);
        $this->assertTrue($periodo->logros->contains($logros[0]));
    }

    /** @test */
    public function it_can_have_many_estudiante_logros()
    {
        $periodo = Periodo::factory()->create();
        $estudianteLogros = EstudianteLogro::factory(5)->create(['periodo_id' => $periodo->id]);

        $this->assertCount(5, $periodo->estudianteLogros);
        $this->assertTrue($periodo->estudianteLogros->contains($estudianteLogros[0]));
    }

    /** @test */
    public function scope_activos_returns_only_active_periodos()
    {
        Periodo::factory()->activo()->create();
        Periodo::factory()->inactivo()->create();
        Periodo::factory()->activo()->create();

        $activosCount = Periodo::activos()->count();
        $totalCount = Periodo::count();

        $this->assertEquals(2, $activosCount);
        $this->assertEquals(3, $totalCount);
    }

    /** @test */
    public function scope_por_año_escolar_filters_by_year()
    {
        Periodo::factory()->forYear(2023)->create();
        Periodo::factory()->forYear(2024)->create();
        Periodo::factory()->forYear(2024)->create();

        $periodos2024 = Periodo::porAñoEscolar(2024)->get();
        $periodos2023 = Periodo::porAñoEscolar(2023)->get();

        $this->assertCount(2, $periodos2024);
        $this->assertCount(1, $periodos2023);
    }

    /** @test */
    public function scope_por_numero_periodo_filters_by_period_number()
    {
        Periodo::factory()->primerPeriodo()->create();
        Periodo::factory()->segundoPeriodo()->create();
        Periodo::factory()->primerPeriodo()->create();

        $primerosPeriodos = Periodo::porNumeroPeriodo(1)->get();
        $segundosPeriodos = Periodo::porNumeroPeriodo(2)->get();

        $this->assertCount(2, $primerosPeriodos);
        $this->assertCount(1, $segundosPeriodos);
    }

    /** @test */
    public function scope_por_corte_filters_by_cut()
    {
        Periodo::factory()->primerCorte()->create();
        Periodo::factory()->segundoCorte()->create();
        Periodo::factory()->primerCorte()->create();

        $primerCorte = Periodo::porCorte('Primer Corte')->get();
        $segundoCorte = Periodo::porCorte('Segundo Corte')->get();

        $this->assertCount(2, $primerCorte);
        $this->assertCount(1, $segundoCorte);
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $periodo = Periodo::factory()->create([
            'fecha_inicio' => '2024-02-01',
            'fecha_fin' => '2024-04-15',
        ]);

        $this->assertInstanceOf(Carbon::class, $periodo->fecha_inicio);
        $this->assertInstanceOf(Carbon::class, $periodo->fecha_fin);
        $this->assertEquals('2024-02-01', $periodo->fecha_inicio->format('Y-m-d'));
        $this->assertEquals('2024-04-15', $periodo->fecha_fin->format('Y-m-d'));
    }

    /** @test */
    public function it_validates_fecha_fin_after_fecha_inicio()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio.');
        
        Periodo::factory()->create([
            'fecha_inicio' => '2024-04-15',
            'fecha_fin' => '2024-02-01', // Fecha fin antes que inicio
        ]);
    }

    /** @test */
    public function it_detaches_logros_when_deleted()
    {
        $periodo = Periodo::factory()->create();
        $logro = Logro::factory()->create();
        
        $periodo->logros()->attach($logro->id);
        $this->assertDatabaseHas('logro_periodo', [
            'periodo_id' => $periodo->id,
            'logro_id' => $logro->id,
        ]);
        
        $periodo->delete();
        
        $this->assertDatabaseMissing('logro_periodo', [
            'periodo_id' => $periodo->id,
            'logro_id' => $logro->id,
        ]);
    }

    /** @test */
    public function factory_can_create_different_periods()
    {
        $primer = Periodo::factory()->primerPeriodo()->create();
        $segundo = Periodo::factory()->segundoPeriodo()->create();
        $tercer = Periodo::factory()->tercerPeriodo()->create();
        $cuarto = Periodo::factory()->cuartoPeriodo()->create();

        $this->assertEquals(1, $primer->numero_periodo);
        $this->assertEquals(2, $segundo->numero_periodo);
        $this->assertEquals(3, $tercer->numero_periodo);
        $this->assertEquals(4, $cuarto->numero_periodo);
    }

    /** @test */
    public function factory_can_create_different_cuts()
    {
        $primerCorte = Periodo::factory()->primerCorte()->create();
        $segundoCorte = Periodo::factory()->segundoCorte()->create();

        $this->assertEquals('Primer Corte', $primerCorte->corte);
        $this->assertEquals('Segundo Corte', $segundoCorte->corte);
    }

    /** @test */
    public function factory_creates_valid_date_ranges()
    {
        $periodo = Periodo::factory()->withValidDates()->create();
        
        $this->assertTrue($periodo->fecha_fin > $periodo->fecha_inicio);
    }

    /** @test */
    public function it_can_be_created_for_specific_year()
    {
        $periodo = Periodo::factory()->forYear(2025)->create();
        
        $this->assertEquals(2025, $periodo->año_escolar);
    }

    /** @test */
    public function periodo_anterior_returns_correct_period()
    {
        $primerPeriodoPrimerCorte = Periodo::factory()->create([
            'año_escolar' => 2024,
            'numero_periodo' => 1,
            'corte' => 'Primer Corte',
        ]);
        
        $primerPeriodoSegundoCorte = Periodo::factory()->create([
            'año_escolar' => 2024,
            'numero_periodo' => 1,
            'corte' => 'Segundo Corte',
        ]);
        
        $segundoPeriodoPrimerCorte = Periodo::factory()->create([
            'año_escolar' => 2024,
            'numero_periodo' => 2,
            'corte' => 'Primer Corte',
        ]);

        // El anterior del segundo corte del primer período es el primer corte del primer período
        $anterior = $primerPeriodoSegundoCorte->periodo_anterior;
        $this->assertEquals($primerPeriodoPrimerCorte->id, $anterior->id);

        // El anterior del primer corte del segundo período es el segundo corte del primer período
        $anterior = $segundoPeriodoPrimerCorte->periodo_anterior;
        $this->assertEquals($primerPeriodoSegundoCorte->id, $anterior->id);
    }

    /** @test */
    public function activo_defaults_to_true()
    {
        $periodo = Periodo::factory()->create(['activo' => true]);
        $this->assertTrue($periodo->activo);
    }

    /** @test */
    public function it_can_be_marked_as_inactive()
    {
        $periodo = Periodo::factory()->inactivo()->create();
        $this->assertFalse($periodo->activo);
    }

    /** @test */
    public function it_requires_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Periodo::create([
            'año_escolar' => 2024,
            'numero_periodo' => 1,
        ]);
    }
}
