<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnioEscolar extends Model
{
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
     * Estudiantes históricos de este año
     */
    public function estudiantes(): HasMany
    {
        return $this->hasMany(HistoricoEstudiante::class, 'anio_escolar', 'anio');
    }
    
    /**
     * Desempeños históricos de este año
     */
    public function desempenos(): HasMany
    {
        return $this->hasMany(HistoricoDesempeno::class, 'anio_escolar', 'anio');
    }
}
