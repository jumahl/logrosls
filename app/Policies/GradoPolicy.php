<?php

namespace App\Policies;

use App\Models\Grado;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradoPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver la lista de grados
    }

    public function view(User $user, Grado $grado): bool
    {
        return true; // Todos pueden ver un grado específico
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede crear grados
    }

    public function update(User $user, Grado $grado): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede actualizar grados
    }

    public function delete(User $user, Grado $grado): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar grados
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin'); // Solo el admin puede eliminar múltiples grados
    }
} 