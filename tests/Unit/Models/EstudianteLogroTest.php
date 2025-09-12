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
            'logro_id', 'desempeno_materia_id', 'alcanzado'
        ];
        $estudianteLogro = new EstudianteLogro();
        
        $this->assertHasFillable($estudianteLogro, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'alcanzado' => 'boolean',
            'id' => 'int',
        ];
        $estudianteLogro = new EstudianteLogro();
        
        $this->assertHasCasts($estudianteLogro, $expectedCasts);
    }

    /** @test */
    public function it_has_desempeno_materia_relationship()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudianteLogro->desempenoMateria());
    }

    /** @test */
    public function it_has_logro_relationship()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $estudianteLogro->logro());
    }

    /** @test */
    public function it_can_access_estudiante_through_desempeno_materia()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();
        
        // Verifica que puede acceder al estudiante a través de la relación desempenoMateria
        $this->assertNotNull($estudianteLogro->desempenoMateria);
        $this->assertNotNull($estudianteLogro->desempenoMateria->estudiante);
        $this->assertInstanceOf(\App\Models\Estudiante::class, $estudianteLogro->desempenoMateria->estudiante);
    }

    /** @test */
    public function it_can_create_estudiante_logro_with_factory()
    {
        $logro = Logro::factory()->create();
        
        $estudianteLogro = EstudianteLogro::factory()->create([
            'logro_id' => $logro->id,
            'alcanzado' => true,
        ]);

        $this->assertEquals($logro->id, $estudianteLogro->logro_id);
        $this->assertTrue($estudianteLogro->alcanzado);
        $this->assertNotNull($estudianteLogro->desempeno_materia_id);
    }

    /** @test */
    public function it_belongs_to_desempeno_materia()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();

        $this->assertNotNull($estudianteLogro->desempenoMateria);
        $this->assertInstanceOf(\App\Models\DesempenoMateria::class, $estudianteLogro->desempenoMateria);
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
    public function it_can_access_periodo_through_desempeno_materia()
    {
        $estudianteLogro = EstudianteLogro::factory()->create();

        // Verifica que puede acceder al periodo a través de desempenoMateria
        $this->assertNotNull($estudianteLogro->desempenoMateria->periodo);
        $this->assertInstanceOf(\App\Models\Periodo::class, $estudianteLogro->desempenoMateria->periodo);
    }

    /** @test */
    public function it_can_be_alcanzado_or_not()
    {
        $alcanzado = EstudianteLogro::factory()->alcanzado()->create();
        $noAlcanzado = EstudianteLogro::factory()->noAlcanzado()->create();

        $this->assertTrue($alcanzado->alcanzado);
        $this->assertFalse($noAlcanzado->alcanzado);
    }

    /** @test */
    public function factory_can_create_with_specific_relationships()
    {
        $logro = Logro::factory()->create();
        
        $estudianteLogro = EstudianteLogro::factory()
            ->withLogro($logro)
            ->create();

        $this->assertEquals($logro->id, $estudianteLogro->logro_id);
        $this->assertNotNull($estudianteLogro->desempeno_materia_id);
    }

    /** @test */
    public function it_requires_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        EstudianteLogro::create([
            'alcanzado' => true,
        ]);
    }
}
