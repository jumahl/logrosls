<?php

namespace App\Filament\Resources\LogroResource\Pages;

use App\Filament\Resources\LogroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogros extends ListRecords
{
    protected static string $resource = LogroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
