<?php

namespace App\Filament\Resources\LogroResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstudiantesRelationManager extends RelationManager
{
    protected static string $relationship = 'estudiantes';

    protected static ?string $recordTitleAttribute = 'nombre';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Estudiante'),
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
            ->recordTitleAttribute('estudiante.nombre')
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('estudiante.apellido')
                    ->searchable()
                    ->sortable()
                    ->label('Apellido'),
                Tables\Columns\TextColumn::make('estudiante.documento')
                    ->searchable()
                    ->sortable()
                    ->label('Documento'),
                Tables\Columns\TextColumn::make('estudiante.grado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Grado'),
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->date()
                    ->sortable()
                    ->label('Fecha de Asignación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estudiante.grado_id')
                    ->relationship('estudiante.grado', 'nombre')
                    ->label('Grado'),
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