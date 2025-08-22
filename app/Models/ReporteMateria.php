<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteMateria extends Model
{
    use HasFactory;
    
    // Esta es una clase de visualización que no tiene tabla real
    protected $table = 'estudiante_logros';
    
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class);
    }
    
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
    
    public function materia()
    {
        return $this->logro->materia;
    }
    
    // Método para obtener todos los logros de un estudiante en una materia específica para un periodo
    public static function getLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        return EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
            $query->where('materia_id', $materiaId);
        })
        ->where('estudiante_id', $estudianteId)
        ->where('periodo_id', $periodoId)
        ->get();
    }
    
    // Método para contar los logros por materia
    public static function countLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        return EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
            $query->where('materia_id', $materiaId);
        })
        ->where('estudiante_id', $estudianteId)
        ->where('periodo_id', $periodoId)
        ->count();
    }
    
    /**
     * Obtener el promedio de nivel de desempeño para una materia
     */
    public static function getNivelDesempenoPromedio($estudianteId, $materiaId, $periodoId)
    {
        $logros = self::getLogrosPorMateria($estudianteId, $materiaId, $periodoId);
        
        if ($logros->isEmpty()) {
            return null;
        }
        
        // Calcular el nivel de desempeño más común
        $nivelCounts = [
            'E' => 0,
            'S' => 0,
            'A' => 0,
            'I' => 0
        ];
        
        foreach ($logros as $logro) {
            $nivelCounts[$logro->nivel_desempeno]++;
        }
        
        // Determinar el nivel de desempeño más frecuente
        arsort($nivelCounts);
        return key($nivelCounts);
    }
}
