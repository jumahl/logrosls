<?php

namespace App\Filament\Resources\PeriodoResource\RelationManagers;

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
                Forms\Components\TextInput::make('titulo')
                    ->required()
                    ->maxLength(255)
                    ->label('Título del Logro'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia'),
                Forms\Components\Textarea::make('descripcion')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Select::make('nivel')
                    ->required()
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                        'superior' => 'Superior',
                    ])
                    ->label('Nivel de Logro'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Logro Activo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('titulo')
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('nivel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bajo' => 'danger',
                        'medio' => 'warning',
                        'alto' => 'success',
                        'superior' => 'primary',
                        default => 'gray',
                    })
                    ->label('Nivel'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('estudiantes_count')
                    ->counts('estudiantes')
                    ->label('Estudiantes')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('nivel')
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                        'superior' => 'Superior',
                    ])
                    ->label('Nivel'),
                Tables\Filters\SelectFilter::make('activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ])
                    ->label('Estado'),
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