<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogroResource\Pages;
use App\Models\Logro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class LogroResource extends Resource
{
    protected static ?string $model = Logro::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Logros';
    
    protected static ?string $modelLabel = 'Logro';
    
    protected static ?string $pluralModelLabel = 'Logros';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Gestión Académica';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->label('Código')
                    ->helperText('Código único del logro'),
                Forms\Components\TextInput::make('titulo')
                    ->maxLength(255)
                    ->label('Identificador / Subrama (opcional)')
                    ->helperText('Identificador para diferenciar subramas dentro de la materia (opcional)'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            return $user->materias()
                                ->get()
                                ->mapWithKeys(function ($materia) {
                                    return [$materia->id => "{$materia->codigo} - {$materia->nombre}"];
                                });
                        }
                        return \App\Models\Materia::get()
                            ->mapWithKeys(function ($materia) {
                                return [$materia->id => "{$materia->codigo} - {$materia->nombre}"];
                            });
                    })
                    ->createOptionForm($user && $user->hasRole('admin') ? [
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
                            ->label('Grados'),
                    ] : null)
                    ->label('Materia'),
                Forms\Components\Textarea::make('desempeno')
                    ->required()
                    ->maxLength(65535)
                    ->label('Desempeño')
                    ->helperText('Descripción del desempeño esperado'),
                Forms\Components\Select::make('periodos')
                    ->relationship('periodos', 'corte')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Períodos')
                    ->helperText('Debe seleccionar al menos un período')
                    ->createOptionForm($user && $user->hasRole('admin') ? [
                        Forms\Components\Select::make('numero_periodo')
                            ->options([
                                1 => 'Primer Período',
                                2 => 'Segundo Período',
                            ])
                            ->required()
                            ->label('Número de Período'),
                        Forms\Components\Select::make('corte')
                            ->options([
                                'Primer Corte' => 'Primer Corte',
                                'Segundo Corte' => 'Segundo Corte',
                            ])
                            ->required()
                            ->label('Corte'),
                        Forms\Components\TextInput::make('anio_escolar')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->label('Año Escolar'),
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->required()
                            ->label('Fecha de Inicio'),
                        Forms\Components\DatePicker::make('fecha_fin')
                            ->required()
                            ->label('Fecha de Fin'),
                    ] : null)
                    ->label('Períodos'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Logro Activo')
                    ->helperText('Indica si el logro está disponible para asignar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título')
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $state ?: \Illuminate\Support\Str::limit($record->desempeno, 40)),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable(['materias.nombre', 'materias.codigo'])
                    ->sortable()
                    ->label('Materia')
                    ->formatStateUsing(fn($state, $record) => 
                        "{$record->materia->codigo} - {$record->materia->nombre}"
                    ),
                Tables\Columns\TextColumn::make('desempeno')
                    ->searchable()
                    ->limit(60)
                    ->label('Desempeño'),
                Tables\Columns\IconColumn::make('activo')
                    ->boolean()
                    ->sortable()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Orden'),
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
                    ->searchable()
                    ->preload()
                    ->label('Materia')
                    ->options(function () {
                        return \App\Models\Materia::get()
                            ->mapWithKeys(function ($materia) {
                                return [$materia->id => "{$materia->codigo} - {$materia->nombre}"];
                            });
                    }),
                // Filtros de nivel y tipo removidos en el nuevo esquema
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('profesor')),
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
            'index' => Pages\ListLogros::route('/'),
            'create' => Pages\CreateLogro::route('/create'),
            'edit' => Pages\EditLogro::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['materia']);
        
        if ($user && $user->hasRole('profesor')) {
            $materiaIds = $user->materias()->pluck('id');
            $query->whereIn('materia_id', $materiaIds);
        }
        return $query;
    }
}
