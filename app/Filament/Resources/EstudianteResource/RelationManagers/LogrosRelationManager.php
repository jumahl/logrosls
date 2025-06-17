<?php

namespace App\Filament\Resources\EstudianteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogrosRelationManager extends RelationManager
{
    protected static string $relationship = 'logros';

    protected static ?string $recordTitleAttribute = 'titulo';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('logro_id')
                    ->relationship('logro', 'titulo')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Logro'),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->default(now())
                    ->label('Fecha de Asignación'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('logro.titulo')
            ->columns([
                Tables\Columns\TextColumn::make('logro.titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Logro'),
                Tables\Columns\TextColumn::make('logro.materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('logro.periodo.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Periodo'),
                Tables\Columns\TextColumn::make('logro.nivel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bajo' => 'danger',
                        'medio' => 'warning',
                        'alto' => 'success',
                        'superior' => 'primary',
                        default => 'gray',
                    })
                    ->label('Nivel'),
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha de Asignación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('logro.materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('logro.periodo_id')
                    ->relationship('logro.periodo', 'nombre')
                    ->label('Periodo'),
                Tables\Filters\SelectFilter::make('logro.nivel')
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                        'superior' => 'Superior',
                    ])
                    ->label('Nivel'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 