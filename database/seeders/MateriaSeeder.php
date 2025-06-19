<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\User;
use App\Models\Grado;
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

        // Obtener el grado Transición
        $gradoTransicion = Grado::where('nombre', 'Transición')->first();

        // Crear materias
        $materias = [
            [
                'nombre' => 'Matemáticas',
                'codigo' => 'MAT001',
                'descripcion' => 'Matemáticas básicas y avanzadas',
                'docente_id' => 1, // María Rodríguez
                'activa' => true,
            ],
            [
                'nombre' => 'Lenguaje',
                'codigo' => 'LEN001',
                'descripcion' => 'Comunicación y expresión',
                'docente_id' => 2, // Juan Pérez
                'activa' => true,
            ],
            [
                'nombre' => 'Ciencias Naturales',
                'codigo' => 'CIE001',
                'descripcion' => 'Exploración del mundo natural',
                'docente_id' => 3, // Ana Martínez
                'activa' => true,
            ],
        ];

        foreach ($materias as $materiaData) {
            $materia = Materia::create($materiaData);
            
            // Asignar el grado a la materia usando la relación muchos a muchos
            if ($gradoTransicion) {
                $materia->grados()->attach($gradoTransicion->id);
            }
        }
    }
} 