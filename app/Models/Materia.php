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
        'activa',
        'area'
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
     * Obtener los desempeños de esta materia.
     */
    public function desempenosMateria(): HasMany
    {
        return $this->hasMany(DesempenoMateria::class);
    }

    /**
     * Scope para filtrar materias activas.
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para filtrar por docente.
     */
    public function scopePorDocente($query, $docenteId)
    {
        return $query->where('docente_id', $docenteId);
    }

    /**
     * Scope para filtrar por área.
     */
    public function scopePorArea($query, $area)
    {
        return $query->where('area', $area);
    }
    
    /**
     * Obtener las áreas disponibles con sus etiquetas legibles.
     */
    public static function getAreas(): array
    {
        return [
            'humanidades' => 'Humanidades',
            'matematicas' => 'Matemáticas',
            'ciencias_naturales_y_educacion_ambiental' => 'Ciencias Naturales y Educación Ambiental',
            'ciencias_sociales' => 'Ciencias Sociales',
            'ciencias_politicas_y_economicas' => 'Ciencias Políticas y Económicas',
            'filosofia' => 'Filosofía',
            'tecnologia_e_informatica' => 'Tecnología e Informática',
            'educacion_etica_y_valores_humanos' => 'Educación Ética y Valores Humanos',
            'educacion_religiosa' => 'Educación Religiosa',
            'educacion_artistica' => 'Educación Artística',
            'educacion_fisica_recreacion_y_deporte' => 'Educación Física, Recreación y Deporte',
            'disciplina_y_convivencia_escolar' => 'Disciplina y Convivencia Escolar',
            'dimension_comunicativa' => 'Dimensión Comunicativa',
            'dimension_cognitiva' => 'Dimensión Cognitiva',
            'dimension_estetica' => 'Dimensión Estética',
            'dimension_etica_y_socio_afectiva' => 'Dimensión Ética y/o Socio Afectiva',
            'dimension_corporal' => 'Dimensión Corporal'
        ];
    }

    /**
     * Obtener la etiqueta legible del área.
     */
    public function getAreaLabelAttribute(): string
    {
        $areas = self::getAreas();
        return $areas[$this->area] ?? $this->area ?? 'Sin área';
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
            
            // Eliminar en cascada los desempeños de la materia
            $materia->desempenosMateria()->delete();
        });
    }
}
