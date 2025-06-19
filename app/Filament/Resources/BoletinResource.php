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
                    ->options(Periodo::pluck('nombre', 'id'))
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
                            ->options(Periodo::pluck('nombre', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Periodo'),
                    ])
                    ->action(function (Estudiante $record, array $data) {
                        $periodo = Periodo::find($data['periodo_id']);
                        
                        // Obtener los logros del estudiante para el período seleccionado
                        $notas = $record->estudianteLogros()
                            ->whereHas('logro', function ($query) use ($periodo) {
                                $query->whereHas('periodos', function ($q) use ($periodo) {
                                    $q->where('periodos.id', $periodo->id);
                                });
                            })
                            ->with(['logro.materia', 'logro.grado'])
                            ->get()
                            ->groupBy('logro.materia.nombre');

                        // Generar el PDF
                        $pdf = PDF::loadView('boletines.academico', [
                            'estudiante' => $record,
                            'periodo' => $periodo,
                            'notas' => $notas,
                        ]);

                        // Generar un nombre para el archivo
                        $filename = "boletin_{$record->nombre}_{$record->apellido}_{$periodo->nombre}.pdf";

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
                            ->options(Periodo::pluck('nombre', 'id'))
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
                        $zipName = "boletines_grado_{$grado->nombre}_periodo_{$periodo->nombre}.zip";
                        $zipPath = tempnam(sys_get_temp_dir(), 'zip');
                        
                        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                            foreach ($estudiantes as $estudiante) {
                                $notas = $estudiante->estudianteLogros()
                                    ->whereHas('logro', function ($query) use ($periodo) {
                                        $query->whereHas('periodos', function ($q) use ($periodo) {
                                            $q->where('periodos.id', $periodo->id);
                                        });
                                    })
                                    ->with(['logro.materia', 'logro.grado'])
                                    ->get()
                                    ->groupBy('logro.materia.nombre');

                                $pdf = PDF::loadView('boletines.academico', [
                                    'estudiante' => $estudiante,
                                    'periodo' => $periodo,
                                    'notas' => $notas,
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