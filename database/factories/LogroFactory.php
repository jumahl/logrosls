<?php

namespace Database\Factories;

use App\Models\Logro;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Logro>
 */
class LogroFactory extends Factory
{
    protected $model = Logro::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = ['conocimiento', 'habilidad', 'actitud', 'valor'];
        $niveles = ['bajo', 'medio', 'alto'];
        $dimensiones = ['cognitiva', 'procedimental', 'actitudinal', 'comunicativa'];

        return [
            'codigo' => $this->generateCodigo(),
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(3),
            'materia_id' => Materia::factory(),
            'nivel_dificultad' => $this->faker->randomElement($niveles),
            'tipo' => $this->faker->randomElement($tipos),
            'activo' => true,
            'competencia' => $this->faker->sentence(6),
            'tema' => $this->faker->words(3, true),
            'indicador_desempeno' => $this->faker->sentence(8),
            'dimension' => $this->faker->randomElement($dimensiones),
            'orden' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Generate a unique code for the logro.
     */
    private function generateCodigo(): string
    {
        return strtoupper($this->faker->lexify('???')) . '-' . $this->faker->numberBetween(100, 999);
    }

    /**
     * Indicate that the logro is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the logro is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Set a specific materia for the logro.
     */
    public function withMateria(Materia $materia): static
    {
        return $this->state(fn (array $attributes) => [
            'materia_id' => $materia->id,
        ]);
    }

    /**
     * Create a conceptual logro.
     */
    public function conocimiento(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'conocimiento',
            'dimension' => 'cognitiva',
            'titulo' => 'Comprende ' . $this->faker->words(3, true),
            'descripcion' => 'El estudiante demuestra comprensión de ' . $this->faker->words(5, true),
        ]);
    }

    /**
     * Create a procedimental logro.
     */
    public function habilidad(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'habilidad',
            'dimension' => 'procedimental',
            'titulo' => 'Aplica ' . $this->faker->words(3, true),
            'descripcion' => 'El estudiante aplica procedimientos para ' . $this->faker->words(5, true),
        ]);
    }

    /**
     * Create an actitudinal logro.
     */
    public function actitud(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'actitud',
            'dimension' => 'actitudinal',
            'titulo' => 'Demuestra ' . $this->faker->words(3, true),
            'descripcion' => 'El estudiante demuestra actitudes de ' . $this->faker->words(5, true),
        ]);
    }

    /**
     * Create a valor logro.
     */
    public function valor(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'valor',
            'dimension' => 'actitudinal',
            'titulo' => 'Valora ' . $this->faker->words(3, true),
            'descripcion' => 'El estudiante valora y respeta ' . $this->faker->words(5, true),
        ]);
    }

    /**
     * Create a bajo level logro.
     */
    public function bajo(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_dificultad' => 'bajo',
        ]);
    }

    /**
     * Create a medio level logro.
     */
    public function medio(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_dificultad' => 'medio',
        ]);
    }

    /**
     * Create an alto level logro.
     */
    public function alto(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_dificultad' => 'alto',
        ]);
    }

    // Mantener compatibilidad con nombres antiguos
    public function basico(): static { return $this->bajo(); }
    public function intermedio(): static { return $this->medio(); }
    public function avanzado(): static { return $this->alto(); }
    public function conceptual(): static { return $this->conocimiento(); }
    public function procedimental(): static { return $this->habilidad(); }
    public function actitudinal(): static { return $this->actitud(); }
}
