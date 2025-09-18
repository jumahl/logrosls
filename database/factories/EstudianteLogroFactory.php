<?php

namespace Database\Factories;

use App\Models\EstudianteLogro;
use App\Models\DesempenoMateria;
use App\Models\Logro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EstudianteLogro>
 */
class EstudianteLogroFactory extends Factory
{
    protected $model = EstudianteLogro::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desempeno_materia_id' => DesempenoMateria::factory(),
            'logro_id' => Logro::factory(),
            'alcanzado' => $this->faker->boolean(80), // 80% alcanzados
        ];
    }

    /**
     * Set a specific desempeno_materia for the logro.
     */
    public function withDesempenoMateria(DesempenoMateria $desempeno): static
    {
        return $this->state(fn (array $attributes) => [
            'desempeno_materia_id' => $desempeno->id,
        ]);
    }

    /**
     * Set a specific logro for the record.
     */
    public function withLogro(Logro $logro): static
    {
        return $this->state(fn (array $attributes) => [
            'logro_id' => $logro->id,
        ]);
    }

    /**
     * Create a logro alcanzado (achieved).
     */
    public function alcanzado(): static
    {
        return $this->state(fn (array $attributes) => [
            'alcanzado' => true,
        ]);
    }

    /**
     * Create a logro no alcanzado (not achieved).
     */
    public function noAlcanzado(): static
    {
        return $this->state(fn (array $attributes) => [
            'alcanzado' => false,
        ]);
    }

    /**
     * Create a logro evaluado (evaluated).
     */
    public function evaluado(): static
    {
        return $this->state(fn (array $attributes) => [
            'alcanzado' => true,
        ]);
    }

    /**
     * Create a logro pendiente (pending).
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'alcanzado' => false,
        ]);
    }

    /**
     * Create a logro with excelente performance.
     */
    public function excelente(): static
    {
        return $this->state(fn (array $attributes) => [
            'alcanzado' => true,
        ]);
    }
}