<?php

namespace App\Filament\Resources\LogroResource\Pages;

use App\Filament\Resources\LogroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogro extends EditRecord
{
    protected static string $resource = LogroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
