<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\Periodo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PDF;

class GenerarBoletines extends Command
{
    protected $signature = 'boletines:generar {periodo_id}';
    protected $description = 'Genera los boletines académicos para un periodo específico';

    public function handle()
    {
        $periodoId = $this->argument('periodo_id');
        $periodo = Periodo::findOrFail($periodoId);

        $this->info("Generando boletines para el periodo: {$periodo->nombre}");

        $estudiantes = Estudiante::with(['grado', 'estudianteLogros.logro.materia.docente', 'estudianteLogros.periodo'])
            ->where('activo', true)
            ->get();

        foreach ($estudiantes as $estudiante) {
            $this->info("Procesando boletín para: {$estudiante->nombre}");

            $notas = $estudiante->estudianteLogros
                ->groupBy(function ($nota) {
                    return $nota->logro->materia->nombre;
                });

            if ($notas->isEmpty()) {
                continue;
            }

            $periodo = $notas->first()->first()->periodo;

            $pdf = PDF::loadView('boletines.academico', [
                'estudiante' => $estudiante,
                'notas' => $notas,
                'periodo' => $periodo
            ]);

            $filename = "boletin_{$estudiante->documento}_{$periodo->nombre}.pdf";
            $pdf->save(storage_path("app/public/boletines/{$filename}"));

            $this->info("Boletín generado: {$filename}");
        }

        $this->info('Proceso de generación de boletines completado.');
    }
} 