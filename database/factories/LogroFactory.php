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
        return [
            'codigo' => $this->generateCodigo(),
            'titulo' => $this->faker->boolean(70) ? $this->faker->sentence(3) : null,
            'desempeno' => $this->faker->paragraph(2),
            'materia_id' => Materia::factory(),
            'activo' => true,
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

    // Métodos de compatibilidad con estados antiguos (no-ops)
    public function basico(): static { return $this->state(fn (array $attributes) => []); }
    public function medio(): static { return $this->state(fn (array $attributes) => []); }
    public function alto(): static { return $this->state(fn (array $attributes) => []); }
    public function intermedio(): static { return $this->state(fn (array $attributes) => []); }
    public function avanzado(): static { return $this->state(fn (array $attributes) => []); }
    public function conocimiento(): static { return $this->state(fn (array $attributes) => []); }
    public function habilidad(): static { return $this->state(fn (array $attributes) => []); }
    public function actitud(): static { return $this->state(fn (array $attributes) => []); }
    public function valor(): static { return $this->state(fn (array $attributes) => []); }

    // Estados específicos antiguos eliminados (tipo/nivel/dimensión). Mantener API mínima.
}
