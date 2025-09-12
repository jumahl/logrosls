<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricoEstudiante extends Model
{
    protected $table = 'historico_estudiantes';
    
    protected $fillable = [
        'estudiante_id',
        'grado_id',
        'anio_escolar',
        'estudiante_nombre',
        'estudiante_apellido',
        'estudiante_documento',
        'grado_nombre',
        'grado_grupo',
        'resultado_final',
        'promedio_anual',
        'observaciones_anuales',
    ];
    
    protected $casts = [
        'promedio_anual' => 'decimal:2'
    ];
    
    /**
     * Estudiante actual (si aún existe)
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    
    /**
     * Grado que cursó (si aún existe)
     */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }
    
    /**
     * Desempeños de este estudiante en este año
     */
    public function desempenos(): HasMany
    {
        return $this->hasMany(HistoricoDesempeno::class, 'estudiante_id', 'estudiante_id')
            ->where('anio_escolar', $this->anio_escolar);
    }
    
    /**
     * Nombre completo del estudiante
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->estudiante_nombre} {$this->estudiante_apellido}";
    }
    
    /**
     * Nombre completo del grado
     */
    public function getGradoCompletoAttribute()
    {
        return $this->grado_grupo ? "{$this->grado_nombre} {$this->grado_grupo}" : $this->grado_nombre;
    }
}
