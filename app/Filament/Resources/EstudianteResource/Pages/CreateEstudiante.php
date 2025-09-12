<?php

namespace App\Filament\Resources\EstudianteResource\Pages;

use App\Filament\Resources\EstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;

class CreateEstudiante extends CreateRecord
{
    protected static string $resource = EstudianteResource::class;
    
    public function mount(): void
    {
        $user = auth()->user();
        
        // Solo admin y directores de grupo pueden crear estudiantes
        if (!$user || (!$user->hasRole('admin') && !($user->hasRole('profesor') && $user->isDirectorGrupo()))) {
            throw new AuthorizationException('No tienes permisos para crear estudiantes.');
        }
        
        parent::mount();
    }
}
