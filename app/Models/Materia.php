<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'docente_id',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean'
    ];

    /**
     * Obtener los grados a los que pertenece la materia (relación muchos a muchos).
     */
    public function grados(): BelongsToMany
    {
        return $this->belongsToMany(Grado::class, 'grado_materia');
    }

    /**
     * Obtener el docente asignado a la materia.
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    /**
     * Obtener los logros de esta materia.
     */
    public function logros(): HasMany
    {
        return $this->hasMany(Logro::class);
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($materia) {
            // Eliminar en cascada los logros de la materia
            $materia->logros()->delete();
        });
    }
}
