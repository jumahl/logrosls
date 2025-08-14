<?php

namespace Database\Factories;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'director_grado_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user as director of a grade.
     */
    public function directorGrado(?Grado $grado = null): static
    {
        return $this->state(fn (array $attributes) => [
            'director_grado_id' => $grado?->id ?? Grado::factory(),
        ]);
    }

    /**
     * Create a user as admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ])->afterCreating(function ($user) {
            $user->assignRole('admin');
        });
    }

    /**
     * Create a user as profesor.
     */
    public function profesor(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Profesor ' . fake()->lastName(),
        ])->afterCreating(function ($user) {
            $user->assignRole('profesor');
        });
    }

    /**
     * Create a user as profesor with director de grupo role.
     */
    public function profesorDirector(?Grado $grado = null): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Profesor Director ' . fake()->lastName(),
            'director_grado_id' => $grado?->id ?? Grado::factory(),
        ])->afterCreating(function ($user) {
            $user->assignRole('profesor');
        });
    }

    /**
     * Create a user with a specific password.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Create a user with default test password.
     */
    public function withTestPassword(): static
    {
        return $this->withPassword('test123');
    }
}
