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
            // Optimización: usar eager loading y chunking para manejar grandes volúmenes
            $estudiantesQuery = Estudiante::select(['id', 'nombre', 'apellido', 'documento', 'grado_id'])
                ->with(['grado:id,nombre,grupo'])
                ->whereHas('estudianteLogros', function ($query) use ($periodoId) {
                    $query->select('estudiante_id')->where('periodo_id', $periodoId);
                });

            $totalEstudiantes = $estudiantesQuery->count();
            
            if ($totalEstudiantes === 0) {
                $this->error('No hay estudiantes con logros en este período.');
                return 1;
            }

            $this->info("Generando boletines para {$totalEstudiantes} estudiantes...");
            
            $bar = $this->output->createProgressBar($totalEstudiantes);
            $bar->start();
            
            // Procesar en chunks de 50 estudiantes para optimizar memoria
            $estudiantesQuery->chunk(50, function ($estudiantes) use ($periodo, $bar) {
                foreach ($estudiantes as $estudiante) {
                    try {
                        $this->generarBoletinEstudiante($estudiante, $periodo);
                        $bar->advance();
                    } catch (\Exception $e) {
                        $this->error("\nError generando boletín para {$estudiante->nombre}: {$e->getMessage()}");
                    }
                }
            });
            
            $bar->finish();
            $this->newLine();
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
        // Obtener el período anterior (primer corte del mismo período) una sola vez
        $periodoAnterior = $periodo->periodo_anterior;
        
        // Optimización: cargar todos los logros necesarios en una sola consulta
        $logrosQuery = $estudiante->estudianteLogros()
            ->select(['id', 'desempeno_materia_id', 'logro_id', 'alcanzado'])
            ->with([
                'logro:id,codigo,titulo,desempeno,materia_id',
                'logro.materia:id,nombre,codigo,docente_id',
                'logro.materia.docente:id,name'
            ]);
        
        // Obtener logros de ambos períodos en una consulta
        $periodosIds = array_filter([$periodoAnterior?->id, $periodo->id]);
        $todosLosLogros = $logrosQuery->whereIn('periodo_id', $periodosIds)->get();
        
        // Separar por período después de cargar
        $logrosPrimerCorte = $periodoAnterior 
            ? $todosLosLogros->where('periodo_id', $periodoAnterior->id)
            : collect();
            
        $logrosSegundoCorte = $todosLosLogros->where('periodo_id', $periodo->id);

        // Agrupar por materia usando la relación ya cargada
        $logrosPorMateria = $todosLosLogros->groupBy(function ($logro) {
            return $logro->logro->materia->nombre;
        });

        if ($logrosPorMateria->isEmpty()) {
            $this->warn("El estudiante {$estudiante->nombre} no tiene logros en el período {$periodo->periodo_completo}.");
            return;
        }

        // Calcular promedios por materia de forma eficiente
        $promediosPorMateria = $logrosPorMateria->map(function ($logros) {
            return $logros->filter(function($logro) {
                return $logro->valor_numerico > 0;
            })->avg('valor_numerico') ?? 0;
        })->toArray();

        try {
            // Generar PDF con timeout aumentado para documentos grandes
            ini_set('max_execution_time', 120);
            
            $pdf = Pdf::loadView('boletines.academico', [
                'estudiante' => $estudiante,
                'periodo' => $periodo,
                'periodoAnterior' => $periodoAnterior,
                'logrosPrimerCorte' => $logrosPrimerCorte,
                'logrosSegundoCorte' => $logrosSegundoCorte,
                'logrosPorMateria' => $logrosPorMateria,
                'promediosPorMateria' => $promediosPorMateria,
            ]);

            // Guardar archivo con nombre único
            $fecha = now()->format('Y-m-d');
            $filename = "boletin_{$estudiante->id}_{$periodo->id}_{$fecha}.pdf";
            $path = "boletines/boletines/{$filename}";
            
            Storage::put($path, $pdf->output());

        } catch (\Exception $e) {
            $this->error("Error generando PDF para estudiante {$estudiante->id}: {$e->getMessage()}");
            throw $e;
        }
    }
} 