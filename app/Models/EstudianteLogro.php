<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteLogro extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'logro_id',
        'fecha_asignacion',
        'observaciones',
    ];

    /**
     * Obtener el estudiante al que se le asignó el logro.
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Obtener el logro asignado al estudiante.
     */
    public function logro(): BelongsTo
    {
        return $this->belongsTo(Logro::class);
    }
}
