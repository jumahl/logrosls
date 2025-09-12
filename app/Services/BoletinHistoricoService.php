<?php

namespace App\Services;

use App\Models\HistoricoEstudiante;
use App\Models\HistoricoDesempeno;
use App\Models\HistoricoLogro;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class BoletinHistoricoService
{
    /**
     * Generar boletín histórico específicamente del segundo periodo
     */
    public function generarBoletinHistoricoSegundoPeriodo($estudianteId, $anioEscolar)
    {
        // Obtener datos históricos del estudiante
        $historicoEstudiante = HistoricoEstudiante::where('estudiante_id', $estudianteId)
            ->where('anio_escolar', $anioEscolar)
            ->first();
            
        if (!$historicoEstudiante) {
            throw new \Exception("No se encontraron datos históricos del estudiante para el año {$anioEscolar}");
        }
        
        // Obtener desempeños históricos solo del segundo periodo
        $desempenosHistoricos = HistoricoDesempeno::where('estudiante_id', $estudianteId)
            ->where('anio_escolar', $anioEscolar)
            ->where(function ($query) {
                $query->where('periodo_corte', 2)
                      ->orWhere('periodo_numero', 2)
                      ->orWhere('periodo_nombre', 'like', '%segundo%')
                      ->orWhere('periodo_nombre', 'like', '%2%');
            })
            ->get();
            
        if ($desempenosHistoricos->isEmpty()) {
            throw new \Exception("No se encontraron desempeños del segundo periodo para el año {$anioEscolar}");
        }
        
        // Obtener logros históricos usando el nombre del estudiante y año
        $logrosHistoricos = HistoricoLogro::where('estudiante_nombre', $historicoEstudiante->estudiante_nombre)
            ->where('estudiante_apellido', $historicoEstudiante->estudiante_apellido)
            ->where('estudiante_documento', $historicoEstudiante->estudiante_documento)
            ->where('anio_escolar', $anioEscolar)
            ->where('alcanzado', true)
            ->get();
        
        // Agrupar desempeños por materia
        $desempenosPorMateria = $desempenosHistoricos->groupBy('materia_nombre');
        
        // Calcular promedios por materia
        $promediosPorMateria = [];
        foreach ($desempenosPorMateria as $materia => $desempenos) {
            $promedio = $desempenos->avg(function ($desempeno) {
                return $this->convertirDesempenoANumero($desempeno->nivel_desempeno);
            });
            $promediosPorMateria[$materia] = round($promedio, 2);
        }
        
        // Generar PDF
        $pdf = Pdf::loadView('boletines.historico_segundo_periodo', [
            'historicoEstudiante' => $historicoEstudiante,
            'anioEscolar' => $anioEscolar,
            'periodo' => 'Segundo Periodo',
            'desempenosPorMateria' => $desempenosPorMateria,
            'logrosHistoricos' => $logrosHistoricos,
            'promediosPorMateria' => $promediosPorMateria,
        ]);
        
        return $pdf;
    }
    
    /**
     * Generar múltiples boletines del segundo periodo en un ZIP
     */
    public function generarBoletinesSegundoPeriodoMasivo(Collection $registros)
    {
        $zip = new \ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'boletines_segundo_periodo');
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($registros as $registro) {
                try {
                    $pdf = $this->generarBoletinHistoricoSegundoPeriodo($registro->estudiante_id, $registro->anio_escolar);
                    $filename = "boletin_2do_periodo_{$registro->estudiante_apellido}_{$registro->estudiante_nombre}_{$registro->anio_escolar}.pdf";
                    $zip->addFromString($filename, $pdf->output());
                } catch (\Exception $e) {
                    // Continuar con el siguiente si hay error
                    continue;
                }
            }
            $zip->close();
        }
        
        return $zipPath;
    }
    
    /**
     * Generar boletín histórico para un estudiante y año específico
     */
    public function generarBoletinHistorico($estudianteId, $anioEscolar)
    {
        // Obtener datos históricos del estudiante
        $historicoEstudiante = HistoricoEstudiante::where('estudiante_id', $estudianteId)
            ->where('anio_escolar', $anioEscolar)
            ->first();
            
        if (!$historicoEstudiante) {
            throw new \Exception("No se encontraron datos históricos del estudiante para el año {$anioEscolar}");
        }
        
        // Obtener desempeños históricos
        $desempenosHistoricos = HistoricoDesempeno::where('estudiante_id', $estudianteId)
            ->where('anio_escolar', $anioEscolar)
            ->get();
            
        // Obtener logros históricos usando el nombre del estudiante y año
        $logrosHistoricos = HistoricoLogro::where('estudiante_nombre', $historicoEstudiante->estudiante_nombre)
            ->where('estudiante_apellido', $historicoEstudiante->estudiante_apellido)
            ->where('estudiante_documento', $historicoEstudiante->estudiante_documento)
            ->where('anio_escolar', $anioEscolar)
            ->get();
        
        // Agrupar desempeños por materia
        $desempenosPorMateria = $desempenosHistoricos->groupBy('materia_nombre');
        
        // Agrupar logros por materia
        $logrosPorMateria = $logrosHistoricos->groupBy(function ($logro) {
            // Necesitamos buscar la materia del logro, pero en datos históricos
            // podemos usar una aproximación basada en el nombre del logro
            return $this->extraerMateriaDeLLogro($logro);
        });
        
        // Calcular promedios por materia
        $promediosPorMateria = [];
        foreach ($desempenosPorMateria as $materia => $desempenos) {
            $promedio = $desempenos->avg(function ($desempeno) {
                return $this->convertirDesempenoANumero($desempeno->nivel_desempeno);
            });
            $promediosPorMateria[$materia] = round($promedio, 2);
        }
        
        // Generar PDF
        $pdf = Pdf::loadView('boletines.historico', [
            'historicoEstudiante' => $historicoEstudiante,
            'anioEscolar' => $anioEscolar,
            'desempenosPorMateria' => $desempenosPorMateria,
            'logrosPorMateria' => $logrosPorMateria,
            'promediosPorMateria' => $promediosPorMateria,
        ]);
        
        return $pdf;
    }
    
    /**
     * Generar múltiples boletines históricos en un ZIP
     */
    public function generarBoletinesHistoricosMasivo(Collection $registros)
    {
        $zip = new \ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'boletines_historicos');
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($registros as $registro) {
                try {
                    $pdf = $this->generarBoletinHistorico($registro->estudiante_id, $registro->anio_escolar);
                    $filename = "boletin_historico_{$registro->estudiante_nombre}_{$registro->estudiante_apellido}_{$registro->anio_escolar}.pdf";
                    $zip->addFromString($filename, $pdf->output());
                } catch (\Exception $e) {
                    // Continuar con el siguiente si hay error
                    continue;
                }
            }
            $zip->close();
        }
        
        return $zipPath;
    }
    
    /**
     * Convertir nivel de desempeño a número
     */
    private function convertirDesempenoANumero($nivel)
    {
        return match($nivel) {
            'E' => 5.0,
            'S' => 4.0,
            'A' => 3.0,
            'I' => 2.0,
            default => 0.0
        };
    }
    
    /**
     * Extraer materia del logro (aproximación)
     */
    private function extraerMateriaDeLLogro($logro)
    {
        // Esta es una aproximación, idealmente deberíamos tener el nombre de la materia
        // guardado en el registro histórico del logro
        return $logro->logro_descripcion ?? 'Sin clasificar';
    }
}
