<?php

namespace App\Filament\Resources\HistoricoEstudianteResource\Pages;

use App\Filament\Resources\HistoricoEstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHistoricoEstudiante extends ViewRecord
{
    protected static string $resource = HistoricoEstudianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No permitir editar registros históricos
        ];
    }
}
