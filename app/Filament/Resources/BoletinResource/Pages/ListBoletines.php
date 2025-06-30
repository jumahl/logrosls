<?php

namespace App\Filament\Resources\BoletinResource\Pages;

use App\Filament\Resources\BoletinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBoletines extends ListRecords
{
    protected static string $resource = BoletinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
} 