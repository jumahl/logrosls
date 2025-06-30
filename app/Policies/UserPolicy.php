<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede ver la lista de usuarios
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede ver un usuario específico
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede crear usuarios
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede actualizar usuarios
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar usuarios
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar múltiples usuarios
    }
} 