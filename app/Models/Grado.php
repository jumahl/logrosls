<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Grado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Obtener los estudiantes de este grado.
     */
    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class);
    }

    /**
     * Obtener las materias de este grado (relación muchos a muchos).
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'grado_materia');
    }

    /**
     * Obtener los logros de este grado a través de las materias.
     * Esta relación usa hasManyThrough, que genera una consulta SQL incorrecta
     * para nuestra estructura de base de datos (muchos a muchos).
     * Sin embargo, es necesaria para que Filament reconozca la relación.
     * La lógica de consulta real se sobreescribe completamente en el RelationManager.
     */
    public function logros(): HasManyThrough
    {
        return $this->hasManyThrough(Logro::class, Materia::class);
    }

    /**
     * Obtener el director de grupo de este grado.
     */
    public function directorGrupo(): HasOne
    {
        return $this->hasOne(User::class, 'director_grado_id');
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($grado) {
            // Eliminar en cascada los estudiantes del grado
            $grado->estudiantes()->delete();
        });
    }
}
