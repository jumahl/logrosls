<?php

namespace Database\Factories;

use App\Models\DesempenoMateria;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Periodo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DesempenoMateria>
 */
class DesempenoMateriaFactory extends Factory
{
    protected $model = DesempenoMateria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nivelesDesempeno = ['E', 'S', 'A', 'I']; // Excelente, Sobresaliente, Aceptable, Insuficiente

        return [
            'estudiante_id' => Estudiante::factory(),
            'materia_id' => Materia::factory(),
            'periodo_id' => Periodo::factory(),
            'nivel_desempeno' => $this->faker->randomElement($nivelesDesempeno),
            'observaciones_finales' => $this->faker->optional(0.7)->paragraph(2),
            'fecha_asignacion' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'estado' => $this->faker->randomElement(['borrador', 'publicado', 'revisado']),
        ];
    }

    /**
     * Set a specific estudiante for the desempeño.
     */
    public function withEstudiante(Estudiante $estudiante): static
    {
        return $this->state(fn (array $attributes) => [
            'estudiante_id' => $estudiante->id,
        ]);
    }

    /**
     * Set a specific materia for the desempeño.
     */
    public function withMateria(Materia $materia): static
    {
        return $this->state(fn (array $attributes) => [
            'materia_id' => $materia->id,
        ]);
    }

    /**
     * Set a specific periodo for the desempeño.
     */
    public function withPeriodo(Periodo $periodo): static
    {
        return $this->state(fn (array $attributes) => [
            'periodo_id' => $periodo->id,
        ]);
    }

    /**
     * Create an "Excelente" performance record.
     */
    public function excelente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'E',
            'observaciones_finales' => 'Demuestra un excelente dominio de la materia. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a "Sobresaliente" performance record.
     */
    public function sobresaliente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'S',
            'observaciones_finales' => 'Logra un desempeño sobresaliente. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create an "Aceptable" performance record.
     */
    public function aceptable(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'A',
            'observaciones_finales' => 'Alcanza un nivel aceptable. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create an "Insuficiente" performance record.
     */
    public function insuficiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'I',
            'observaciones_finales' => 'Requiere apoyo para mejorar el desempeño. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a borrador state.
     */
    public function borrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'borrador',
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    /**
     * Create a publicado state.
     */
    public function publicado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'publicado',
            'locked_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'locked_by' => 1, // Assuming user ID 1 exists
        ]);
    }
}
