<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        $grados = [
            ['nombre' => 'Transición', 'tipo' => 'preescolar'],
            ['nombre' => 'Primero', 'tipo' => 'primaria'],
            ['nombre' => 'Segundo', 'tipo' => 'primaria'],
            ['nombre' => 'Tercero', 'tipo' => 'primaria'],
            ['nombre' => 'Cuarto', 'tipo' => 'primaria'],
            ['nombre' => 'Quinto', 'tipo' => 'primaria'],
        ];

        foreach ($grados as $grado) {
            Grado::create($grado);
        }
    }
} 