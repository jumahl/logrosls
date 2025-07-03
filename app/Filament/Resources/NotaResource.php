<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Models\EstudianteLogro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotaResource extends Resource
{
    protected static ?string $model = EstudianteLogro::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $navigationLabel = 'Reporte Materia';
    
    protected static ?string $modelLabel = 'Reporte Materia';
    
    protected static ?string $pluralModelLabel = 'Reportes Materia';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Reportes';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\Select::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Estudiante'),
                Forms\Components\Select::make('materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Materia')
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            return $user->materias()->pluck('nombre', 'id');
                        }
                        return \App\Models\Materia::pluck('nombre', 'id');
                    })
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar el logro seleccionado cuando cambie la materia
                        $set('logro_id', null);
                    }),
                Forms\Components\Select::make(request()->routeIs('filament.resources.nota-resource.create') ? 'logros' : 'logro_id')
                    ->options(function ($get) use ($user) {
                        $materiaId = $get('materia_id');
                        if ($materiaId) {
                            $query = \App\Models\Logro::where('materia_id', $materiaId)->where('activo', true);
                            if ($user && $user->hasRole('profesor')) {
                                $materiaIds = $user->materias()->pluck('id');
                                if (!$materiaIds->contains($materiaId)) {
                                    return [];
                                }
                            }
                            return $query->orderBy('titulo')->pluck('titulo', 'id')->map(function ($titulo, $id) {
                                $logro = \App\Models\Logro::find($id);
                                return $titulo . ' - ' . substr($logro->competencia, 0, 50) . '...';
                            });
                        }
                        return [];
                    })
                    ->when(request()->routeIs('filament.resources.nota-resource.create'), fn ($field) => $field->multiple())
                    ->required()
                    ->searchable()
                    ->label('Logros')
                    ->helperText('Seleccione los logros que desea asignar al estudiante. Puede seleccionar múltiples logros de la materia seleccionada.')
                    ->disabled(fn ($get) => !$get('materia_id')),
                Forms\Components\Select::make('periodo_id')
                    ->relationship('periodo', 'corte')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->periodo_completo;
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Período'),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'E' => 'E - Excelente',
                        'S' => 'S - Sobresaliente',
                        'A' => 'A - Aceptable',
                        'I' => 'I - Insuficiente',
                    ])
                    ->required()
                    ->label('Nivel de Desempeño')
                    ->helperText('Seleccione el nivel de desempeño alcanzado por el estudiante en este logro'),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones')
                    ->helperText('Comentarios adicionales sobre el desempeño del estudiante'),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Estudiante'),
                Tables\Columns\TextColumn::make('logro.materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia'),
                Tables\Columns\TextColumn::make('logro.materia.docente.name')
                    ->searchable()
                    ->sortable()
                    ->label('Docente'),
                Tables\Columns\TextColumn::make('logro.titulo')
                    ->searchable()
                    ->sortable()
                    ->label('Título del Logro')
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('nivel_desempeno')
                    ->colors([
                        'success' => 'E',
                        'info' => 'S',
                        'warning' => 'A',
                        'danger' => 'I',
                    ])
                    ->searchable()
                    ->sortable()
                    ->label('Nivel de Desempeño')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'E' => 'E - Excelente',
                            'S' => 'S - Sobresaliente',
                            'A' => 'A - Aceptable',
                            'I' => 'I - Insuficiente',
                            default => $state
                        };
                    }),
                Tables\Columns\TextColumn::make('periodo.periodo_completo')
                    ->searchable()
                    ->sortable()
                    ->label('Período'),
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->date()
                    ->sortable()
                    ->label('Fecha de Asignación'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periodo_id')
                    ->relationship('periodo', 'corte')
                    ->label('Período')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->periodo_completo;
                    }),
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('logro.materia', 'nombre')
                    ->label('Materia'),
                Tables\Filters\SelectFilter::make('nivel_desempeno')
                    ->options([
                        'E' => 'E - Excelente',
                        'S' => 'S - Sobresaliente',
                        'A' => 'A - Aceptable',
                        'I' => 'I - Insuficiente',
                    ])
                    ->label('Nivel de Desempeño'),
                Tables\Filters\SelectFilter::make('estudiante_id')
                    ->relationship('estudiante', 'nombre')
                    ->label('Estudiante'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotas::route('/'),
            'create' => Pages\CreateNota::route('/create'),
            'edit' => Pages\EditNota::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
        if ($user && $user->hasRole('profesor')) {
            // Solo mostrar notas de logros de las materias del profesor
            $materiaIds = $user->materias()->pluck('id');
            $query->whereHas('logro', function ($q) use ($materiaIds) {
                $q->whereIn('materia_id', $materiaIds);
            });
        }
        return $query;
    }
} 