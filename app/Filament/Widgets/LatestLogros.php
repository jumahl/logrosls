<?php

namespace App\Filament\Widgets;

use App\Models\Logro;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLogros extends BaseWidget
{
    protected static ?string $heading = 'Últimos Logros Asignados';
    
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Logro::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('periodo.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Periodo'),
                Tables\Columns\TextColumn::make('nivel')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Fecha de Creación'),
            ])
            ->defaultSort('created_at', 'desc');
    }
} 