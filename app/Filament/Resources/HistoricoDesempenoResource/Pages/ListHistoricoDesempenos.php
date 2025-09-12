<?php

namespace App\Filament\Resources\HistoricoDesempenoResource\Pages;

use App\Filament\Resources\HistoricoDesempenoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistoricoDesempenos extends ListRecords
{
    protected static string $resource = HistoricoDesempenoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No permitir crear registros históricos manualmente
        ];
    }
}
