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
            ['nombre' => 'Preescolar', 'tipo' => 'preescolar', 'activo' => true],
            ['nombre' => 'Transición', 'tipo' => 'preescolar', 'activo' => true],
            
            // Primaria
            ['nombre' => 'Primero', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Segundo', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Tercero', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Cuarto', 'tipo' => 'primaria', 'activo' => true],
            ['nombre' => 'Quinto', 'tipo' => 'primaria', 'activo' => true],
            
            // Secundaria
            ['nombre' => 'Sexto', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Séptimo', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Octavo', 'tipo' => 'secundaria', 'activo' => true],
            ['nombre' => 'Noveno', 'tipo' => 'secundaria', 'activo' => true],
            
            // Media Académica
            ['nombre' => 'Décimo', 'tipo' => 'media_academica', 'activo' => true],
            ['nombre' => 'Once', 'tipo' => 'media_academica', 'activo' => true],
        ];

        foreach ($grados as $grado) {
            Grado::firstOrCreate(
                ['nombre' => $grado['nombre']],
                $grado
            );
        }

        $this->command->info('Grados creados exitosamente: ' . count($grados) . ' grados desde preescolar hasta media académica.');
    }
} 