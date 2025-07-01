<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoletinResource\Pages;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Periodo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class BoletinResource extends Resource
{
    protected static ?string $model = Estudiante::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Boletines';
    
    protected static ?string $modelLabel = 'Boletín';
    
    protected static ?string $pluralModelLabel = 'Boletines';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = 'Reportes';

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        
        return $form
            ->schema([
                Forms\Components\Select::make('grado_id')
                    ->options(function () use ($user) {
                        $query = Grado::where('activo', true);
                        
                        // Si el usuario es profesor y es director de grupo, solo mostrar su grado
                        if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                            $query->where('id', $user->director_grado_id);
                        }
                        // Si es profesor pero no es director de grupo, mostrar grados donde tiene materias
                        elseif ($user && $user->hasRole('profesor')) {
                            $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                            $query->whereIn('id', $gradoIds);
                        }
                        
                        return $query->pluck('nombre', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Grado')
                    ->disabled(fn() => auth()->user()?->hasRole('profesor') && auth()->user()?->isDirectorGrupo()),
                Forms\Components\Select::make('periodo_id')
                    ->options(function () {
                        return Periodo::all()->mapWithKeys(function ($periodo) {
                            return [$periodo->id => $periodo->periodo_completo];
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Periodo'),
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
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable()
                    ->sortable()
                    ->label('Apellido'),
                Tables\Columns\TextColumn::make('grado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Grado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grado_id')
                    ->relationship('grado', 'nombre')
                    ->label('Grado'),
            ])
            ->actions([
                Tables\Actions\Action::make('descargarPreinforme')
                    ->label('Descargar Preinforme')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Forms\Components\Select::make('periodo_id')
                            ->options(function () {
                                return Periodo::where('corte', 'Primer Corte')
                                    ->get()
                                    ->mapWithKeys(function ($periodo) {
                                        return [$periodo->id => $periodo->periodo_completo];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Período (Primer Corte)')
                            ->helperText('Solo se muestran períodos del primer corte'),
                    ])
                    ->action(function (Estudiante $record, array $data) {
                        $periodo = Periodo::find($data['periodo_id']);
                        
                        if ($periodo->corte !== 'Primer Corte') {
                            throw new \Exception('Solo se pueden generar preinformes para períodos del primer corte.');
                        }

                        // Obtener logros del estudiante en el período (primer corte)
                        $logrosPorMateria = $record->estudianteLogros()
                            ->where('periodo_id', $periodo->id)
                            ->with(['logro.materia.docente', 'logro.materia'])
                            ->get()
                            ->groupBy(function ($logro) {
                                return $logro->logro->materia->nombre;
                            });

                        if ($logrosPorMateria->isEmpty()) {
                            throw new \Exception("El estudiante {$record->nombre} no tiene logros en el período {$periodo->periodo_completo}.");
                        }

                        // Generar PDF
                        $pdf = Pdf::loadView('boletines.preinforme', [
                            'estudiante' => $record,
                            'periodo' => $periodo,
                            'logrosPorMateria' => $logrosPorMateria,
                        ]);

                        // Generar un nombre para el archivo
                        $filename = "preinforme_{$record->nombre}_{$record->apellido}_{$periodo->periodo_completo}.pdf";

                        // Descargar el PDF directamente
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    }),
                Tables\Actions\Action::make('descargarBoletin')
                    ->label('Descargar Boletín')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Select::make('periodo_id')
                            ->options(function () {
                                return Periodo::where('corte', 'Segundo Corte')->get()->mapWithKeys(function ($periodo) {
                                    return [$periodo->id => $periodo->periodo_completo];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Periodo (Solo Segundo Corte)'),
                    ])
                    ->action(function (Estudiante $record, array $data) {
                        $periodo = Periodo::find($data['periodo_id']);
                        
                        // Obtener el período anterior (primer corte del mismo período)
                        $periodoAnterior = $periodo->periodo_anterior;
                        
                        // Obtener logros del primer corte
                        $logrosPrimerCorte = collect();
                        if ($periodoAnterior) {
                            $logrosPrimerCorte = $record->estudianteLogros()
                                ->where('periodo_id', $periodoAnterior->id)
                                ->with(['logro.materia.docente', 'logro.materia.grados'])
                                ->get();
                        }

                        // Obtener logros del segundo corte
                        $logrosSegundoCorte = $record->estudianteLogros()
                            ->where('periodo_id', $periodo->id)
                            ->with(['logro.materia.docente', 'logro.materia.grados'])
                            ->get();

                        // Combinar logros de ambos cortes
                        $todosLosLogros = $logrosPrimerCorte->concat($logrosSegundoCorte);
                        
                        // Agrupar por materia
                        $logrosPorMateria = $todosLosLogros->groupBy(function ($logro) {
                            return $logro->logro->materia->nombre;
                        });

                        // Obtener todas las materias del grado del estudiante
                        $materiasDelGrado = $record->grado->materias()->where('activa', true)->get();
                        
                        // Asegurar que todas las materias aparezcan en el boletín, incluso sin logros
                        foreach ($materiasDelGrado as $materia) {
                            if (!$logrosPorMateria->has($materia->nombre)) {
                                $logrosPorMateria->put($materia->nombre, collect());
                            }
                        }

                        // Calcular promedios por materia
                        $promediosPorMateria = [];
                        foreach ($logrosPorMateria as $materia => $logros) {
                            if ($logros->isNotEmpty()) {
                                $promedio = $logros->avg('valor_numerico');
                                $promediosPorMateria[$materia] = $promedio;
                            } else {
                                $promediosPorMateria[$materia] = 0;
                            }
                        }

                        // Generar el PDF
                        $pdf = PDF::loadView('boletines.academico', [
                            'estudiante' => $record,
                            'periodo' => $periodo,
                            'periodoAnterior' => $periodoAnterior,
                            'logrosPrimerCorte' => $logrosPrimerCorte,
                            'logrosSegundoCorte' => $logrosSegundoCorte,
                            'logrosPorMateria' => $logrosPorMateria,
                            'promediosPorMateria' => $promediosPorMateria,
                        ]);

                        // Generar un nombre para el archivo
                        $filename = "boletin_{$record->nombre}_{$record->apellido}_{$periodo->periodo_completo}.pdf";

                        // Descargar el PDF directamente
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('descargarPreinformesGrado')
                    ->label('Descargar Preinformes por Grado')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Forms\Components\Select::make('grado_id')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Grado::where('activo', true);
                                
                                // Si el usuario es profesor y es director de grupo, solo mostrar su grado
                                if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                                    $query->where('id', $user->director_grado_id);
                                }
                                // Si es profesor pero no es director de grupo, mostrar grados donde tiene materias
                                elseif ($user && $user->hasRole('profesor')) {
                                    $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                                    $query->whereIn('id', $gradoIds);
                                }
                                
                                return $query->pluck('nombre', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grado')
                            ->default(function () {
                                $user = auth()->user();
                                if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                                    return $user->director_grado_id;
                                }
                                return null;
                            })
                            ->disabled(fn() => auth()->user()?->hasRole('profesor') && auth()->user()?->isDirectorGrupo()),
                        Forms\Components\Select::make('periodo_id')
                            ->options(function () {
                                return Periodo::where('corte', 'Primer Corte')
                                    ->get()
                                    ->mapWithKeys(function ($periodo) {
                                        return [$periodo->id => $periodo->periodo_completo];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Período (Primer Corte)')
                            ->helperText('Solo se muestran períodos del primer corte'),
                    ])
                    ->action(function (array $data) {
                        $grado = Grado::find($data['grado_id']);
                        $periodo = Periodo::find($data['periodo_id']);
                        
                        if ($periodo->corte !== 'Primer Corte') {
                            throw new \Exception('Solo se pueden generar preinformes para períodos del primer corte.');
                        }
                        
                        $estudiantes = Estudiante::where('grado_id', $grado->id)->get();

                        // Crear un archivo ZIP en memoria
                        $zip = new \ZipArchive();
                        $zipName = "preinformes_grado_{$grado->nombre}_periodo_{$periodo->periodo_completo}.zip";
                        $zipPath = tempnam(sys_get_temp_dir(), 'zip');
                        
                        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                            foreach ($estudiantes as $estudiante) {
                                // Obtener logros del estudiante en el período
                                $logros = $estudiante->estudianteLogros()
                                    ->where('periodo_id', $periodo->id)
                                    ->with(['logro.materia.docente', 'logro.materia.grados'])
                                    ->get()
                                    ->groupBy(function ($logro) {
                                        return $logro->logro->materia->nombre;
                                    });

                                if (!$logros->isEmpty()) {
                                    $pdf = Pdf::loadView('boletines.preinforme', [
                                        'estudiante' => $estudiante,
                                        'periodo' => $periodo,
                                        'logros' => $logros,
                                    ]);

                                    $filename = "preinforme_{$estudiante->nombre}_{$estudiante->apellido}.pdf";
                                    $zip->addFromString($filename, $pdf->output());
                                }
                            }
                            $zip->close();

                            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
                        }

                        return null;
                    }),
                Tables\Actions\Action::make('descargarBoletinesGrado')
                    ->label('Descargar Boletines por Grado')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Select::make('grado_id')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Grado::where('activo', true);
                                
                                // Si el usuario es profesor y es director de grupo, solo mostrar su grado
                                if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                                    $query->where('id', $user->director_grado_id);
                                }
                                // Si es profesor pero no es director de grupo, mostrar grados donde tiene materias
                                elseif ($user && $user->hasRole('profesor')) {
                                    $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                                    $query->whereIn('id', $gradoIds);
                                }
                                
                                return $query->pluck('nombre', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grado')
                            ->default(function () {
                                $user = auth()->user();
                                if ($user && $user->hasRole('profesor') && $user->isDirectorGrupo()) {
                                    return $user->director_grado_id;
                                }
                                return null;
                            })
                            ->disabled(fn() => auth()->user()?->hasRole('profesor') && auth()->user()?->isDirectorGrupo()),
                        Forms\Components\Select::make('periodo_id')
                            ->options(function () {
                                return Periodo::all()->mapWithKeys(function ($periodo) {
                                    return [$periodo->id => $periodo->periodo_completo];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Periodo'),
                    ])
                    ->action(function (array $data) {
                        $grado = Grado::find($data['grado_id']);
                        $periodo = Periodo::find($data['periodo_id']);
                        $estudiantes = Estudiante::where('grado_id', $grado->id)->get();

                        // Crear un archivo ZIP en memoria
                        $zip = new \ZipArchive();
                        $zipName = "boletines_grado_{$grado->nombre}_periodo_{$periodo->periodo_completo}.zip";
                        $zipPath = tempnam(sys_get_temp_dir(), 'zip');
                        
                        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                            foreach ($estudiantes as $estudiante) {
                                // Obtener el período anterior (primer corte del mismo período)
                                $periodoAnterior = $periodo->periodo_anterior;
                                
                                // Obtener logros del primer corte
                                $logrosPrimerCorte = collect();
                                if ($periodoAnterior) {
                                    $logrosPrimerCorte = $estudiante->estudianteLogros()
                                        ->where('periodo_id', $periodoAnterior->id)
                                        ->with(['logro.materia.docente', 'logro.materia.grados'])
                                        ->get();
                                }

                                // Obtener logros del segundo corte
                                $logrosSegundoCorte = $estudiante->estudianteLogros()
                                    ->where('periodo_id', $periodo->id)
                                    ->with(['logro.materia.docente', 'logro.materia.grados'])
                                    ->get();

                                // Combinar logros de ambos cortes
                                $todosLosLogros = $logrosPrimerCorte->concat($logrosSegundoCorte);
                                
                                // Agrupar por materia
                                $logrosPorMateria = $todosLosLogros->groupBy(function ($logro) {
                                    return $logro->logro->materia->nombre;
                                });

                                // Obtener todas las materias del grado del estudiante
                                $materiasDelGrado = $estudiante->grado->materias()->where('activa', true)->get();
                                
                                // Asegurar que todas las materias aparezcan en el boletín, incluso sin logros
                                foreach ($materiasDelGrado as $materia) {
                                    if (!$logrosPorMateria->has($materia->nombre)) {
                                        $logrosPorMateria->put($materia->nombre, collect());
                                    }
                                }

                                // Calcular promedios por materia
                                $promediosPorMateria = [];
                                foreach ($logrosPorMateria as $materia => $logros) {
                                    if ($logros->isNotEmpty()) {
                                        $promedio = $logros->avg('valor_numerico');
                                        $promediosPorMateria[$materia] = $promedio;
                                    } else {
                                        $promediosPorMateria[$materia] = 0;
                                    }
                                }

                                $pdf = PDF::loadView('boletines.academico', [
                                    'estudiante' => $estudiante,
                                    'periodo' => $periodo,
                                    'periodoAnterior' => $periodoAnterior,
                                    'logrosPrimerCorte' => $logrosPrimerCorte,
                                    'logrosSegundoCorte' => $logrosSegundoCorte,
                                    'logrosPorMateria' => $logrosPorMateria,
                                    'promediosPorMateria' => $promediosPorMateria,
                                ]);

                                $filename = "boletin_{$estudiante->nombre}_{$estudiante->apellido}.pdf";
                                $zip->addFromString($filename, $pdf->output());
                            }
                            $zip->close();

                            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
                        }

                        return null;
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoletines::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();
        
        if ($user && $user->hasRole('profesor')) {
            // Si el profesor es director de grupo, solo mostrar estudiantes de su grupo
            if ($user->isDirectorGrupo()) {
                $query->where('grado_id', $user->director_grado_id);
            } else {
                // Si no es director de grupo, mostrar estudiantes de grados donde tiene materias
                $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                $query->whereIn('grado_id', $gradoIds);
            }
        }
        
        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        
        // Solo admin y profesores que son directores de grupo pueden ver boletines
        return $user && ($user->hasRole('admin') || ($user->hasRole('profesor') && $user->isDirectorGrupo()));
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }
        
        // Admin puede ver todos los boletines
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Profesor director de grupo solo puede ver boletines de su grupo
        if ($user->hasRole('profesor') && $user->isDirectorGrupo()) {
            return $record->grado_id === $user->director_grado_id;
        }
        
        return false;
    }
} 