<?php

namespace Database\Factories;

use App\Models\Periodo;
use App\Models\AnioEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Periodo>
 */
class PeriodoFactory extends Factory
{
    protected $model = Periodo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cortes = ['Primer Corte', 'Segundo Corte'];
        $periodos = [1, 2]; // Solo 1 o 2 según la migración
        
        // Crear o usar un año escolar existente
        $anioEscolar = AnioEscolar::inRandomOrder()->first()?->anio 
            ?? AnioEscolar::factory()->create()->anio;
        
        // Generar fechas coherentes
        $numeroCorte = $this->faker->randomElement([1, 2]);
        $numeroPeriodo = $this->faker->randomElement($periodos);
        
    $fechaInicio = $this->generateFechaInicio($anioEscolar, $numeroPeriodo, $numeroCorte);
        $fechaFin = $this->generateFechaFin($fechaInicio, $numeroCorte);

        return [
            'corte' => $this->faker->randomElement($cortes),
            'anio_escolar' => $anioEscolar,
            'numero_periodo' => $numeroPeriodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'activo' => $this->faker->boolean(80), // 80% activos
        ];
    }

    /**
     * Generate start date based on school year, period and cut.
     */
    private function generateFechaInicio(int $anio, int $periodo, int $corte): string
    {
        $mesInicio = match([$periodo, $corte]) {
            [1, 1] => 2,  // Febrero
            [1, 2] => 4,  // Abril
            [2, 1] => 5,  // Mayo
            [2, 2] => 7,  // Julio
            default => 2
        };

        return $this->faker->dateTimeBetween(
            "{$anio}-{$mesInicio}-01",
            "{$anio}-{$mesInicio}-15"
        )->format('Y-m-d');
    }

    /**
     * Generate end date based on start date and cut.
     */
    private function generateFechaFin(string $fechaInicio, int $corte): string
    {
        $inicio = new \DateTime($fechaInicio);
        
        // Primer corte: ~8 semanas, Segundo corte: ~10 semanas
        $semanas = $corte === 1 ? 8 : 10;
        $fin = clone $inicio;
        $fin->add(new \DateInterval("P{$semanas}W"));
        
        return $fin->format('Y-m-d');
    }

    /**
     * Indicate that the periodo is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the periodo is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Create a first period.
     */
    public function primerPeriodo(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_periodo' => 1,
        ]);
    }

    /**
     * Create a second period.
     */
    public function segundoPeriodo(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_periodo' => 2,
        ]);
    }

    /**
     * Create a third period.
     */
    public function tercerPeriodo(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_periodo' => 3,
        ]);
    }

    /**
     * Create a fourth period.
     */
    public function cuartoPeriodo(): static
    {
        return $this->state(fn (array $attributes) => [
            'numero_periodo' => 4,
        ]);
    }

    /**
     * Create a primer corte period.
     */
    public function primerCorte(): static
    {
        return $this->state(fn (array $attributes) => [
            'corte' => 'Primer Corte',
        ]);
    }

    /**
     * Create a segundo corte period.
     */
    public function segundoCorte(): static
    {
        return $this->state(fn (array $attributes) => [
            'corte' => 'Segundo Corte',
        ]);
    }

    /**
     * Create a period for a specific school year.
     */
    public function forYear(int $anio): static
    {
        return $this->state(fn (array $attributes) => [
            'anio_escolar' => $anio,
        ]);
    }

    /**
     * Create a period with valid date range.
     */
    public function withValidDates(): static
    {
        $fechaInicio = $this->faker->dateTimeBetween('now', '+1 month');
        $fechaFin = $this->faker->dateTimeBetween($fechaInicio, $fechaInicio->format('Y-m-d') . ' +3 months');

        return $this->state(fn (array $attributes) => [
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
        ]);
    }
}
