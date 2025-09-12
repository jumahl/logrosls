<?php

namespace Database\Seeders;

use App\Models\EstudianteLogro;
use App\Models\DesempenoMateria;
use App\Models\Logro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstudianteLogroSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla existente usando DELETE en lugar de TRUNCATE
        // debido a foreign key constraints
        EstudianteLogro::query()->delete();

        // Obtener todos los desempeños de materia existentes
        $desempenos = DesempenoMateria::with(['materia.logros'])->get();

        if ($desempenos->isEmpty()) {
            $this->command->warn('No se encontraron desempeños de materia. Ejecutar DesempenoMateriaSeeder primero.');
            return;
        }

        $logrosAsignados = [];

        foreach ($desempenos as $desempeno) {
            // Obtener logros disponibles para esta materia
            $logrosDisponibles = $desempeno->materia->logros;

            if ($logrosDisponibles->isEmpty()) {
                continue;
            }

            // Asignar entre 2 y 5 logros por desempeño (aleatorio)
            $cantidadLogros = rand(2, min(5, $logrosDisponibles->count()));
            $logrosSeleccionados = $logrosDisponibles->random($cantidadLogros);

            foreach ($logrosSeleccionados as $logro) {
                // Determinar si el logro fue alcanzado basado en el nivel de desempeño
                $alcanzado = $this->determinarSiAlcanzo($desempeno->nivel_desempeno);

                $logrosAsignados[] = [
                    'logro_id' => $logro->id,
                    'desempeno_materia_id' => $desempeno->id,
                    'alcanzado' => $alcanzado,
                    'created_at' => $desempeno->created_at,
                    'updated_at' => $desempeno->updated_at,
                ];
            }
        }

        // Insertar en chunks para mejor rendimiento
        $chunks = array_chunk($logrosAsignados, 500);
        foreach ($chunks as $chunk) {
            DB::table('estudiante_logros')->insert($chunk);
        }

        $this->command->info('✅ Creados ' . count($logrosAsignados) . ' logros asignados a desempeños');
    }

    /**
     * Determinar si un logro fue alcanzado basado en el nivel de desempeño.
     */
    private function determinarSiAlcanzo(string $nivelDesempeno): bool
    {
        return match($nivelDesempeno) {
            'E' => rand(1, 100) <= 95, // 95% de probabilidad con Excelente
            'S' => rand(1, 100) <= 85, // 85% de probabilidad con Sobresaliente
            'A' => rand(1, 100) <= 65, // 65% de probabilidad con Aceptable
            'I' => rand(1, 100) <= 25, // 25% de probabilidad con Insuficiente
            default => false
        };
    }
} 