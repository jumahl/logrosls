<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnioEscolar extends Model
{
    use HasFactory;
    protected $table = 'anios_escolares';
    
    protected $fillable = [
        'anio',
        'activo',
        'finalizado', 
        'fecha_inicio',
        'fecha_fin',
        'observaciones'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'finalizado' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];
    
    /**
     * Obtener el año escolar activo actual
     */
    public static function getActivo()
    {
        return static::where('activo', true)->first();
    }
    
    /**
     * Activar este año escolar
     */
    public function activar()
    {
        // Desactivar todos los otros años
        static::where('id', '!=', $this->id)->update(['activo' => false]);
        // Activar este año
        $this->update(['activo' => true]);
    }
    
    /**
     * Períodos académicos de este año escolar
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class, 'anio_escolar', 'anio');
    }
    
    /**
     * Estudiantes históricos de este año
     */
    public function estudiantesHistoricos(): HasMany
    {
        return $this->hasMany(HistoricoEstudiante::class, 'anio_escolar', 'anio');
    }
    
    /**
     * Desempeños históricos de este año
     */
    public function desempenosHistoricos(): HasMany
    {
        return $this->hasMany(HistoricoDesempeno::class, 'anio_escolar', 'anio');
    }
    
    /**
     * Logros históricos de este año
     */
    public function logrosHistoricos(): HasMany
    {
        return $this->hasMany(HistoricoEstudianteLogro::class, 'anio_escolar', 'anio');
    }
    
    /**
     * Verificar si este año puede ser activado
     */
    public function puedeActivarse(): bool
    {
        return !$this->finalizado && !$this->activo;
    }
    
    /**
     * Verificar si este año puede finalizarse
     */
    public function puedeFinalizarse(): bool
    {
        return $this->activo && !$this->finalizado;
    }
    
    /**
     * Finalizar este año escolar
     */
    public function finalizar()
    {
        $this->update([
            'activo' => false,
            'finalizado' => true
        ]);
    }
    
    /**
     * Scope para años activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    /**
     * Scope para años finalizados
     */
    public function scopeFinalizados($query)
    {
        return $query->where('finalizado', true);
    }
    
    /**
     * Scope para años disponibles para transición
     */
    public function scopeDisponiblesParaTransicion($query)
    {
        return $query->where('activo', false)->where('finalizado', false);
    }
}
