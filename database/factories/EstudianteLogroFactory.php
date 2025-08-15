<?php

namespace Database\Factories;

use App\Models\EstudianteLogro;
use App\Models\Estudiante;
use App\Models\Logro;
use App\Models\Periodo;
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
        $nivelesDesempeno = ['E', 'S', 'A', 'I']; // Excelente, Sobresaliente, Aceptable, Insuficiente

        return [
            'estudiante_id' => Estudiante::factory(),
            'logro_id' => Logro::factory(),
            'periodo_id' => Periodo::factory(),
            'nivel_desempeno' => $this->faker->randomElement($nivelesDesempeno),
            'observaciones' => $this->faker->optional(0.6)->paragraph(2), // 60% tienen observaciones
            'fecha_asignacion' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Set a specific estudiante for the logro.
     */
    public function withEstudiante(Estudiante $estudiante): static
    {
        return $this->state(fn (array $attributes) => [
            'estudiante_id' => $estudiante->id,
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
     * Set a specific periodo for the record.
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
            'observaciones' => 'Demuestra un excelente dominio del logro. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a "Sobresaliente" performance record.
     */
    public function sobresaliente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'S',
            'observaciones' => 'Logra un desempeño sobresaliente. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create an "Aceptable" performance record.
     */
    public function aceptable(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'A',
            'observaciones' => 'Alcanza un nivel aceptable del logro. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create an "Insuficiente" performance record.
     */
    public function insuficiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'I',
            'observaciones' => 'Requiere apoyo para alcanzar el logro. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a record without observations.
     */
    public function withoutObservations(): static
    {
        return $this->state(fn (array $attributes) => [
            'observaciones' => null,
        ]);
    }

    /**
     * Create a record with detailed observations.
     */
    public function withDetailedObservations(): static
    {
        $observaciones = [
            'El estudiante demuestra un excelente manejo de los conceptos trabajados en clase.',
            'Se observa progreso significativo en el desarrollo de las competencias esperadas.',
            'Requiere refuerzo en algunos aspectos específicos para consolidar el aprendizaje.',
            'Participa activamente en las actividades propuestas y colabora efectivamente.',
            'Muestra dificultades que requieren estrategias de apoyo adicionales.',
        ];

        return $this->state(fn (array $attributes) => [
            'observaciones' => $this->faker->randomElement($observaciones) . ' ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a recent assignment.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_asignacion' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ]);
    }

    /**
     * Create an old assignment.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_asignacion' => $this->faker->dateTimeBetween('-1 year', '-6 months')->format('Y-m-d'),
        ]);
    }

    /**
     * Create a record for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_asignacion' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Create a pendiente (pending) evaluation.
     */
    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'I', // Insuficiente como pendiente
            'observaciones' => null, // Sin observaciones = pendiente
            'fecha_asignacion' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Create an evaluado (evaluated) record.
     */
    public function evaluado(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => $this->faker->randomElement(['E', 'S', 'A']),
            'observaciones' => 'Evaluación completada. ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Create a superior performance record.
     */
    public function superior(): static
    {
        return $this->state(fn (array $attributes) => [
            'nivel_desempeno' => 'S',
            'observaciones' => 'Desempeño superior demostrado. ' . $this->faker->sentence(),
        ]);
    }
}
