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
                'competencia' => 'Reconoce y cuenta números del 1 al 10',
                'descripcion' => 'El estudiante puede identificar y contar números del 1 al 10 en diferentes contextos',
                'materia_id' => 1, // Matemáticas
                'grado_id' => 1, // Transición
                'codigo' => 'MAT001',
                'nivel' => 'Básico',
                'titulo' => 'Conteo de números',
                'indicador_desempeno' => 'Cuenta objetos del 1 al 10 en situaciones cotidianas',
            ],
            [
                'competencia' => 'Identifica figuras geométricas básicas',
                'descripcion' => 'El estudiante reconoce círculos, cuadrados, triángulos y rectángulos',
                'materia_id' => 1,
                'grado_id' => 1,
                'codigo' => 'MAT002',
                'nivel' => 'Alto',
                'titulo' => 'Formas geométricas',
                'indicador_desempeno' => 'Reconoce círculos, cuadrados y triángulos en su entorno',
            ],
            [
                'competencia' => 'Resuelve problemas simples de suma',
                'descripcion' => 'El estudiante puede resolver problemas de suma con números del 1 al 5',
                'materia_id' => 1,
                'grado_id' => 1,
                'codigo' => 'MAT003',
                'nivel' => 'Superior',
                'titulo' => 'Suma y resta',
                'indicador_desempeno' => 'Resuelve problemas de suma y resta con números del 1 al 10',
            ],

            // Logros de Lenguaje
            [
                'competencia' => 'Reconoce las vocales',
                'descripcion' => 'El estudiante identifica y diferencia las vocales',
                'materia_id' => 2, // Lenguaje
                'grado_id' => 1,
                'codigo' => 'LEN001',
                'nivel' => 'Básico',
                'titulo' => 'Reconocimiento de letras',
                'indicador_desempeno' => 'Identifica y nombra las vocales y algunas consonantes',
            ],
            [
                'competencia' => 'Escribe su nombre completo',
                'descripcion' => 'El estudiante puede escribir su nombre completo de manera legible',
                'materia_id' => 2,
                'grado_id' => 1,
                'codigo' => 'LEN002',
                'nivel' => 'Alto',
                'titulo' => 'Escritura de palabras',
                'indicador_desempeno' => 'Escribe su nombre y palabras cortas con ayuda',
            ],
            [
                'competencia' => 'Comprende instrucciones orales simples',
                'descripcion' => 'El estudiante sigue instrucciones orales de uno o dos pasos',
                'materia_id' => 2,
                'grado_id' => 1,
                'codigo' => 'LEN003',
                'nivel' => 'Superior',
                'titulo' => 'Comprensión lectora',
                'indicador_desempeno' => 'Responde preguntas sobre un texto leído',
            ],

            // Logros de Ciencias Naturales
            [
                'competencia' => 'Identifica las partes del cuerpo',
                'descripcion' => 'El estudiante reconoce y nombra las partes principales del cuerpo',
                'materia_id' => 3, // Ciencias Naturales
                'grado_id' => 1,
                'codigo' => 'CN001',
                'nivel' => 'Básico',
                'titulo' => 'Partes del cuerpo',
                'indicador_desempeno' => 'Nombra y señala las partes principales del cuerpo',
            ],
            [
                'competencia' => 'Reconoce los sentidos',
                'descripcion' => 'El estudiante identifica los cinco sentidos y su función',
                'materia_id' => 3,
                'grado_id' => 1,
                'codigo' => 'CN002',
                'nivel' => 'Alto',
                'titulo' => 'Los sentidos',
                'indicador_desempeno' => 'Identifica y describe la función de cada sentido',
            ],
            [
                'competencia' => 'Clasifica animales por su hábitat',
                'descripcion' => 'El estudiante diferencia animales terrestres, acuáticos y aéreos',
                'materia_id' => 3,
                'grado_id' => 1,
                'codigo' => 'CN003',
                'nivel' => 'Superior',
                'titulo' => 'Seres vivos',
                'indicador_desempeno' => 'Clasifica objetos y seres en vivos y no vivos',
            ],
        ];

        foreach ($logros as $logro) {
            Logro::create($logro);
        }
    }
} 