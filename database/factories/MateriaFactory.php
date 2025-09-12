<?php

namespace Database\Factories;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Materia>
 */
class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $materias = [
            'Matemáticas',
            'Lenguaje',
            'Ciencias Naturales',
            'Ciencias Sociales',
            'Inglés',
            'Educación Física',
            'Artística',
            'Tecnología e Informática',
            'Ética y Valores',
            'Religión',
            'Química',
            'Física',
            'Biología',
            'Filosofía',
            'Economía',
            'Política'
        ];

        $materia = $this->faker->randomElement($materias);
        
        return [
            'nombre' => $materia,
            'codigo' => $this->generateCodigo($materia),
            'descripcion' => $this->faker->sentence(10),
            'docente_id' => User::factory(),
            'activa' => true,
            'area' => $this->faker->randomElement(array_keys(Materia::getAreas())),
        ];
    }

    /**
     * Generate a unique code for the materia.
     */
    private function generateCodigo(string $materia): string
    {
        $words = explode(' ', $materia);
        $code = '';
        
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 3));
        }
        
        return $code . '-' . $this->faker->numberBetween(100, 999);
    }

    /**
     * Indicate that the materia is active.
     */
    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'activa' => true,
        ]);
    }

    /**
     * Indicate that the materia is inactive.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activa' => false,
        ]);
    }

    /**
     * Set a specific docente for the materia.
     */
    public function withDocente(User $docente): static
    {
        return $this->state(fn (array $attributes) => [
            'docente_id' => $docente->id,
        ]);
    }

    /**
     * Create a basic subject for primary education.
     */
    public function primaria(): static
    {
        $materiasPrimaria = [
            'Matemáticas',
            'Lenguaje',
            'Ciencias Naturales',
            'Ciencias Sociales',
            'Inglés',
            'Educación Física',
            'Artística',
            'Ética y Valores',
            'Religión'
        ];

        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement($materiasPrimaria),
        ]);
    }

    /**
     * Create a subject for secondary education.
     */
    public function secundaria(): static
    {
        $materiasSecundaria = [
            'Matemáticas',
            'Lenguaje',
            'Ciencias Naturales',
            'Ciencias Sociales',
            'Inglés',
            'Educación Física',
            'Artística',
            'Tecnología e Informática',
            'Ética y Valores',
            'Religión'
        ];

        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement($materiasSecundaria),
        ]);
    }

    /**
     * Create a subject for media académica.
     */
    public function mediaAcademica(): static
    {
        $materiasMedia = [
            'Matemáticas',
            'Lenguaje',
            'Química',
            'Física',
            'Biología',
            'Ciencias Sociales',
            'Inglés',
            'Filosofía',
            'Economía',
            'Política',
            'Educación Física'
        ];

        return $this->state(fn (array $attributes) => [
            'nombre' => $this->faker->randomElement($materiasMedia),
        ]);
    }
}
