<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'director_grado_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Obtener las materias donde el usuario es docente.
     */
    public function materias()
    {
        return $this->hasMany(Materia::class, 'docente_id');
    }

    /**
     * Obtener el grado donde el usuario es director de grupo.
     */
    public function directorGrado()
    {
        return $this->belongsTo(Grado::class, 'director_grado_id');
    }

    /**
     * Verificar si el usuario es director de grupo.
     */
    public function isDirectorGrupo(): bool
    {
        return !is_null($this->director_grado_id);
    }

    /**
     * Obtener los estudiantes del grupo donde el usuario es director.
     */
    public function estudiantesGrupo()
    {
        if (!$this->isDirectorGrupo()) {
            return collect();
        }
        return $this->directorGrado->estudiantes;
    }
}
