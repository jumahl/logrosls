<?php

namespace App\Filament\Resources\GradoResource\Pages;

use App\Filament\Resources\GradoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateGrado extends CreateRecord
{
    protected static string $resource = GradoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Grado creado')
            ->body('El grado ha sido creado exitosamente.');
    }
}
