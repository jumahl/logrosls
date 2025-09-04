<?php

namespace App\Filament\Resources\EstudianteResource\Pages;

use App\Filament\Resources\EstudianteResource;
use App\Imports\EstudiantesImport;
use App\Exports\EstudiantesPlantillaExport;
use App\Exports\EstudiantesExport;
use App\Models\Grado;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ListEstudiantes extends ListRecords
{
    protected static string $resource = EstudianteResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // Obtener grados disponibles según el rol
        $gradosDisponibles = $this->getGradosDisponibles();

        // Acción para exportar estudiantes (disponible para todos los roles)
        $actions[] = Actions\Action::make('exportar_estudiantes')
            ->label('Exportar Excel')
            ->icon('heroicon-o-document-arrow-down')
            ->color('primary')
            ->form([
                Select::make('grado_id')
                    ->label('Seleccionar Grado')
                    ->placeholder('Todos los grados permitidos')
                    ->options($gradosDisponibles)
                    ->helperText($user->hasRole('admin') 
                        ? 'Dejar vacío para exportar todos los grados' 
                        : 'Solo puede exportar grados donde enseña'),
            ])
            ->action(function (array $data) {
                try {
                    $gradoId = $data['grado_id'] ?? null;
                    $export = new EstudiantesExport($gradoId);
                    
                    $gradoNombre = $gradoId ? Grado::find($gradoId)?->nombre : 'todos_los_grados';
                    $filename = "estudiantes_{$gradoNombre}_" . now()->format('Y-m-d') . ".xlsx";
                    
                    return Excel::download($export, $filename);
                    
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al exportar')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        // Acciones solo para administradores
        if ($user && $user->hasRole('admin')) {
            
            // Descargar plantilla
            $actions[] = Actions\Action::make('descargar_plantilla')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->form([
                    Select::make('grado_id')
                        ->label('Grado para Importación')
                        ->placeholder('Incluir columna de grado en plantilla')
                        ->options($gradosDisponibles)
                        ->helperText('Si selecciona un grado, todos los estudiantes importados se asignarán a ese grado'),
                ])
                ->action(function (array $data) {
                    try {
                        $gradoId = $data['grado_id'] ?? null;
                        $incluirGrado = !$gradoId; // Si no hay grado seleccionado, incluir columna
                        $gradoNombre = $gradoId ? Grado::find($gradoId)?->nombre : null;
                        
                        $export = new EstudiantesPlantillaExport($incluirGrado, $gradoNombre);
                        $filename = $gradoId 
                            ? "plantilla_estudiantes_{$gradoNombre}.xlsx"
                            : "plantilla_estudiantes_con_grado.xlsx";
                            
                        return Excel::download($export, $filename);
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar plantilla')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });

            // Importar estudiantes
            $actions[] = Actions\Action::make('importar_estudiantes')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Select::make('grado_id')
                        ->label('Grado de Destino')
                        ->placeholder('Usar grado del archivo Excel')
                        ->options($gradosDisponibles)
                        ->helperText('Si selecciona un grado, todos los estudiantes se asignarán a este grado'),
                    FileUpload::make('archivo')
                        ->label('Archivo Excel')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel'
                        ])
                        ->helperText('Formatos: .xlsx, .xls. Máximo 2MB.')
                        ->maxSize(2048)
                        ->directory('temp/imports'),
                ])
                ->action(function (array $data) {
                    try {
                        $archivo = $data['archivo'];
                        $gradoId = $data['grado_id'] ?? null;
                        $rutaArchivo = storage_path('app/public/' . $archivo);
                        
                        $import = new EstudiantesImport($gradoId);
                        Excel::import($import, $rutaArchivo);
                        
                        $resultados = $import->getImportResults();
                        $failures = $import->failures();
                        $errors = $import->errors();
                        
                        // Construir mensaje de resultado
                        $mensaje = sprintf(
                            'Importación completada: %d estudiantes creados, %d actualizados.',
                            $resultados['created'],
                            $resultados['updated']
                        );
                        
                        if (count($failures) > 0 || count($errors) > 0) {
                            $mensaje .= sprintf(' Se encontraron %d errores.', count($failures) + count($errors));
                            
                            Notification::make()
                                ->title('Importación con errores')
                                ->body($mensaje)
                                ->warning()
                                ->duration(8000)
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Importación exitosa')
                                ->body($mensaje)
                                ->success()
                                ->send();
                        }
                        
                        // Limpiar archivo temporal
                        if (file_exists($rutaArchivo)) {
                            unlink($rutaArchivo);
                        }
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error en la importación')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }
        
        // Botón de crear estudiante
        if ($user && ($user->hasRole('admin') || ($user->hasRole('profesor') && $user->isDirectorGrupo()))) {
            $actions[] = Actions\CreateAction::make();
        }
        
        return $actions;
    }

    /**
     * Obtener grados disponibles según el rol del usuario
     */
    private function getGradosDisponibles(): array
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            // Admin puede ver todos los grados
            return Grado::orderBy('nombre')->orderBy('grupo')
                ->get()
                ->mapWithKeys(fn($grado) => [$grado->id => $grado->nombre_completo ?? $grado->nombre])
                ->toArray();
        } elseif ($user->hasRole('profesor')) {
            // Profesor solo puede ver grados donde enseña
            return Grado::whereHas('materias', function($query) use ($user) {
                $query->where('docente_id', $user->id);
            })
            ->orderBy('nombre')->orderBy('grupo')
            ->get()
            ->mapWithKeys(fn($grado) => [$grado->id => $grado->nombre_completo ?? $grado->nombre])
            ->toArray();
        }
        
        return [];
    }
}
