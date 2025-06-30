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
        return $form
            ->schema([
                Forms\Components\Select::make('grado_id')
                    ->relationship('grado', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Grado'),
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
                Tables\Actions\Action::make('descargarBoletin')
                    ->label('Descargar Boletín')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
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
                Tables\Actions\Action::make('descargarBoletinesGrado')
                    ->label('Descargar Boletines por Grado')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Select::make('grado_id')
                            ->relationship('grado', 'nombre')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Grado'),
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
} 