<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotaResource\Pages;
use App\Models\EstudianteLogro;
use App\Models\ReporteMateria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Rules\FechaNoPosterior;
use App\Rules\EstudianteLogroUnico;

class NotaResource extends Resource
{
    protected static ?string $model = ReporteMateria::class;

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
                    ->label('Estudiante')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar materia y logro cuando cambie el estudiante
                        $set('materia_id', null);
                        $set('logro_id', null);
                    }),
                Forms\Components\Select::make('materia_id')
                    ->options(function ($get, $record) use ($user) {
                        $estudianteId = $get('estudiante_id');
                        
                        if ($record && request()->routeIs('filament.resources.nota-resource.edit')) {
                            // En edición, obtener la materia del logro asociado
                            if ($record->logro && $record->logro->materia) {
                                return [$record->logro->materia_id => $record->logro->materia->nombre];
                            }
                        }
                        
                        if ($estudianteId) {
                            $estudiante = \App\Models\Estudiante::find($estudianteId);
                            if ($estudiante && $estudiante->grado) {
                                // Obtener materias del grado del estudiante
                                // Especificar explícitamente las columnas para evitar ambigüedad
                                $materias = $estudiante->grado->materias()
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
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Limpiar el logro seleccionado cuando cambie la materia
                        $set('logro_id', null);
                    })
                    ->disabled(fn ($get, $record) => request()->routeIs('filament.resources.nota-resource.edit') || !$get('estudiante_id'))
                    ->dehydrated(true)
                    ->afterStateHydrated(function ($state, $record, Forms\Set $set) {
                        // En modo de edición, establecer la materia del logro
                        if ($record && $record->logro) {
                            $set('materia_id', $record->logro->materia_id);
                        }
                    }),
                Forms\Components\Select::make('logros')
                    ->options(function ($get, $record) use ($user) {
                        $materiaId = $get('materia_id');
                        $estudianteId = $get('estudiante_id');
                        
                        // En modo edición, obtener la materia del logro asociado
                        if (request()->routeIs('filament.resources.nota-resource.edit') && $record) {
                            $materiaId = $record->logro->materia_id;
                        }
                        
                        if ($materiaId && $estudianteId) {
                            $estudiante = \App\Models\Estudiante::find($estudianteId);
                            if ($estudiante && $estudiante->grado) {
                                // Obtener logros de la materia que pertenecen al grado del estudiante
                                $query = \App\Models\Logro::where('materia_id', $materiaId)
                                    ->where('activo', true)
                                    ->whereHas('materia.grados', function ($q) use ($estudiante) {
                                        $q->where('grado_id', $estudiante->grado_id);
                                    })
                                    ->select('id', 'codigo', 'titulo', 'desempeno', 'orden')
                                    ->orderBy('orden')
                                    ->orderBy('titulo');
                                
                                // Si es profesor, verificar que la materia le pertenezca
                                if ($user && $user->hasRole('profesor')) {
                                    $materiaIds = $user->materias()->pluck('id');
                                    if (!$materiaIds->contains($materiaId)) {
                                        return [];
                                    }
                                }
                                
                                // Construir opciones con código, título y desempeño
                                return $query->get()->mapWithKeys(function ($logro) {
                                    // Formato más claro y completo
                                    $codigo = $logro->codigo ? "[{$logro->codigo}] " : "";
                                    $titulo = $logro->titulo ? "{$logro->titulo}" : "";
                                    
                                    // Limpiar y formatear la descripción del desempeño
                                    $desempeno = trim($logro->desempeno ?? '');
                                    $desempeno = preg_replace('/\s+/', ' ', $desempeno);
                                    
                                    // Construir la etiqueta del selector con formato mejorado
                                    $label = $codigo;
                                    
                                    if (!empty($titulo)) {
                                        $label .= $titulo;
                                        
                                        // Agregar desempeño solo si hay título y desempeño
                                        if (!empty($desempeno)) {
                                            $label .= " — {$desempeno}";
                                        }
                                    } else {
                                        // Si no hay título, mostrar solo el desempeño
                                        $label .= $desempeno;
                                    }
                                    
                                    return [$logro->id => $label];
                                });
                            }
                        }
                        return [];
                    })
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->label('Logros')
                    ->helperText(function() {
                        if (request()->routeIs('filament.resources.nota-resource.edit')) {
                            return 'Estos logros están asignados al estudiante para esta materia en este período. Puede modificar la selección.';
                        }
                        return 'Seleccione los logros que desea asignar al estudiante. Puede seleccionar múltiples logros de la materia seleccionada.';
                    }),
                Forms\Components\Select::make('periodo_id')
                    ->relationship('periodo', 'corte')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return $record->periodo_completo;
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Período')
                    ->disabled(fn ($livewire) => $livewire instanceof Pages\EditNota),
                Forms\Components\Select::make('nivel_desempeno')
                    ->options([
                        'E' => 'E - Excelente',
                        'S' => 'S - Sobresaliente',
                        'A' => 'A - Aceptable',
                        'I' => 'I - Insuficiente',
                    ])
                    ->required()
                    ->label('Nivel de Desempeño')
                    ->helperText(function () {
                        if (request()->routeIs('filament.resources.nota-resource.edit')) {
                            return 'Este valor se aplicará a todos los logros seleccionados.';
                        }
                        return 'Seleccione el nivel de desempeño alcanzado por el estudiante en este logro';
                    }),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Observaciones')
                    ->helperText(function () {
                        if (request()->routeIs('filament.resources.nota-resource.edit')) {
                            return 'Estas observaciones se aplicarán a todos los logros seleccionados.';
                        }
                        return 'Comentarios adicionales sobre el desempeño del estudiante';
                    }),
                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->required()
                    ->label('Fecha de Asignación')
                    ->default(now())
                    ->rules([new FechaNoPosterior()])
                    ->helperText(function () {
                        if (request()->routeIs('filament.resources.nota-resource.edit')) {
                            return 'Esta fecha se aplicará a todos los logros seleccionados.';
                        }
                        return 'No puede ser una fecha futura';
                    }),
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
                    ->label('Materia')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->logro->materia->nombre;
                    })
                    ->color('primary'),
                Tables\Columns\TextColumn::make('logro.materia.docente.name')
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
                    ->getStateUsing(function ($record) {
                        // Obtener el nivel de desempeño promedio para esta materia
                        $materiaId = $record->logro->materia_id;
                        $nivel = ReporteMateria::getNivelDesempenoPromedio(
                            $record->estudiante_id,
                            $materiaId,
                            $record->periodo_id
                        );
                        
                        return $nivel ?: $record->nivel_desempeno;
                    })
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'E' => 'E - Excelente',
                            'S' => 'S - Sobresaliente',
                            'A' => 'A - Aceptable',
                            'I' => 'I - Insuficiente',
                            default => $state
                        };
                    }),
                    
                Tables\Columns\TextColumn::make('logros_count')
                    ->label('Logros Evaluados')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        // Contar logros del mismo estudiante, materia y periodo
                        $materiaId = $record->logro->materia_id;
                        return ReporteMateria::countLogrosPorMateria(
                            $record->estudiante_id, 
                            $materiaId, 
                            $record->periodo_id
                        );
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
                    ->visible(fn() => auth()->user()?->hasRole('admin'))
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar esta nota?')
                    ->modalDescription('Al eliminar esta nota, se eliminarán todos los logros de esta materia para este estudiante en este período.')
                    ->successNotificationTitle('Notas eliminadas correctamente')
                    ->action(function ($record) {
                        // Obtener la materia del logro
                        $materiaId = $record->logro->materia_id;
                        $estudianteId = $record->estudiante_id;
                        $periodoId = $record->periodo_id;
                        
                        // Eliminar todos los registros con la misma materia, estudiante y período
                        $registros = \App\Models\EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
                            $query->where('materia_id', $materiaId);
                        })
                        ->where('estudiante_id', $estudianteId)
                        ->where('periodo_id', $periodoId)
                        ->get();
                        
                        $count = $registros->count();
                        
                        foreach ($registros as $registro) {
                            $registro->delete();
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Notas eliminadas")
                            ->body("Se han eliminado {$count} notas de esta materia.")
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->hasRole('admin'))
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar estas notas?')
                        ->modalDescription('Al eliminar estas notas, se eliminarán todos los logros de estas materias para los estudiantes seleccionados.')
                        ->successNotificationTitle('Notas eliminadas correctamente')
                        ->action(function ($records) {
                            $totalEliminados = 0;
                            
                            foreach ($records as $record) {
                                // Obtener la materia del logro
                                $materiaId = $record->logro->materia_id;
                                $estudianteId = $record->estudiante_id;
                                $periodoId = $record->periodo_id;
                                
                                // Eliminar todos los registros con la misma materia, estudiante y período
                                $registros = \App\Models\EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
                                    $query->where('materia_id', $materiaId);
                                })
                                ->where('estudiante_id', $estudianteId)
                                ->where('periodo_id', $periodoId)
                                ->get();
                                
                                $count = $registros->count();
                                $totalEliminados += $count;
                                
                                foreach ($registros as $registro) {
                                    $registro->delete();
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title("Notas eliminadas")
                                ->body("Se han eliminado {$totalEliminados} notas en total.")
                                ->send();
                        }),
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
        $query = parent::getEloquentQuery()->with([
            'estudiante.grado',
            'logro.materia.docente',
            'periodo'
        ]);
        
        if ($user && $user->hasRole('profesor')) {
            $materiaIds = $user->materias()->pluck('id');
            $query->whereHas('logro', function ($q) use ($materiaIds) {
                $q->whereIn('materia_id', $materiaIds);
            });
        }
        
        // Si estamos en modo de edición o creación, no aplicamos agrupación
        if (request()->routeIs('filament.resources.nota-resource.edit') || request()->routeIs('filament.resources.nota-resource.create')) {
            return $query;
        }
        
        // Para el listado, usamos un enfoque diferente para agrupar
        // Primero, obtenemos los IDs únicos para cada combinación estudiante/periodo/materia
        $uniqueIds = \DB::table('estudiante_logros')
            ->join('logros', 'estudiante_logros.logro_id', '=', 'logros.id')
            ->select(
                'estudiante_logros.estudiante_id',
                'estudiante_logros.periodo_id',
                'logros.materia_id',
                \DB::raw('MIN(estudiante_logros.id) as id')
            )
            ->groupBy('estudiante_logros.estudiante_id', 'estudiante_logros.periodo_id', 'logros.materia_id')
            ->pluck('id');
        
        // Luego, filtramos para mostrar solo los IDs que representan cada combinación única
        $query->whereIn('id', $uniqueIds);
        
        return $query;
    }
    
    // Cargamos los logros relacionados para una materia específica
    public static function mountFormData($record = null): array
    {
        $data = parent::mountFormData($record);
        
        // Si estamos en modo de edición y tenemos un record, cargar todos los logros de esa materia
        if ($record && request()->routeIs('filament.resources.nota-resource.edit')) {
            $materiaId = $record->logro->materia_id;
            $estudianteId = $record->estudiante_id;
            $periodoId = $record->periodo_id;
            
            // Obtener todos los IDs de logros relacionados con esta materia para este estudiante/periodo
            $logrosIds = \App\Models\EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
                $query->where('materia_id', $materiaId);
            })
            ->where('estudiante_id', $estudianteId)
            ->where('periodo_id', $periodoId)
            ->pluck('logro_id')
            ->toArray();
            
            // Asignar los logros al formulario
            $data['logros'] = $logrosIds;
        }
        
        return $data;
    }
} 