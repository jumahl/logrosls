<?php

namespace App\Filament\Resources\PeriodoResource\Pages;

use App\Filament\Resources\PeriodoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePeriodo extends CreateRecord
{
    protected static string $resource = PeriodoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Período creado')
            ->body('El período ha sido creado exitosamente.');
    }
}
