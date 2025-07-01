<?php

namespace App\Policies;

use App\Models\EstudianteLogro;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EstudianteLogroPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('profesor');
    }

    public function view(User $user, EstudianteLogro $nota): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->materias->pluck('id')->contains($nota->logro->materia_id));
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('profesor');
    }

    public function update(User $user, EstudianteLogro $nota): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->materias->pluck('id')->contains($nota->logro->materia_id));
    }

    public function delete(User $user, EstudianteLogro $nota): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
} 