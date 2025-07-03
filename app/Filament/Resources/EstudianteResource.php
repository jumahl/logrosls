<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstudianteResource\Pages;
use App\Models\Estudiante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class EstudianteResource extends Resource
{
    protected static ?string $model = Estudiante::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Estudiantes';
    
    protected static ?string $modelLabel = 'Estudiante';
    
    protected static ?string $pluralModelLabel = 'Estudiantes';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Gestión de Estudiantes';

    public static function form(Form $form): Form
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
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable()
                    ->label('Apellido'),
                Tables\Columns\TextColumn::make('documento')
                    ->searchable()
                    ->sortable()
                    ->label('Documento'),
                Tables\Columns\TextColumn::make('grado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Grado'),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha de Nacimiento'),
                Tables\Columns\TextColumn::make('activo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    })
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('es_mi_grupo')
                    ->label('Mi Grupo')
                    ->getStateUsing(function ($record) {
                        $user = auth()->user();
                        if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                            return $record->grado_id === $user->director_grado_id ? 'Sí' : 'No';
                        }
                        return null;
                    })
                    ->badge()
                    ->color('success')
                    ->visible(fn() => auth()->user()?->hasRole('profesor') && auth()->user()?->isDirectorGrupo()),
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
                Tables\Filters\SelectFilter::make('activo')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ])
                    ->label('Estado'),
                Tables\Filters\Filter::make('mi_grupo')
                    ->label('Solo mi grupo')
                    ->query(function ($query) {
                        $user = auth()->user();
                        if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                            return $query->where('grado_id', $user->director_grado_id);
                        }
                        return $query;
                    })
                    ->visible(fn() => auth()->user()?->hasRole('profesor') && auth()->user()?->isDirectorGrupo()),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstudiantes::route('/'),
            'create' => Pages\CreateEstudiante::route('/create'),
            'edit' => Pages\EditEstudiante::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
        if ($user && $user->hasRole('profesor')) {
            // Obtener los grados donde el profesor tiene materias asignadas
            $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
            $query->whereIn('grado_id', $gradoIds);
        }
        return $query;
    }
}
