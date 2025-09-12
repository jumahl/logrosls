<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        $grados = [
            // Preescolar
            ['nombre' => 'Preescolar', 'grupo' => 'A', 'tipo' => 'preescolar', 'activo' => true],
            ['nombre' => 'Preescolar', 'grupo' => 'B', 'tipo' => 'preescolar', 'activo' => true],
            ['nombre' => 'Transición', 'grupo' => 'A', 'tipo' => 'preescolar', 'activo' => true],
            ['nombre' => 'Transición', 'grupo' => 'B', 'tipo' => 'preescolar', 'activo' => true],
            
            // Primaria
            ['nombre' => 'Primero', 'grupo' => 'A', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Primero', 'grupo' => 'B', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Segundo', 'grupo' => 'A', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Segundo', 'grupo' => 'B', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Tercero', 'grupo' => 'A', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Tercero', 'grupo' => 'B', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Cuarto', 'grupo' => 'A', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Cuarto', 'grupo' => 'B', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Quinto', 'grupo' => 'A', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Quinto', 'grupo' => 'B', 'tipo' => 'primaria', 'activo' => true],
            
            // Secundaria
            ['nombre' => 'Sexto', 'grupo' => 'A', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Sexto', 'grupo' => 'B', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Séptimo', 'grupo' => 'A', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Séptimo', 'grupo' => 'B', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Octavo', 'grupo' => 'A', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Octavo', 'grupo' => 'B', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Noveno', 'grupo' => 'A', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Noveno', 'grupo' => 'B', 'tipo' => 'secundaria', 'activo' => true],
            
            // Media Académica
            ['nombre' => 'Décimo', 'grupo' => 'A', 'tipo' => 'media_academica', 'activo' => true],
            ['nombre' => 'Décimo', 'grupo' => 'B', 'tipo' => 'media_academica', 'activo' => true],
            ['nombre' => 'Once', 'grupo' => 'A', 'tipo' => 'media_academica', 'activo' => true],
            ['nombre' => 'Once', 'grupo' => 'B', 'tipo' => 'media_academica', 'activo' => true],
        ];

        foreach ($grados as $grado) {
            Grado::firstOrCreate(
                ['nombre' => $grado['nombre'], 'grupo' => $grado['grupo']],
                $grado
            );
        }

        $this->command->info('Grados creados exitosamente: ' . count($grados) . ' grados con grupos desde preescolar hasta media académica.');
    }
} 