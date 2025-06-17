<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradoResource\Pages;
use App\Filament\Resources\GradoResource\RelationManagers;
use App\Models\Grado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GradoResource extends Resource
{
    protected static ?string $model = Grado::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Grados';
    
    protected static ?string $modelLabel = 'Grado';
    
    protected static ?string $pluralModelLabel = 'Grados';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Gestión Académica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Grado'),
                Forms\Components\Select::make('tipo')
                    ->required()
                    ->options([
                        'preescolar' => 'Preescolar',
                        'primaria' => 'Primaria',
                        'secundaria' => 'Secundaria',
                    ])
                    ->label('Tipo de Grado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre del Grado'),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preescolar' => 'success',
                        'primaria' => 'info',
                        'secundaria' => 'warning',
                        default => 'gray',
                    })
                    ->label('Tipo de Grado'),
                Tables\Columns\TextColumn::make('estudiantes_count')
                    ->counts('estudiantes')
                    ->label('Estudiantes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('materias_count')
                    ->counts('materias')
                    ->label('Materias')
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
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'preescolar' => 'Preescolar',
                        'primaria' => 'Primaria',
                        'secundaria' => 'Secundaria',
                    ])
                    ->label('Tipo de Grado'),
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
            RelationManagers\MateriasRelationManager::class,
            RelationManagers\LogrosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrados::route('/'),
            'create' => Pages\CreateGrado::route('/create'),
            'edit' => Pages\EditGrado::route('/{record}/edit'),
        ];
    }
}
