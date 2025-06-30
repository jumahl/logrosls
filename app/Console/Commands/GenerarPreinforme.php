<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\Periodo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarPreinforme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boletin:preinforme {estudiante_id?} {periodo_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera preinformes para el primer corte de un período';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estudianteId = $this->argument('estudiante_id');
        $periodoId = $this->argument('periodo_id');

        // Si no se especifica período, usar el período activo del primer corte
        if (!$periodoId) {
            $periodo = Periodo::where('activo', true)
                ->where('corte', 'Primer Corte')
                ->first();
            
            if (!$periodo) {
                $this->error('No hay un período activo del primer corte.');
                return 1;
            }
            $periodoId = $periodo->id;
        } else {
            $periodo = Periodo::find($periodoId);
            if (!$periodo) {
                $this->error('Período no encontrado.');
                return 1;
            }
            if ($periodo->corte !== 'Primer Corte') {
                $this->error('El período especificado no es del primer corte.');
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

            $this->info("Generando preinformes para {$estudiantes->count()} estudiantes...");
            
            foreach ($estudiantes as $estudiante) {
                $this->generarPreinformeEstudiante($estudiante, $periodo);
            }

            $this->info('Preinformes generados exitosamente.');
            return 0;
        }

        // Generar para un estudiante específico
        $estudiante = Estudiante::find($estudianteId);
        if (!$estudiante) {
            $this->error('Estudiante no encontrado.');
            return 1;
        }

        $this->generarPreinformeEstudiante($estudiante, $periodo);
        $this->info('Preinforme generado exitosamente.');
        return 0;
    }

    private function generarPreinformeEstudiante(Estudiante $estudiante, Periodo $periodo)
    {
        // Obtener logros del estudiante en el período
        $logros = $estudiante->estudianteLogros()
            ->where('periodo_id', $periodo->id)
            ->with(['logro.materia.docente', 'logro.materia.grados'])
            ->get()
            ->groupBy(function ($logro) {
                return $logro->logro->materia->nombre;
            });

        if ($logros->isEmpty()) {
            $this->warn("El estudiante {$estudiante->nombre} no tiene logros en el período {$periodo->periodo_completo}.");
            return;
        }

        // Generar PDF
        $pdf = Pdf::loadView('boletines.preinforme', [
            'estudiante' => $estudiante,
            'periodo' => $periodo,
            'logros' => $logros,
        ]);

        // Guardar archivo
        $filename = "preinforme_{$estudiante->id}_{$periodo->id}.pdf";
        $path = "boletines/preinformes/{$filename}";
        
        Storage::put($path, $pdf->output());

        $this->line("Preinforme generado: {$path}");
    }
} 