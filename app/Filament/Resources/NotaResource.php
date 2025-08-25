<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Models\DesempenoMateria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Rules\FechaNoPosterior;

class NotaResource extends Resource
{
    protected static ?string $model = DesempenoMateria::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $navigationLabel = 'Calificaciones';
    
    protected static ?string $modelLabel = 'Calificación';
    
    protected static ?string $pluralModelLabel = 'Calificaciones';
    
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Reportes';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        return $form
            ->schema([
                Forms\Components\Select::make('grado_id')
                    ->relationship('estudiante.grado', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Grado')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('estudiante_id', null);
                        $set('materia_id', null);
                    }),
                    
                Forms\Components\Select::make('estudiante_id')
                    ->options(function ($get) {
                        $gradoId = $get('grado_id');
                        
                        if ($gradoId) {
                            return \App\Models\Estudiante::where('grado_id', $gradoId)
                                ->where('activo', true)
                                ->orderBy('apellido')
                                ->orderBy('nombre')
                                ->get()
                                ->mapWithKeys(function ($estudiante) {
                                    return [$estudiante->id => $estudiante->nombre_completo];
                                });
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->label('Estudiante')
                    ->live()
                    ->disabled(fn ($get) => !$get('grado_id'))
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('materia_id', null);
                    }),
                    
                Forms\Components\Select::make('materia_id')
                    ->options(function ($get) use ($user) {
                        $gradoId = $get('grado_id');
                        
                        if ($gradoId) {
                            $grado = \App\Models\Grado::find($gradoId);
                            if ($grado) {
                                $materias = $grado->materias()
                                    ->select('materias.id', 'materias.nombre')
                                    ->where('materias.activa', true);
                                
                                // Si es profesor, filtrar solo sus materias
                                if ($user && $user->hasRole('profesor')) {
                                    $materias = $materias->whereIn('materias.id', $user->materias()->pluck('id'));
                                }
                                
                                return $materias->pluck('materias.nombre', 'materias.id');
                            }
                        }
                        return [];
                    })
                    ->required()
                    ->searchable()
                    ->label('Materia')
                    ->live()
                    ->disabled(fn ($get) => !$get('grado_id')),
                    
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
                    ->helperText('Calificación consolidada para la materia'),
                    
                Forms\Components\Select::make('logros')
                    ->multiple()
                    ->options(function ($get) use ($user) {
                        $materiaId = $get('materia_id');
                        $gradoId = $get('grado_id');
                        
                        if ($materiaId && $gradoId) {
                            $query = \App\Models\Logro::where('materia_id', $materiaId)
                                ->where('activo', true)
                                ->whereHas('materia.grados', function ($q) use ($gradoId) {
                                    $q->where('grado_id', $gradoId);
                                })
                                ->orderBy('orden')
                                ->orderBy('titulo');
                            
                            // Si es profesor, verificar que la materia le pertenezca
                            if ($user && $user->hasRole('profesor')) {
                                $materiaIds = $user->materias()->pluck('id');
                                if (!$materiaIds->contains($materiaId)) {
                                    return [];
                                }
                            }
                            
                            return $query->get()->mapWithKeys(function ($logro) {
                                $codigo = $logro->codigo ? "[{$logro->codigo}] " : "";
                                $titulo = $logro->titulo ? "{$logro->titulo}" : "";
                                $desempeno = trim($logro->desempeno ?? '');
                                
                                $label = $codigo . $titulo;
                                if (!empty($desempeno)) {
                                    $label .= " — {$desempeno}";
                                }
                                
                                return [$logro->id => $label];
                            });
                        }
                        return [];
                    })
                    ->searchable()
                    ->label('Logros Asociados')
                    ->helperText('Seleccione los logros que evidencian esta calificación')
                    ->disabled(fn ($get) => !$get('materia_id')),
                    
                Forms\Components\Select::make('estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'publicado' => 'Publicado',
                        'revisado' => 'Revisado',
                    ])
                    ->default('borrador')
                    ->required()
                    ->label('Estado')
                    ->helperText('Estado de la calificación'),
                    
                Forms\Components\Textarea::make('observaciones_finales')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones Finales')
                    ->helperText('Comentarios consolidados sobre el desempeño del estudiante en la materia'),
                    
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación')
                    ->default(now())
                    ->rules([new FechaNoPosterior()])
                    ->helperText('Fecha de la evaluación'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('estudiante.nombre_completo')
                    ->label('Estudiante')
                    ->searchable(['estudiante.nombre', 'estudiante.apellido'])
                    ->sortable(['estudiantes.apellido', 'estudiantes.nombre'])
                    ->getStateUsing(function ($record) {
                        return $record->estudiante->nombre_completo;
                    }),
                    
                Tables\Columns\TextColumn::make('materia.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Materia')
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('materia.docente.name')
                    ->searchable()
                    ->sortable()
                    ->label('Docente'),
                    
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
                    
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'borrador',
                        'success' => 'publicado',
                        'info' => 'revisado',
                    ])
                    ->searchable()
                    ->sortable()
                    ->label('Estado'),
                    
