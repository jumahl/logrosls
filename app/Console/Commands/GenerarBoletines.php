<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\Periodo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarBoletines extends Command
{
    protected $signature = 'boletin:generar {estudiante_id?} {periodo_id?}';
    protected $description = 'Genera boletines académicos para el segundo corte de un período';

    public function handle()
    {
        $estudianteId = $this->argument('estudiante_id');
        $periodoId = $this->argument('periodo_id');

        // Si no se especifica período, usar el período activo del segundo corte
        if (!$periodoId) {
            $periodo = Periodo::where('activo', true)
                ->where('corte', 'Segundo Corte')
                ->first();
            
            if (!$periodo) {
                $this->error('No hay un período activo del segundo corte.');
                return 1;
            }
            $periodoId = $periodo->id;
        } else {
            $periodo = Periodo::find($periodoId);
            if (!$periodo) {
                $this->error('Período no encontrado.');
                return 1;
            }
            if ($periodo->corte !== 'Segundo Corte') {
                $this->error('El período especificado no es del segundo corte.');
                return 1;
            }
        }

        // Si no se especifica estudiante, generar para todos los estudiantes del período
        if (!$estudianteId) {
            $estudiantes = Estudiante::whereHas('estudianteLogros', function ($query) use ($periodoId) {
                $query->where('periodo_id', $periodoId);
            })->get();

            if ($estudiantes->isEmpty()) {
                $this->error('No hay estudiantes con logros en este período.');
                return 1;
            }

            $this->info("Generando boletines para {$estudiantes->count()} estudiantes...");
            
            foreach ($estudiantes as $estudiante) {
                $this->generarBoletinEstudiante($estudiante, $periodo);
            }

            $this->info('Boletines generados exitosamente.');
            return 0;
        }

        // Generar para un estudiante específico
        $estudiante = Estudiante::find($estudianteId);
        if (!$estudiante) {
            $this->error('Estudiante no encontrado.');
            return 1;
        }

        $this->generarBoletinEstudiante($estudiante, $periodo);
        $this->info('Boletín generado exitosamente.');
        return 0;
    }

    private function generarBoletinEstudiante(Estudiante $estudiante, Periodo $periodo)
    {
        // Obtener el período anterior (primer corte del mismo período)
        $periodoAnterior = $periodo->periodo_anterior;
        
        // Obtener logros del primer corte
        $logrosPrimerCorte = collect();
        if ($periodoAnterior) {
            $logrosPrimerCorte = $estudiante->estudianteLogros()
                ->where('periodo_id', $periodoAnterior->id)
                ->with(['logro.materia.docente', 'logro.materia.grados'])
                ->get();
        }

        // Obtener logros del segundo corte
        $logrosSegundoCorte = $estudiante->estudianteLogros()
            ->where('periodo_id', $periodo->id)
            ->with(['logro.materia.docente', 'logro.materia.grados'])
            ->get();

        // Combinar logros de ambos cortes
        $todosLosLogros = $logrosPrimerCorte->concat($logrosSegundoCorte);
        
        // Agrupar por materia
        $logrosPorMateria = $todosLosLogros->groupBy(function ($logro) {
            return $logro->logro->materia->nombre;
        });

        if ($logrosPorMateria->isEmpty()) {
            $this->warn("El estudiante {$estudiante->nombre} no tiene logros en el período {$periodo->periodo_completo}.");
            return;
        }

        // Calcular promedios por materia
        $promediosPorMateria = [];
        foreach ($logrosPorMateria as $materia => $logros) {
            $promedio = $logros->avg('valor_numerico');
            $promediosPorMateria[$materia] = $promedio;
        }

        // Generar PDF
        $pdf = Pdf::loadView('boletines.academico', [
            'estudiante' => $estudiante,
            'periodo' => $periodo,
            'periodoAnterior' => $periodoAnterior,
            'logrosPrimerCorte' => $logrosPrimerCorte,
            'logrosSegundoCorte' => $logrosSegundoCorte,
            'logrosPorMateria' => $logrosPorMateria,
            'promediosPorMateria' => $promediosPorMateria,
        ]);

        // Guardar archivo
        $filename = "boletin_{$estudiante->id}_{$periodo->id}.pdf";
        $path = "boletines/boletines/{$filename}";
        
        Storage::put($path, $pdf->output());

        $this->line("Boletín generado: {$path}");
    }
} 