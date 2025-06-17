<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'documento',
        'genero',
        'fecha_nacimiento',
        'grado_id',
    ];

    /**
     * Obtener el grado al que pertenece el estudiante.
     */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Obtener los logros asignados al estudiante.
     */
    public function estudianteLogros(): HasMany
    {
        return $this->hasMany(EstudianteLogro::class);
    }

    /**
     * Obtener los logros asignados al estudiante.
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(Logro::class, 'estudiante_logros')
            ->withPivot('fecha_asignacion', 'observaciones')
            ->withTimestamps();
    }
}
