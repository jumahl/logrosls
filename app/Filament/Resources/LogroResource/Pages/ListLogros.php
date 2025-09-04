<?php

namespace App\Filament\Resources\LogroResource\Pages;

use App\Filament\Resources\LogroResource;
use App\Imports\LogrosImport;
use App\Exports\LogrosPlantillaExport;
use App\Exports\LogrosExport;
use App\Models\Materia;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ListLogros extends ListRecords
{
    protected static string $resource = LogroResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $actions = [];

        // Obtener materias disponibles según el rol
        $materiasDisponibles = $this->getMateriasDisponibles();

        // Acción para exportar logros (disponible para todos los roles)
        $actions[] = Actions\Action::make('exportar_logros')
            ->label('Exportar Excel')
            ->icon('heroicon-o-document-arrow-down')
            ->color('primary')
            ->form([
                Select::make('materia_id')
                    ->label('Seleccionar Materia')
                    ->placeholder('Todas las materias permitidas')
                    ->options($materiasDisponibles)
                    ->helperText($user->hasRole('admin') 
                        ? 'Dejar vacío para exportar todas las materias' 
                        : 'Solo puede exportar materias que enseña'),
            ])
            ->action(function (array $data) {
                try {
                    $materiaId = $data['materia_id'] ?? null;
                    $export = new LogrosExport($materiaId);
                    
                    $materiaNombre = $materiaId ? Materia::find($materiaId)?->codigo : 'todas_las_materias';
                    $filename = "logros_{$materiaNombre}_" . now()->format('Y-m-d') . ".xlsx";
                    
                    return Excel::download($export, $filename);
                    
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al exportar')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        // Acciones para administradores y profesores (pueden importar)
        if ($user && ($user->hasRole('admin') || $user->hasRole('profesor'))) {
            
            // Descargar plantilla
            $actions[] = Actions\Action::make('descargar_plantilla')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->form([
                    Select::make('materia_id')
                        ->label('Materia para Importación')
                        ->placeholder('Incluir columnas de materia en plantilla')
                        ->options($materiasDisponibles)
                        ->helperText('Si selecciona una materia, todos los logros importados se asignarán a esa materia'),
                ])
                ->action(function (array $data) {
                    try {
                        $materiaId = $data['materia_id'] ?? null;
                        $incluirMateria = !$materiaId; // Si no hay materia seleccionada, incluir columnas
                        
                        $materiaNombre = null;
                        $materiaCodigo = null;
                        if ($materiaId) {
                            $materia = Materia::find($materiaId);
                            $materiaNombre = $materia?->nombre;
                            $materiaCodigo = $materia?->codigo;
                        }
                        
                        $export = new LogrosPlantillaExport($incluirMateria, $materiaNombre, $materiaCodigo);
                        $filename = $materiaId 
                            ? "plantilla_logros_{$materiaCodigo}.xlsx"
                            : "plantilla_logros_con_materia.xlsx";
                            
                        return Excel::download($export, $filename);
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al generar plantilla')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });

            // Importar logros
            $actions[] = Actions\Action::make('importar_logros')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Select::make('materia_id')
                        ->label('Materia de Destino')
                        ->placeholder('Usar materia del archivo Excel')
                        ->options($materiasDisponibles)
                        ->helperText('Si selecciona una materia, todos los logros se asignarán a esta materia'),
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
                        $materiaId = $data['materia_id'] ?? null;
                        $rutaArchivo = storage_path('app/public/' . $archivo);
                        
                        $import = new LogrosImport($materiaId);
                        Excel::import($import, $rutaArchivo);
                        
                        $resultados = $import->getImportResults();
                        $failures = $import->failures();
                        $errors = $import->errors();
                        
                        // Construir mensaje de resultado
                        $mensaje = sprintf(
                            'Importación completada: %d logros creados, %d actualizados.',
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
        
        // Botón de crear logro
        $actions[] = Actions\CreateAction::make();
        
        return $actions;
    }

    /**
     * Obtener materias disponibles según el rol del usuario
     */
    private function getMateriasDisponibles(): array
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            // Admin puede ver todas las materias
            return Materia::orderBy('nombre')
                ->get()
                ->mapWithKeys(fn($materia) => [$materia->id => "{$materia->nombre} ({$materia->codigo})"])
                ->toArray();
        } elseif ($user->hasRole('profesor')) {
            // Profesor solo puede ver materias que enseña
            return Materia::where('docente_id', $user->id)
                ->orderBy('nombre')
                ->get()
                ->mapWithKeys(fn($materia) => [$materia->id => "{$materia->nombre} ({$materia->codigo})"])
                ->toArray();
        }
        
        return [];
    }
}
