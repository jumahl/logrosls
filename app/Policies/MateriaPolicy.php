<?php

namespace App\Policies;

use App\Models\Materia;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MateriaPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver la lista de materias
    }

    public function view(User $user, Materia $materia): bool
    {
        return true; // Todos pueden ver una materia específica
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede crear materias
    }

    public function update(User $user, Materia $materia): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede actualizar materias
    }

    public function delete(User $user, Materia $materia): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar materias
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar múltiples materias
    }
} 