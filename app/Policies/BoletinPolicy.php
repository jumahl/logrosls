<?php

namespace App\Policies;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BoletinPolicy
{
    public function viewAny(User $user): bool
    {
        // Solo admin y profesores que son directores de grupo pueden ver boletines
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->isDirectorGrupo());
    }

    public function view(User $user, Estudiante $estudiante): bool
    {
        if (!$user) {
            return false;
        }
        
        // Admin puede ver todos los boletines
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Profesor director de grupo solo puede ver boletines de su grupo
        if ($user->hasRole('profesor') && $user->isDirectorGrupo()) {
            return $estudiante->grado_id === $user->director_grado_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        // Solo admin y profesores que son directores de grupo pueden crear boletines
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->isDirectorGrupo());
    }

    public function update(User $user, Estudiante $estudiante): bool
    {
        // Los boletines no se pueden editar, solo generar
        return false;
    }

    public function delete(User $user, Estudiante $estudiante): bool
    {
        // Los boletines no se pueden eliminar
        return false;
    }

    public function deleteAny(User $user): bool
    {
        // Los boletines no se pueden eliminar
        return false;
    }
} 