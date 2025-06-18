<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean'
    ];

    /**
     * Obtener los logros de este periodo.
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(Logro::class)
            ->withTimestamps();
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($periodo) {
            // Desvincular los logros del período
            $periodo->logros()->detach();
        });
    }
}
