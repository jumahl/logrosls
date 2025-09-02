<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricoDesempeno extends Model
{
    protected $table = 'historico_desempenos';
    
    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'periodo_id',
        'anio_escolar',
        'estudiante_nombre',
        'estudiante_apellido',
        'estudiante_documento',
        'materia_nombre',
        'materia_codigo',
        'periodo_nombre',
        'periodo_corte',
        'periodo_numero',
        'nivel_desempeno',
        'observaciones_finales',
        'docente_nombre',
        'director_grupo'
    ];
    
    /**
     * Estudiante actual (si aún existe)
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    
    /**
     * Materia actual (si aún existe)
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
    
    /**
     * Período actual (si aún existe)
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }
    
    /**
     * Logros históricos de este desempeño
     */
    public function logros(): HasMany
    {
        return $this->hasMany(HistoricoLogro::class, 'historico_desempeno_id');
    }
    
    /**
     * Obtener valor numérico del nivel de desempeño
     */
    public function getValorNumericoAttribute()
    {
        return match($this->nivel_desempeno) {
            'E' => 5.0, // Excelente
            'S' => 4.0, // Sobresaliente  
            'A' => 3.0, // Aceptable
            'I' => 2.0, // Insuficiente
            default => 0.0
        };
    }
    
    /**
     * Nombre completo del estudiante
     */
    public function getNombreCompletoEstudianteAttribute()
    {
        return "{$this->estudiante_nombre} {$this->estudiante_apellido}";
    }
}
