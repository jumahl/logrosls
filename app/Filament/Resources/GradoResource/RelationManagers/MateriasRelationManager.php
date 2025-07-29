<?php

namespace App\Filament\Resources\GradoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MateriasRelationManager extends RelationManager
{
    protected static string $relationship = 'materias';

    protected static ?string $recordTitleAttribute = 'nombre';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(20)
                    ->label('Código'),
                Forms\Components\Select::make('grados')
                    ->relationship('grados', 'nombre')
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre'),
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'preescolar' => 'Preescolar',
                                'primaria' => 'Primaria',
                                'secundaria' => 'Secundaria',
                                'media_academica' => 'Media Académica',
                            ])
                            ->required()
                            ->label('Tipo'),
                    ])
                    ->label('Grados'),
                Forms\Components\Select::make('docente_id')
                    ->relationship('docente', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Docente'),
                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Toggle::make('activa')
                    ->required()
                    ->default(true)
                    ->label('Materia Activa'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('grados.nombre')
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->sortable()
                    ->label('Grados'),
                Tables\Columns\TextColumn::make('docente.name')
                    ->searchable()
                    ->sortable()
                    ->label('Docente'),
                Tables\Columns\IconColumn::make('activa')
                    ->boolean()
                    ->sortable()
                    ->label('Activa'),
                Tables\Columns\TextColumn::make('logros_count')
                    ->counts('logros')
                    ->label('Logros')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grados')
                    ->relationship('grados', 'nombre')
                    ->multiple()
                    ->label('Grados'),
                Tables\Filters\SelectFilter::make('docente_id')
                    ->relationship('docente', 'name')
                    ->label('Docente'),
                Tables\Filters\SelectFilter::make('activa')
                    ->options([
                        '1' => 'Activa',
                        '0' => 'Inactiva',
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