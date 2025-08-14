<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->firstName(),
            'apellido' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'documento' => $this->faker->unique()->numerify('##########'),
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->optional(0.7)->safeEmail(), // 70% tienen email
            'grado_id' => Grado::factory(),
            'activo' => $this->faker->boolean(95), // 95% activos
        ];
    }

    /**
     * Indicate that the estudiante is active.
     */
    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    /**
     * Indicate that the estudiante is inactive.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Set a specific grado for the estudiante.
     */
    public function withGrado(Grado $grado): static
    {
        return $this->state(fn (array $attributes) => [
            'grado_id' => $grado->id,
        ]);
    }

    /**
     * Create a student without email.
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * Create a student without phone.
     */
    public function withoutPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'telefono' => null,
        ]);
    }

    /**
     * Create a young student (5-8 years).
     */
    public function young(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-8 years', '-5 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Create an older student (15-18 years).
     */
    public function older(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => $this->faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
        ]);
    }

    /**
     * Create student with minimal required data.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'direccion' => null,
            'telefono' => null,
            'email' => null,
        ]);
    }
}
