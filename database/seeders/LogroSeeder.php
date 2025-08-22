<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

class LogroSeeder extends Seeder
{
    public function run(): void
    {
    $logros = [
            // Logros de Matemáticas
            [
        'desempeno' => 'El estudiante puede identificar y contar números del 1 al 10 en diferentes contextos',
                'materia_id' => 1, // Matemáticas
                'codigo' => 'MAT001',
        'titulo' => 'Conteo de números',
                'activo' => true,
            ],
            [
        'desempeno' => 'El estudiante reconoce círculos, cuadrados, triángulos y rectángulos',
                'materia_id' => 1,
                'codigo' => 'MAT002',
                'titulo' => 'Formas geométricas',
                'activo' => true,
            ],
            [
        'desempeno' => 'El estudiante puede resolver problemas de suma con números del 1 al 5',
                'materia_id' => 1,
                'codigo' => 'MAT003',
                'titulo' => 'Suma y resta',
                'activo' => true,
            ],

            // Logros de Lenguaje
            [
                'desempeno' => 'El estudiante identifica y diferencia las vocales',
                'materia_id' => 2, // Lenguaje
                'codigo' => 'LEN001',
                'titulo' => 'Reconocimiento de letras',
                'activo' => true,
            ],
            [
                'desempeno' => 'El estudiante puede escribir su nombre completo de manera legible',
                'materia_id' => 2,
                'codigo' => 'LEN002',
                'titulo' => 'Escritura de palabras',
                'activo' => true,
            ],
            [
                'desempeno' => 'El estudiante sigue instrucciones orales de uno o dos pasos',
                'materia_id' => 2,
                'codigo' => 'LEN003',
                'titulo' => 'Comprensión lectora',
                'activo' => true,
            ],

            // Logros de Ciencias Naturales
            [
                'desempeno' => 'El estudiante reconoce y nombra las partes principales del cuerpo',
                'materia_id' => 3, // Ciencias Naturales
                'codigo' => 'CN001',
                'titulo' => 'Partes del cuerpo',
                'activo' => true,
            ],
            [
                'desempeno' => 'El estudiante identifica los cinco sentidos y su función',
                'materia_id' => 3,
                'codigo' => 'CN002',
                'titulo' => 'Los sentidos',
                'activo' => true,
            ],
            [
                'desempeno' => 'El estudiante diferencia animales terrestres, acuáticos y aéreos',
                'materia_id' => 3,
                'codigo' => 'CN003',
                'titulo' => 'Seres vivos',
                'activo' => true,
            ],
        ];

        foreach ($logros as $logro) {
            Logro::create($logro);
        }
    }
} 