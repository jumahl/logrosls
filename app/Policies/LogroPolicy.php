<?php

namespace App\Policies;

use App\Models\Logro;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LogroPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('profesor');
    }

    public function view(User $user, Logro $logro): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->materias->pluck('id')->contains($logro->materia_id));
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('profesor');
    }

    public function update(User $user, Logro $logro): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('profesor') && $user->materias->pluck('id')->contains($logro->materia_id));
    }

    public function delete(User $user, Logro $logro): bool
    {
        return $user->hasRole('admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
} 