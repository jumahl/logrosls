<?php

namespace App\Filament\Resources\MateriaResource\Pages;

use App\Filament\Resources\MateriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ShowMateria extends ViewRecord
{
    protected static string $resource = MateriaResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [
            \Filament\Actions\Action::make('volver')
                ->label('Volver')
                ->url(MateriaResource::getUrl('index'))
                ->color('gray'),
        ];
        if ($user && $user->hasRole('admin')) {
            $actions[] = \Filament\Actions\EditAction::make();
            $actions[] = \Filament\Actions\DeleteAction::make();
        }
        return $actions;
    }
} 