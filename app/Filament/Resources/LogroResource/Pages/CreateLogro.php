<?php

namespace App\Filament\Resources\LogroResource\Pages;

use App\Filament\Resources\LogroResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLogro extends CreateRecord
{
    protected static string $resource = LogroResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Logro creado')
            ->body('El logro ha sido creado exitosamente.');
    }
}
