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
use Filament\Notifications\Notification;

class GradoResource extends Resource
{
    protected static ?string $model = Grado::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Grados';
    
    protected static ?string $modelLabel = 'Grado';
    
    protected static ?string $pluralModelLabel = 'Grados';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'preescolar' => 'Preescolar',
                        'primaria' => 'Primaria',
                        'secundaria' => 'Secundaria',
                        'media_academica' => 'Media Academica',
                    ])
                    ->required()
                    ->label('Tipo'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Grado Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preescolar' => 'info',
                        'primaria' => 'success',
                        'secundaria' => 'warning',
                        'media_academica' => 'danger',
                        default => 'gray',
                    })
                    ->label('Tipo'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
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
                        'media_academica' => 'Media Academica',
                    ])
                    ->label('Tipo'),
                Tables\Filters\SelectFilter::make('activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ])
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Grado $record) {
                        // Eliminar en cascada los estudiantes, materias y logros del grado
                        $record->estudiantes()->delete();
                        $record->materias()->delete();
                        $record->logros()->delete();
                    })
                    ->after(function (Grado $record) {
                        Notification::make()
                            ->title('Grado eliminado exitosamente')
                            ->icon('heroicon-o-trash')
                            ->iconColor('danger')
                            ->body('El grado y todos sus datos relacionados han sido eliminados del sistema.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Eliminar en cascada los estudiantes, materias y logros de cada grado
                                $record->estudiantes()->delete();
                                $record->materias()->delete();
                                $record->logros()->delete();
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Grados eliminados exitosamente')
                                ->icon('heroicon-o-trash')
                                ->iconColor('danger')
                                ->body('Los grados seleccionados y todos sus datos relacionados han sido eliminados del sistema.')
                                ->success()
                                ->send();
                        }),
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
