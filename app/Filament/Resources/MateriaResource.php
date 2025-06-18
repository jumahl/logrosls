<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MateriaResource\Pages;
use App\Filament\Resources\MateriaResource\RelationManagers;
use App\Models\Materia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class MateriaResource extends Resource
{
    protected static ?string $model = Materia::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationLabel = 'Materias';
    
    protected static ?string $modelLabel = 'Materia';
    
    protected static ?string $pluralModelLabel = 'Materias';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
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
                Forms\Components\Select::make('grado_id')
                    ->relationship('grado', 'nombre')
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
                            ])
                            ->required()
                            ->label('Tipo'),
                    ])
                    ->label('Grado'),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('grado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Grado'),
                Tables\Columns\TextColumn::make('docente.name')
                    ->searchable()
                    ->sortable()
                    ->label('Docente'),
                Tables\Columns\IconColumn::make('activa')
                    ->boolean()
                    ->sortable()
                    ->label('Activa'),
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
                Tables\Filters\SelectFilter::make('grado_id')
                    ->relationship('grado', 'nombre')
                    ->label('Grado'),
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Materia $record) {
                        // Eliminar en cascada los logros de la materia
                        $record->logros()->delete();
                    })
                    ->after(function (Materia $record) {
                        Notification::make()
                            ->title('Materia eliminada exitosamente')
                            ->icon('heroicon-o-trash')
                            ->iconColor('danger')
                            ->body('La materia y sus logros relacionados han sido eliminados del sistema.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Eliminar en cascada los logros de cada materia
                                $record->logros()->delete();
                            }
                        })
                        ->after(function () {
                            Notification::make()
                                ->title('Materias eliminadas exitosamente')
                                ->icon('heroicon-o-trash')
                                ->iconColor('danger')
                                ->body('Las materias seleccionadas y sus logros relacionados han sido eliminados del sistema.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Se ha eliminado la referencia al LogrosRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterias::route('/'),
            'create' => Pages\CreateMateria::route('/create'),
            'edit' => Pages\EditMateria::route('/{record}/edit'),
        ];
    }
}
