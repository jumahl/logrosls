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
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Configuración Académica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Grado')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->label('Nombre del Grado'),
                        Forms\Components\TextInput::make('tipo')
                            ->required()
                            ->maxLength(255)
                            ->label('Tipo de Grado'),
                        Forms\Components\Toggle::make('activo')
                            ->label('Grado Activo')
                            ->default(true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Director de Grupo')
                    ->schema([
                        Forms\Components\Placeholder::make('director_info')
                            ->label('Director Asignado')
                            ->content(function ($record) {
                                if ($record && $record->directorGrupo) {
                                    return $record->directorGrupo->name . ' (' . $record->directorGrupo->email . ')';
                                }
                                return 'No hay director asignado';
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
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
                Tables\Columns\TextColumn::make('directorGrupo.name')
                    ->label('Director de Grupo')
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color('info'),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->hasRole('admin')),
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
            'view' => Pages\ShowGrado::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
        if ($user && $user->hasRole('profesor')) {
            // Solo mostrar los grados donde el profesor tiene materias asignadas
            $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
            $query->whereIn('id', $gradoIds);
        }
        return $query;
    }
}
