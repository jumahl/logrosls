<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase de utilidad para reportes de materias
 * Ahora usa la nueva estructura con tabla consolidada de desempeños
 */
class ReporteMateria extends Model
{
    use HasFactory;
    
    // Esta es una clase de utilidad que no tiene tabla real propia
    protected $table = 'desempenos_materia';
    
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
    
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
    
    /**
     * Obtener el desempeño de un estudiante en una materia específica para un periodo
     */
    public static function getDesempenoMateria($estudianteId, $materiaId, $periodoId)
    {
        return DesempenoMateria::where('estudiante_id', $estudianteId)
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->first();
    }
    
    /**
     * Obtener todos los logros asociados a un desempeño de materia
     */
    public static function getLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        
        if (!$desempeno) {
            return collect([]);
        }
        
        return $desempeno->estudianteLogros()->with('logro')->get();
    }
    
    /**
     * Contar los logros asociados a una materia
     */
    public static function countLogrosPorMateria($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        
        if (!$desempeno) {
            return 0;
        }
        
        return $desempeno->estudianteLogros()->count();
    }
    
    /**
     * Obtener el nivel de desempeño de una materia (ahora directamente de la tabla consolidada)
     */
    public static function getNivelDesempenoPromedio($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        
        return $desempeno?->nivel_desempeno;
    }
    
    /**
     * Obtener estadísticas completas de un estudiante en una materia
     */
    public static function getEstadisticasCompletas($estudianteId, $materiaId, $periodoId)
    {
        $desempeno = self::getDesempenoMateria($estudianteId, $materiaId, $periodoId);
        
        if (!$desempeno) {
            return [
                'desempeno' => null,
                'total_logros' => 0,
                'logros_alcanzados' => 0,
                'porcentaje_alcanzado' => 0,
                'observaciones' => null,
                'estado' => null,
                'bloqueado' => false
            ];
        }
        
        $logros = $desempeno->estudianteLogros;
        $logrosAlcanzados = $logros->where('alcanzado', true)->count();
        $totalLogros = $logros->count();
        
        return [
            'desempeno' => $desempeno,
            'total_logros' => $totalLogros,
            'logros_alcanzados' => $logrosAlcanzados,
            'porcentaje_alcanzado' => $totalLogros > 0 ? round(($logrosAlcanzados / $totalLogros) * 100, 1) : 0,
            'observaciones' => $desempeno->observaciones_finales,
            'estado' => $desempeno->estado,
            'bloqueado' => !is_null($desempeno->locked_at)
        ];
    }
    
    /**
     * Obtener resumen de calificaciones por grado y período
     */
    public static function getResumenGradoPeriodo($gradoId, $periodoId)
    {
        return DesempenoMateria::join('estudiantes', 'desempenos_materia.estudiante_id', '=', 'estudiantes.id')
            ->join('materias', 'desempenos_materia.materia_id', '=', 'materias.id')
            ->where('estudiantes.grado_id', $gradoId)
            ->where('desempenos_materia.periodo_id', $periodoId)
            ->select([
                'materias.nombre as materia_nombre',
                'desempenos_materia.nivel_desempeno',
                \DB::raw('COUNT(*) as cantidad_estudiantes'),
                \DB::raw('ROUND(AVG(CASE 
                    WHEN nivel_desempeno = "E" THEN 5
                    WHEN nivel_desempeno = "S" THEN 4
                    WHEN nivel_desempeno = "A" THEN 3
                    WHEN nivel_desempeno = "I" THEN 2
                    ELSE 0 END), 2) as promedio_numerico')
            ])
            ->groupBy('materias.nombre', 'desempenos_materia.nivel_desempeno')
            ->orderBy('materias.nombre')
            ->orderBy('desempenos_materia.nivel_desempeno')
            ->get()
            ->groupBy('materia_nombre');
    }
    
    /**
     * Obtener estudiantes con rendimiento específico en una materia
     */
    public static function getEstudiantesPorRendimiento($materiaId, $periodoId, $nivelDesempeno)
    {
        return DesempenoMateria::with(['estudiante.grado', 'materia'])
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->where('nivel_desempeno', $nivelDesempeno)
            ->get();
    }
    
    /**
     * Verificar si existe una calificación para una combinación específica
     */
    public static function existeCalificacion($estudianteId, $materiaId, $periodoId)
    {
        return DesempenoMateria::where('estudiante_id', $estudianteId)
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->exists();
    }
    
    /**
     * Obtener estadísticas generales de una materia en un período
     */
    public static function getEstadisticasMateria($materiaId, $periodoId)
    {
        $estadisticas = DesempenoMateria::where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->select([
                'nivel_desempeno',
                \DB::raw('COUNT(*) as cantidad')
            ])
            ->groupBy('nivel_desempeno')
            ->get()
            ->keyBy('nivel_desempeno');
        
        $total = $estadisticas->sum('cantidad');
        
        return [
            'total_estudiantes' => $total,
            'excelentes' => $estadisticas->get('E')?->cantidad ?? 0,
            'sobresalientes' => $estadisticas->get('S')?->cantidad ?? 0,
            'aceptables' => $estadisticas->get('A')?->cantidad ?? 0,
            'insuficientes' => $estadisticas->get('I')?->cantidad ?? 0,
            'porcentaje_aprobados' => $total > 0 ? round((($estadisticas->get('E')?->cantidad ?? 0) + 
                                                          ($estadisticas->get('S')?->cantidad ?? 0) + 
                                                          ($estadisticas->get('A')?->cantidad ?? 0)) / $total * 100, 1) : 0
        ];
    }
}
