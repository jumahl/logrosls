<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoLogro extends Model
{
    protected $table = 'historico_logros';
    
    protected $fillable = [
        'historico_desempeno_id',
        'logro_id',
        'anio_escolar',
        'estudiante_nombre',
        'estudiante_apellido',
        'estudiante_documento',
        'logro_descripcion',
        'materia_nombre',
        'alcanzado'
    ];
    
    protected $casts = [
        'alcanzado' => 'boolean'
    ];
    
    /**
     * Desempeño histórico al que pertenece
     */
    public function historicoDesempeno(): BelongsTo
    {
        return $this->belongsTo(HistoricoDesempeno::class);
    }
    
    /**
     * Logro actual (si aún existe)
     */
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class);
    }
    
    /**
     * Nombre completo del estudiante
     */
    public function getNombreCompletoEstudianteAttribute()
    {
        return "{$this->estudiante_nombre} {$this->estudiante_apellido}";
    }
}
