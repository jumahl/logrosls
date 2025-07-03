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
                    ->required()
                    ->maxLength(255)
                    ->label('Título del Logro')
                    ->helperText('Título descriptivo del logro'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            return $user->materias()->pluck('nombre', 'id');
                        }
                        return \App\Models\Materia::pluck('nombre', 'id');
                    })
                    ->createOptionForm([
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
                    ])
                    ->label('Materia'),
                Forms\Components\TextInput::make('competencia')
                    ->required()
                    ->maxLength(255)
                    ->label('Competencia')
                    ->helperText('Competencia que evalúa este logro'),
                Forms\Components\TextInput::make('tema')
                    ->required()
                    ->maxLength(255)
                    ->label('Tema')
                    ->helperText('Tema específico del logro'),
                Forms\Components\TextInput::make('indicador_desempeno')
                    ->required()
                    ->maxLength(255)
                    ->label('Indicador de Desempeño')
                    ->helperText('Indicador específico que se evalúa'),
                Forms\Components\TextInput::make('dimension')
                    ->maxLength(255)
                    ->label('Dimensión')
                    ->helperText('Dimensión del aprendizaje (opcional)'),
                Forms\Components\Select::make('nivel_dificultad')
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                    ])
                    ->required()
                    ->default('medio')
                    ->label('Nivel de Dificultad')
                    ->helperText('Nivel de complejidad del logro'),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'conocimiento' => 'Conocimiento',
                        'habilidad' => 'Habilidad',
                        'actitud' => 'Actitud',
                        'valor' => 'Valor',
                    ])
                    ->required()
                    ->default('conocimiento')
                    ->label('Tipo de Logro')
                    ->helperText('Tipo de aprendizaje que evalúa'),
                Forms\Components\Select::make('periodos')
                    ->relationship('periodos', 'corte')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
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
                        Forms\Components\TextInput::make('año_escolar')
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
                    ])
                    ->label('Períodos'),
                Forms\Components\Textarea::make('descripcion')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Descripción General')
                    ->helperText('Descripción adicional del logro'),
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
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('competencia')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->label('Competencia'),
                Tables\Columns\TextColumn::make('tema')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->label('Tema'),
                Tables\Columns\TextColumn::make('nivel_dificultad')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bajo' => 'gray',
                        'medio' => 'warning',
                        'alto' => 'danger',
                    })
                    ->label('Nivel'),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'conocimiento' => 'info',
                        'habilidad' => 'success',
                        'actitud' => 'warning',
                        'valor' => 'danger',
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
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('nivel_dificultad')
                    ->options([
                        'bajo' => 'Bajo',
                        'medio' => 'Medio',
                        'alto' => 'Alto',
                    ])
                    ->label('Nivel de Dificultad'),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'conocimiento' => 'Conocimiento',
                        'habilidad' => 'Habilidad',
                        'actitud' => 'Actitud',
                        'valor' => 'Valor',
                    ])
                    ->label('Tipo de Logro'),
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
        $query = parent::getEloquentQuery();
        if ($user && $user->hasRole('profesor')) {
            // Solo mostrar logros de las materias del profesor
            $materiaIds = $user->materias()->pluck('id');
            $query->whereIn('materia_id', $materiaIds);
        }
        return $query;
    }
}
