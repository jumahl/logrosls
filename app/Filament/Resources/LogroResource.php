<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogroResource\Pages;
use App\Filament\Resources\LogroResource\RelationManagers;
use App\Models\Logro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogroResource extends Resource
{
    protected static ?string $model = Logro::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    
    protected static ?string $navigationLabel = 'Logros';
    
    protected static ?string $modelLabel = 'Logro';
    
    protected static ?string $pluralModelLabel = 'Logros';
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->placeholder('Ej: LOG-2024-001')
                    ->helperText('Ingrese un código único para el logro (ej: LOG-2024-001)')
                    ->label('Código del Logro'),
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
                Forms\Components\Select::make('periodo_id')
                    ->relationship('periodo', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Periodo'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('periodo_id')
                    ->relationship('periodo', 'nombre')
                    ->label('Periodo'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\EstudiantesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogros::route('/'),
            'create' => Pages\CreateLogro::route('/create'),
            'edit' => Pages\EditLogro::route('/{record}/edit'),
        ];
    }
}
