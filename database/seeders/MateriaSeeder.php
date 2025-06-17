<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        // Crear docentes
        $docentes = [
            [
                'name' => 'María Rodríguez',
                'email' => 'maria.rodriguez@escuela.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@escuela.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@escuela.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($docentes as $docente) {
            User::create($docente);
        }

        // Crear materias
        $materias = [
            [
                'nombre' => 'Matemáticas',
                'codigo' => 'MAT001',
                'descripcion' => 'Matemáticas básicas y avanzadas',
                'grado_id' => 1, // Transición
                'docente_id' => 1, // María Rodríguez
                'activa' => true,
            ],
            [
                'nombre' => 'Lenguaje',
                'codigo' => 'LEN001',
                'descripcion' => 'Comunicación y expresión',
                'grado_id' => 1,
                'docente_id' => 2, // Juan Pérez
                'activa' => true,
            ],
            [
                'nombre' => 'Ciencias Naturales',
                'codigo' => 'CIE001',
                'descripcion' => 'Exploración del mundo natural',
                'grado_id' => 1,
                'docente_id' => 3, // Ana Martínez
                'activa' => true,
            ],
        ];

        foreach ($materias as $materia) {
            Materia::create($materia);
        }
    }
} 