<?php

namespace Database\Seeders;

use App\Models\Periodo;
use Illuminate\Database\Seeder;

class PeriodoSeeder extends Seeder
{
    public function run(): void
    {
        $periodos = [
            [
                'nombre' => 'Primer Periodo',
                'fecha_inicio' => '2024-01-15',
                'fecha_fin' => '2024-04-15',
                'activo' => true
            ],
            [
                'nombre' => 'Segundo Periodo',
                'fecha_inicio' => '2024-04-16',
                'fecha_fin' => '2024-07-15',
                'activo' => false
            ],
            [
                'nombre' => 'Tercer Periodo',
                'fecha_inicio' => '2024-07-16',
                'fecha_fin' => '2024-10-15',
                'activo' => false
            ],
            [
                'nombre' => 'Cuarto Periodo',
                'fecha_inicio' => '2024-10-16',
                'fecha_fin' => '2024-12-15',
                'activo' => false
            ],
        ];

        foreach ($periodos as $periodo) {
            Periodo::create($periodo);
        }
    }
} 