<?php

namespace Database\Factories;

use App\Models\AnioEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnioEscolar>
 */
class AnioEscolarFactory extends Factory
{
    protected $model = AnioEscolar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $anio = $this->faker->numberBetween(2020, 2025);
        
        return [
            'anio' => $anio,
            'activo' => false, // Por defecto inactivo
            'finalizado' => false,
            'fecha_inicio' => "{$anio}-02-01",
            'fecha_fin' => "{$anio}-11-30", 
            'observaciones' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the año escolar is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the año escolar is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Indicate that the año escolar is finalized.
     */
    public function finalizado(): static
    {
        return $this->state(fn (array $attributes) => [
            'finalizado' => true,
            'activo' => false, // Un año finalizado no puede estar activo
        ]);
    }

    /**
     * Create año escolar for a specific year.
     */
    public function forYear(int $anio): static
    {
        return $this->state(fn (array $attributes) => [
            'anio' => $anio,
            'fecha_inicio' => "{$anio}-02-01",
            'fecha_fin' => "{$anio}-11-30",
        ]);
    }

    /**
     * Create the current year's año escolar.
     */
    public function currentYear(): static
    {
        $currentYear = date('Y');
        return $this->forYear($currentYear)->activo();
    }

    /**
     * Create a past year's año escolar.
     */
    public function pastYear(int $yearsAgo = 1): static
    {
        $year = date('Y') - $yearsAgo;
        return $this->forYear($year)->finalizado();
    }

    /**
     * Create a future year's año escolar.
     */
    public function futureYear(int $yearsAhead = 1): static
    {
        $year = date('Y') + $yearsAhead;
        return $this->forYear($year)->inactivo();
    }
}