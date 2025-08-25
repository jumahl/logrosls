<?php

namespace App\Policies;

use App\Models\DesempenoMateria;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DesempenoMateriaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'profesor', 'rector']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DesempenoMateria $desempenoMateria): bool
    {
        // Admin y rector pueden ver todo
        if ($user->hasAnyRole(['admin', 'rector'])) {
            return true;
        }

        // Profesor solo puede ver los desempeños de sus materias
        if ($user->hasRole('profesor')) {
            return $user->materias()->where('id', $desempenoMateria->materia_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'profesor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DesempenoMateria $desempenoMateria): bool
    {
        // No se puede editar si está bloqueado
        if ($desempenoMateria->locked_at) {
            return false;
        }

        // Admin puede editar todo
        if ($user->hasRole('admin')) {
            return true;
        }

        // Profesor solo puede editar los desempeños de sus materias
        if ($user->hasRole('profesor')) {
            return $user->materias()->where('id', $desempenoMateria->materia_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DesempenoMateria $desempenoMateria): bool
    {
        // No se puede eliminar si está bloqueado
        if ($desempenoMateria->locked_at) {
            return false;
        }

        // Solo admin puede eliminar
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DesempenoMateria $desempenoMateria): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DesempenoMateria $desempenoMateria): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can lock/unlock the model.
     */
    public function lock(User $user, DesempenoMateria $desempenoMateria): bool
    {
        // Admin y rector pueden bloquear/desbloquear
        if ($user->hasAnyRole(['admin', 'rector'])) {
            return true;
        }

        // Profesor puede bloquear solo sus materias (publicar notas)
        if ($user->hasRole('profesor')) {
            return $user->materias()->where('id', $desempenoMateria->materia_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, DesempenoMateria $desempenoMateria): bool
    {
        // Admin puede publicar todo
        if ($user->hasRole('admin')) {
            return true;
        }

        // Profesor solo puede publicar los desempeños de sus materias
        if ($user->hasRole('profesor')) {
            return $user->materias()->where('id', $desempenoMateria->materia_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can bulk actions.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
