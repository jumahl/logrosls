<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'documento',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'email',
        'grado_id',
        'activo'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean'
    ];

    protected $appends = ['nombre_completo'];

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido} - {$this->documento}";
    }

    /**
     * Obtener el grado al que pertenece el estudiante.
     */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    /**
     * Obtener los desempeños de materias del estudiante.
     */
    public function desempenosMateria(): HasMany
    {
        return $this->hasMany(DesempenoMateria::class);
    }

    /**
     * Obtener los logros asignados al estudiante a través de desempeños.
     */
    public function estudianteLogros(): HasManyThrough
    {
        return $this->hasManyThrough(
            EstudianteLogro::class,
            DesempenoMateria::class,
            'estudiante_id',
            'desempeno_materia_id',
            'id',
            'id'
        );
    }

    /**
     * Obtener los logros únicos asignados al estudiante.
     * Método helper que devuelve una colección, no una relación
     */
    public function logros()
    {
        return Logro::whereIn('id', function ($query) {
            $query->select('logro_id')
                  ->from('estudiante_logros')
                  ->join('desempenos_materia', 'desempenos_materia.id', '=', 'estudiante_logros.desempeno_materia_id')
                  ->where('desempenos_materia.estudiante_id', $this->id);
        })->distinct()->get();
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($estudiante) {
            // Eliminar en cascada los logros del estudiante
            $estudiante->estudianteLogros()->delete();
            
            // Eliminar en cascada los desempeños de materias del estudiante
            $estudiante->desempenosMateria()->delete();
        });
    }
}
