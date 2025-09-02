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
            
        // Obtener logros históricos
        $logrosHistoricos = HistoricoLogro::where('estudiante_id', $estudianteId)
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
