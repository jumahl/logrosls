<?php

namespace App\Policies;

use App\Models\Periodo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PeriodoPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver la lista de periodos
    }

    public function view(User $user, Periodo $periodo): bool
    {
        return true; // Todos pueden ver un periodo específico
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede crear periodos
    }

    public function update(User $user, Periodo $periodo): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede actualizar periodos
    }

    public function delete(User $user, Periodo $periodo): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar periodos
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar múltiples periodos
    }
} 