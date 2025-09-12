<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use Illuminate\Database\Seeder;

class AnioEscolarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anios = [
            [
                'anio' => 2024,
                'activo' => false,
                'finalizado' => true,
                'fecha_inicio' => '2024-02-01',
                'fecha_fin' => '2024-11-30',
                'observaciones' => 'Año escolar anterior ya finalizado'
            ],
            [
                'anio' => 2025,
                'activo' => true,
                'finalizado' => false,
                'fecha_inicio' => '2025-02-01',
                'fecha_fin' => '2025-11-30',
                'observaciones' => 'Año escolar actual en curso'
            ],
            [
                'anio' => 2026,
                'activo' => false,
                'finalizado' => false,
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-11-30',
                'observaciones' => 'Próximo año escolar'
            ]
        ];

        foreach ($anios as $anio) {
            AnioEscolar::firstOrCreate(['anio' => $anio['anio']], $anio);
        }

        $this->command->info('✅ Años escolares creados: 2024 (finalizado), 2025 (activo), 2026 (preparado)');
    }
}
