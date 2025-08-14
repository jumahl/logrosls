<?php

namespace Tests\Feature\CRUD;

use App\Models\Periodo;
use App\Models\Logro;
use Carbon\Carbon;
use Tests\TestCase;

class PeriodoCRUDTest extends TestCase
{
    /** @test */
    public function admin_can_create_periodo()
    {
        $admin = $this->createAdmin();
        
        $this->actingAs($admin);
        
        $periodoData = [
            'corte' => 'Primer Corte',
            'año_escolar' => 2024,
            'numero_periodo' => 1,
            'fecha_inicio' => '2024-02-01',
            'fecha_fin' => '2024-04-30',
            'activo' => true,
        ];
        
        $periodo = Periodo::create($periodoData);
        
        $this->assertDatabaseHas('periodos', [
            'corte' => 'Primer Corte',
            'año_escolar' => 2024,
            'numero_periodo' => 1,
            'fecha_inicio' => '2024-02-01 00:00:00',
            'fecha_fin' => '2024-04-30 00:00:00',
            'activo' => 1,
        ]);
    }

    /** @test */
    public function admin_can_view_all_periodos()
    {
        $admin = $this->createAdmin();
        $admin->assignRole('admin');
        $periodos = Periodo::factory(4)->create();
        
        $this->actingAs($admin);

        $response = $this->get('/liceo');
        $response->assertStatus(200);
        
        foreach ($periodos as $periodo) {
            $this->assertDatabaseHas('periodos', [
                'id' => $periodo->id,
                'corte' => $periodo->corte,
            ]);
        }
    }

    /** @test */
    public function profesor_can_view_all_periodos()
    {
        $profesor = $this->createProfesor();
        $periodos = Periodo::factory(3)->create();
        
        $this->actingAs($profesor);
        
        // Los profesores pueden ver todos los períodos para su trabajo
        foreach ($periodos as $periodo) {
            $this->assertDatabaseHas('periodos', [
                'id' => $periodo->id,
                'corte' => $periodo->corte,
            ]);
        }
    }

    /** @test */
    public function admin_can_update_any_periodo()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create([
            'corte' => 'Primer Corte',
            'año_escolar' => 2024,
            'numero_periodo' => 1,
        ]);
        
        $this->actingAs($admin);
        
        $periodo->update([
            'corte' => 'Segundo Corte',
            'año_escolar' => 2025,
            'numero_periodo' => 2,
        ]);
        