                Tables\Columns\TextColumn::make('logros_count')
                    ->label('Logros Asociados')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        return $record->estudianteLogros()->count();
                    }),
                    
                Tables\Columns\TextColumn::make('periodo.periodo_completo')
                    ->searchable()
                    ->sortable()
                    ->label('Período'),
                    
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->date()
                    ->sortable()
                    ->label('Fecha de Asignación'),
                    
                Tables\Columns\IconColumn::make('locked_at')
                    ->label('Bloqueado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !is_null($record->locked_at))
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grado_id')
                    ->label('Grado')
                    ->options(function () use ($user) {
                        if ($user && $user->hasRole('profesor')) {
                            $materiaIds = $user->materias()->pluck('id');
                            $gradoIds = \App\Models\Grado::whereHas('materias', function ($q) use ($materiaIds) {
                                $q->whereIn('materias.id', $materiaIds);
                            })->pluck('nombre', 'id');
                            return $gradoIds->toArray();
                        }
                        return \App\Models\Grado::pluck('nombre', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('estudiante', function ($q) use ($data) {
                                $q->where('grado_id', $data['value']);
                            });
                        }
                    }),
                    
                Tables\Filters\SelectFilter::make('periodo_id')
                    ->relationship('periodo', 'corte')
                    ->label('Período')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->periodo_completo;
                    }),
                    
                Tables\Filters\SelectFilter::make('materia_id')
                    ->relationship('materia', 'nombre')
                    ->label('Materia'),
                    
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'publicado' => 'Publicado',
                        'revisado' => 'Revisado',
                    ])
                    ->label('Estado'),
                    
                Tables\Filters\SelectFilter::make('nivel_desempeno')
                    ->options([
                        'E' => 'Excelente',
                        'S' => 'Sobresaliente',
                        'A' => 'Aceptable',
                        'I' => 'Insuficiente',
                    ])
                    ->label('Nivel de Desempeño'),
            ])
            ->actions([
                Tables\Actions\Action::make('bloquear')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->action(function ($record) {
                        $record->bloquear();
                    })
                    ->visible(fn($record) => 
                        !$record->locked_at && 
                        auth()->user()->can('lock', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('¿Bloquear calificación?')
                    ->modalDescription('Una vez bloqueada, la calificación no podrá ser editada.'),
                    
                Tables\Actions\Action::make('desbloquear')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->action(function ($record) {
                        $record->desbloquear();
                    })
                    ->visible(fn($record) => 
                        $record->locked_at && 
                        auth()->user()->can('lock', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('¿Desbloquear calificación?')
                    ->modalDescription('La calificación podrá ser editada nuevamente.'),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => 
                        !$record->locked_at && 
                        auth()->user()->can('update', $record)
                    ),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => 
                        !$record->locked_at && 
                        auth()->user()->can('delete', $record)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()->can('deleteAny', DesempenoMateria::class)),
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
        $query = parent::getEloquentQuery()
            ->select('desempenos_materia.*')
            ->join('estudiantes', 'desempenos_materia.estudiante_id', '=', 'estudiantes.id')
            ->with([
                'estudiante.grado',
                'materia.docente',
                'periodo',
                'estudianteLogros.logro'
            ]);
        
        if ($user && $user->hasRole('profesor')) {
            $materiaIds = $user->materias()->pluck('id');
            $query->whereIn('desempenos_materia.materia_id', $materiaIds);
        }
        
        return $query;
    }
} 