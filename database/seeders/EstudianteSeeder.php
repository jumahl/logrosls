<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use Illuminate\Database\Seeder;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $estudiantes = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'González',
                'documento' => '1001234567',
                'genero' => 'masculino',
                'fecha_nacimiento' => '2018-05-15',
                'direccion' => 'Calle 123 #45-67',
                'telefono' => '3001234567',
                'email' => 'carlos.gonzalez@estudiante.com',
                'grado_id' => 1, // Transición
                'activo' => true,
            ],
            [
                'nombre' => 'Laura',
                'apellido' => 'Martínez',
                'documento' => '1002345678',
                'genero' => 'femenino',
                'fecha_nacimiento' => '2018-03-20',
                'direccion' => 'Carrera 78 #90-12',
                'telefono' => '3002345678',
                'email' => 'laura.martinez@estudiante.com',
                'grado_id' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'Juan',
                'apellido' => 'Rodríguez',
                'documento' => '1003456789',
                'genero' => 'masculino',
                'fecha_nacimiento' => '2018-07-10',
                'direccion' => 'Avenida 5 #23-45',
                'telefono' => '3003456789',
                'email' => 'juan.rodriguez@estudiante.com',
                'grado_id' => 1,
                'activo' => true,
            ],
        ];

        foreach ($estudiantes as $estudiante) {
            Estudiante::create($estudiante);
        }
    }
} 