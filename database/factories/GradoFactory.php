<?php

namespace Database\Factories;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grado>
 */
class GradoFactory extends Factory
{
    protected $model = Grado::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = ['preescolar', 'primaria', 'secundaria', 'media_academica'];
        $nombres = [
            'preescolar' => ['Prekínder', 'Kínder', 'Transición'],
            'primaria' => ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto'],
            'secundaria' => ['Sexto', 'Séptimo', 'Octavo', 'Noveno'],
            'media_academica' => ['Décimo', 'Once']
        ];

        $tipo = $this->faker->randomElement($tipos);
        $nombre = $this->faker->randomElement($nombres[$tipo]);

        return [
            'nombre' => $nombre,
            'tipo' => $tipo,
            'activo' => true, // Activo por defecto
        ];
    }

    /**
     * Indicate that the grado is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the grado is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Create a preescolar grado.
     */
    public function preescolar(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement(['Prekínder', 'Kínder', 'Transición']),
            'tipo' => 'preescolar',
        ]);
    }

    /**
     * Create a primaria grado.
     */
    public function primaria(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement(['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto']),
            'tipo' => 'primaria',
        ]);
    }

    /**
     * Create a secundaria grado.
     */
    public function secundaria(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement(['Sexto', 'Séptimo', 'Octavo', 'Noveno']),
            'tipo' => 'secundaria',
        ]);
    }

    /**
     * Create a media académica grado.
     */
    public function mediaAcademica(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement(['Décimo', 'Once']),
            'tipo' => 'media_academica',
        ]);
    }
}
