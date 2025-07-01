<?php

namespace App\Filament\Resources\GradoResource\Pages;

use App\Filament\Resources\GradoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ShowGrado extends ViewRecord
{
    protected static string $resource = GradoResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            \Filament\Actions\Action::make('volver')
                ->label('Volver')
                ->url(GradoResource::getUrl('index'))
                ->color('gray'),
        ];
        if ($user && $user->hasRole('admin')) {
            $actions[] = \Filament\Actions\EditAction::make();
            $actions[] = \Filament\Actions\DeleteAction::make();
        }
        return $actions;
    }
} 