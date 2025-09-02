<?php

namespace App\Filament\Resources\HistoricoEstudianteResource\Pages;

use App\Filament\Resources\HistoricoEstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoricoEstudiantes extends ListRecords
{
    protected static string $resource = HistoricoEstudianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No permitir crear registros históricos manualmente
        ];
    }
}