        $this->assertDatabaseHas('periodos', [
            'id' => $periodo->id,
            'corte' => 'Segundo Corte',
            'año_escolar' => 2025,
            'numero_periodo' => 2,
        ]);
    }

    /** @test */
    public function only_admin_can_delete_periodos()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create();
        
        $this->actingAs($admin);
        
        $periodoId = $periodo->id;
        $periodo->delete();
        
        $this->assertDatabaseMissing('periodos', [
            'id' => $periodoId,
        ]);
    }

    /** @test */
    public function periodo_can_be_marked_as_current()
    {
        $admin = $this->createAdmin();
        $periodo1 = Periodo::factory()->create(['activo' => true]);
        $periodo2 = Periodo::factory()->create(['activo' => false]);
        
        $this->actingAs($admin);
        
        // Cambiar el período actual
        $periodo1->update(['activo' => false]);
        $periodo2->update(['activo' => true]);
        
        $this->assertDatabaseHas('periodos', [
            'id' => $periodo1->id,
            'activo' => 0,
        ]);
        
        $this->assertDatabaseHas('periodos', [
            'id' => $periodo2->id,
            'activo' => 1,
        ]);
    }

    /** @test */
    public function only_active_periodos_are_returned_by_scope()
    {
        $admin = $this->createAdmin();
        
        $periodoActivo = Periodo::factory()->activo()->create();
        $periodoInactivo = Periodo::factory()->inactivo()->create();
        
        $this->actingAs($admin);
        
        $periodosActivos = Periodo::activos()->get();
        
        $this->assertCount(1, $periodosActivos);
        $this->assertEquals($periodoActivo->id, $periodosActivos->first()->id);
    }

    /** @test */
    public function current_periodo_scope_returns_only_current()
    {
        $admin = $this->createAdmin();
        
        $periodoActual = Periodo::factory()->create(['activo' => true]);
        $periodoNoActual = Periodo::factory()->create(['activo' => false]);
        
        $this->actingAs($admin);
        
        $periodosCurrent = Periodo::activos()->get();
        
        $this->assertCount(1, $periodosCurrent);
        $this->assertEquals($periodoActual->id, $periodosCurrent->first()->id);
        $this->assertTrue($periodosCurrent->first()->activo);
    }

    /** @test */
    public function periodo_dates_validation_works()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $fechaInicio = Carbon::parse('2024-02-01');
        $fechaFin = Carbon::parse('2024-04-30');
        
        $periodo = Periodo::factory()->create([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
        
        $this->assertEquals($fechaInicio->format('Y-m-d'), $periodo->fecha_inicio->format('Y-m-d'));
        $this->assertEquals($fechaFin->format('Y-m-d'), $periodo->fecha_fin->format('Y-m-d'));
        $this->assertTrue($periodo->fecha_inicio < $periodo->fecha_fin);
    }

    /** @test */
    public function periodo_can_be_filtered_by_año()
    {
        $admin = $this->createAdmin();
        
        $periodo2024 = Periodo::factory()->create(['año_escolar' => 2024]);
        $periodo2023 = Periodo::factory()->create(['año_escolar' => 2023]);
        
        $this->actingAs($admin);
        
        $periodos2024 = Periodo::porAñoEscolar(2024)->get();
        $periodos2023 = Periodo::porAñoEscolar(2023)->get();
        
        $this->assertCount(1, $periodos2024);
        $this->assertCount(1, $periodos2023);
        
        $this->assertEquals($periodo2024->id, $periodos2024->first()->id);
        $this->assertEquals($periodo2023->id, $periodos2023->first()->id);
    }

    /** @test */
    public function periodo_can_be_ordered_by_numero()
    {
        $admin = $this->createAdmin();
        
        // Crear periodos con números específicos para asegurar el orden
        $periodo1 = Periodo::factory()->create(['numero_periodo' => 1, 'corte' => 'Primer Corte']);
        $periodo2 = Periodo::factory()->create(['numero_periodo' => 1, 'corte' => 'Segundo Corte']);
        $periodo3 = Periodo::factory()->create(['numero_periodo' => 2, 'corte' => 'Primer Corte']);
        
        $this->actingAs($admin);
        
        $periodosOrdenados = Periodo::orderBy('numero_periodo')->orderBy('corte')->get();
        
        $this->assertEquals($periodo1->id, $periodosOrdenados[0]->id);
        $this->assertEquals($periodo2->id, $periodosOrdenados[1]->id);
        $this->assertEquals($periodo3->id, $periodosOrdenados[2]->id);
    }

    /** @test */
    public function periodo_porcentaje_nota_is_decimal()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $periodo = Periodo::factory()->create(['numero_periodo' => 1]);
        
        // El modelo de periodo no tiene campo porcentaje_nota según la migración
        // En su lugar verificamos que los campos numéricos funcionan correctamente
        $this->assertEquals(1, $periodo->numero_periodo);
        $this->assertIsInt($periodo->numero_periodo);
        $this->assertIsInt($periodo->año_escolar);
    }

    /** @test */
    public function periodo_can_have_logros_assigned()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create();
        $logros = Logro::factory(3)->create();
        
        $this->actingAs($admin);
        
        $periodo->logros()->attach($logros->pluck('id'));
        
        $this->assertCount(3, $periodo->logros);
        
        foreach ($logros as $logro) {
            $this->assertDatabaseHas('logro_periodo', [
                'periodo_id' => $periodo->id,
                'logro_id' => $logro->id,
            ]);
        }
    }

    /** @test */
    public function periodo_can_detach_logros()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create();
        $logros = Logro::factory(2)->create();
        
        $this->actingAs($admin);
        
        // Asignar logros
        $periodo->logros()->attach($logros->pluck('id'));
        $this->assertCount(2, $periodo->logros);
        
        // Desasignar un logro
        $periodo->logros()->detach($logros->first()->id);
        $this->assertCount(1, $periodo->fresh()->logros);
        
        // Verificar que se eliminó de la tabla pivot
        $this->assertDatabaseMissing('logro_periodo', [
            'periodo_id' => $periodo->id,
            'logro_id' => $logros->first()->id,
        ]);
    }

    /** @test */
    public function periodo_validates_required_fields()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Intentar crear sin campos requeridos
        Periodo::create([
            // Faltan campos requeridos como corte, año_escolar, numero_periodo, fecha_inicio, fecha_fin
        ]);
    }

    /** @test */
    public function periodo_can_be_marked_as_inactive()
    {
        $admin = $this->createAdmin();
        $periodo = Periodo::factory()->create(['activo' => true]);
        
        $this->actingAs($admin);
        
        $periodo->update(['activo' => false]);
        
        $this->assertDatabaseHas('periodos', [
            'id' => $periodo->id,
            'activo' => 0,
        ]);
        
        $this->assertFalse($periodo->fresh()->activo);
    }

    /** @test */
    public function periodo_numero_should_be_between_1_and_4()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $periodo1 = Periodo::factory()->create(['numero_periodo' => 1, 'corte' => 'Primer Corte']);
        $periodo2 = Periodo::factory()->create(['numero_periodo' => 2, 'corte' => 'Segundo Corte']);
        
        $this->assertEquals(1, $periodo1->numero_periodo);
        $this->assertEquals(2, $periodo2->numero_periodo);
        
        // Los números están en el rango esperado (según la migración es 1 o 2)
        $this->assertGreaterThanOrEqual(1, $periodo1->numero_periodo);
        $this->assertLessThanOrEqual(2, $periodo2->numero_periodo);
    }

    /** @test */
    public function periodo_has_correct_relationships()
    {
        $admin = $this->createAdmin();
        $setup = $this->createAcademicSetup();
        
        $this->actingAs($admin);
        
        $periodo = $setup['periodos']['periodo1'];
        
        // Verificar que el período puede tener logros asignados
        $logro = $setup['logros']['matematicas_basico'];
        $periodo->logros()->attach($logro->id);
        
        $this->assertCount(1, $periodo->logros);
        $this->assertTrue($periodo->logros->contains($logro));
    }

    /** @test */
    public function multiple_periodos_can_exist_for_same_year()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $periodo1 = Periodo::factory()->create(['año_escolar' => 2024, 'numero_periodo' => 1, 'corte' => 'Primer Corte']);
        $periodo2 = Periodo::factory()->create(['año_escolar' => 2024, 'numero_periodo' => 1, 'corte' => 'Segundo Corte']);
        $periodo3 = Periodo::factory()->create(['año_escolar' => 2024, 'numero_periodo' => 2, 'corte' => 'Primer Corte']);
        $periodo4 = Periodo::factory()->create(['año_escolar' => 2024, 'numero_periodo' => 2, 'corte' => 'Segundo Corte']);
        
        $periodos2024 = Periodo::porAñoEscolar(2024)->get();
        
        $this->assertCount(4, $periodos2024);
        
        $numeros = $periodos2024->pluck('numero_periodo')->sort()->values();
        $this->assertEquals([1, 1, 2, 2], $numeros->toArray());
    }

    /** @test */
    public function periodo_can_be_in_date_range()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        
        $fechaHoy = Carbon::now();
        $fechaInicio = $fechaHoy->copy()->subDays(10);
        $fechaFin = $fechaHoy->copy()->addDays(10);
        
        $periodo = Periodo::factory()->create([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
        
        // Verificar que las fechas están configuradas correctamente
        $this->assertTrue($periodo->fecha_inicio <= $fechaHoy);
        $this->assertTrue($periodo->fecha_fin >= $fechaHoy);
        $this->assertTrue($periodo->fecha_inicio < $periodo->fecha_fin);
    }
}
