<?php

namespace Database\Seeders;

use App\Models\EstudianteLogro;
use Illuminate\Database\Seeder;

class EstudianteLogroSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = ['E', 'S', 'A', 'I']; // Nuevos valores: E=Excelente, S=Sobresaliente, A=Aceptable, I=Insuficiente
        $estudiantes = [1, 2, 3]; // IDs de los estudiantes
        $logros = [1, 2, 3, 4, 5, 6, 7, 8, 9]; // IDs de los logros
        $periodo_id = 1; // Primer período

        foreach ($estudiantes as $estudiante_id) {
            foreach ($logros as $logro_id) {
                EstudianteLogro::create([
                    'estudiante_id' => $estudiante_id,
                    'logro_id' => $logro_id,
                    'periodo_id' => $periodo_id,
                    'nivel_desempeno' => $niveles[array_rand($niveles)],
                    'observaciones' => 'Observaciones de prueba para el logro ' . $logro_id,
                    'fecha_asignacion' => now(),
                ]);
            }
        }
    }
} 