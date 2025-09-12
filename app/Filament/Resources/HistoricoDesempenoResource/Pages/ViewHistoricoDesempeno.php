<?php

namespace App\Filament\Resources\HistoricoDesempenoResource\Pages;

use App\Filament\Resources\HistoricoDesempenoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHistoricoDesempeno extends ViewRecord
{
    protected static string $resource = HistoricoDesempenoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No permitir editar registros históricos
        ];
    }
}
