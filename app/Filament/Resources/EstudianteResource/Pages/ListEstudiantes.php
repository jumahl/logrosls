<?php

namespace App\Filament\Resources\EstudianteResource\Pages;

use App\Filament\Resources\EstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstudiantes extends ListRecords
{
    protected static string $resource = EstudianteResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        
        // Solo admin y directores de grupo pueden crear estudiantes
        if ($user && ($user->hasRole('admin') || ($user->hasRole('profesor') && $user->isDirectorGrupo()))) {
            return [
                Actions\CreateAction::make(),
            ];
        }
        
        return [];
    }
}
