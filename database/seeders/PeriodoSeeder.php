<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Periodo;

class PeriodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $añoActual = date('Y');
        
        // Crear períodos para el año actual
        $periodos = [
            // Primer Período - Primer Corte
            [
                'nombre' => 'Primer Período',
                'corte' => 'Primer Corte',
                'año_escolar' => $añoActual,
                'numero_periodo' => 1,
                'fecha_inicio' => "{$añoActual}-02-01",
                'fecha_fin' => "{$añoActual}-03-15",
                'activo' => false,
            ],
            // Primer Período - Segundo Corte
            [
                'nombre' => 'Primer Período',
                'corte' => 'Segundo Corte',
                'año_escolar' => $añoActual,
                'numero_periodo' => 1,
                'fecha_inicio' => "{$añoActual}-03-16",
                'fecha_fin' => "{$añoActual}-06-30",
                'activo' => false,
            ],
            // Segundo Período - Primer Corte
            [
                'nombre' => 'Segundo Período',
                'corte' => 'Primer Corte',
                'año_escolar' => $añoActual,
                'numero_periodo' => 2,
                'fecha_inicio' => "{$añoActual}-07-01",
                'fecha_fin' => "{$añoActual}-09-15",
                'activo' => false,
            ],
            // Segundo Período - Segundo Corte
            [
                'nombre' => 'Segundo Período',
                'corte' => 'Segundo Corte',
                'año_escolar' => $añoActual,
                'numero_periodo' => 2,
                'fecha_inicio' => "{$añoActual}-09-16",
                'fecha_fin' => "{$añoActual}-11-30",
                'activo' => false,
            ],
        ];

        foreach ($periodos as $periodo) {
            Periodo::create($periodo);
        }

        // Activar el primer corte del primer período por defecto
        Periodo::where('corte', 'Primer Corte')
            ->where('numero_periodo', 1)
            ->where('año_escolar', $añoActual)
            ->update(['activo' => true]);

        $this->command->info('Períodos creados exitosamente con el nuevo sistema de cortes.');
    }
} 