<?php

namespace App\Filament\Resources\LogroResource\Pages;

use App\Filament\Resources\LogroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditLogro extends EditRecord
{
    protected static string $resource = LogroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Logro actualizado')
            ->body('El logro ha sido actualizado exitosamente.');
    }
}
