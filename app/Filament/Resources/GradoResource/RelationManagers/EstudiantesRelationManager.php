<?php

namespace App\Filament\Resources\GradoResource\RelationManagers;

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
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(255)
                    ->label('Apellido'),
                Forms\Components\TextInput::make('documento')
                    ->required()
                    ->maxLength(20)
                    ->label('Documento de Identidad'),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->required()
                    ->label('Fecha de Nacimiento'),
                Forms\Components\TextInput::make('direccion')
                    ->maxLength(255)
                    ->label('Dirección'),
                Forms\Components\TextInput::make('telefono')
                    ->tel()
                    ->maxLength(20)
                    ->label('Teléfono'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->label('Correo Electrónico'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Estudiante Activo'),
                Forms\Components\Hidden::make('grado_id'),
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
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable()
                    ->label('Apellido'),
                Tables\Columns\TextColumn::make('documento')
                    ->searchable()
                    ->sortable()
                    ->label('Documento'),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha de Nacimiento'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('logros_count')
                    ->counts('logros')
                    ->label('Logros')
                    ->sortable(),
            ])
            ->filters([
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