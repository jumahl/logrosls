<?php

namespace App\Filament\Resources\NotaResource\Pages;

use App\Filament\Resources\NotaResource;
use App\Models\EstudianteLogro;
use App\Models\ReporteMateria;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditNota extends EditRecord
{
    protected static string $resource = NotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('¿Eliminar esta nota?')
                ->modalDescription('Al eliminar esta nota, se eliminarán todos los logros de esta materia para este estudiante en este período.')
                ->successNotificationTitle('Notas eliminadas correctamente')
                ->action(function () {
                    $record = $this->record;
                    
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
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Nota editada')
            ->body('La nota ha sido editada correctamente.');
    }
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Primero obtenemos los datos importantes
        $estudianteId = $record->estudiante_id;
        $periodoId = $record->periodo_id;
        $materiaId = $record->logro->materia_id;
        $nivelDesempeno = $data['nivel_desempeno'];
        $observaciones = $data['observaciones'] ?? null;
        $fechaAsignacion = $data['fecha_asignacion'];
        
        // Obtener todos los logros existentes para esta materia/estudiante/periodo
        $logrosExistentes = \App\Models\EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
                $query->where('materia_id', $materiaId);
            })
            ->where('estudiante_id', $estudianteId)
            ->where('periodo_id', $periodoId)
            ->pluck('logro_id')
            ->toArray();
        
        // Obtener los logros seleccionados en el formulario
        $logrosSeleccionados = $data['logros'];
        
        // 1. Eliminar logros que ya no están seleccionados
        foreach ($logrosExistentes as $logroId) {
            if (!in_array($logroId, $logrosSeleccionados)) {
                \App\Models\EstudianteLogro::where('estudiante_id', $estudianteId)
                    ->where('logro_id', $logroId)
                    ->where('periodo_id', $periodoId)
                    ->delete();
            }
        }
        
        // 2. Actualizar logros existentes que siguen seleccionados
        foreach ($logrosSeleccionados as $logroId) {
            if (in_array($logroId, $logrosExistentes)) {
                \App\Models\EstudianteLogro::where('estudiante_id', $estudianteId)
                    ->where('logro_id', $logroId)
                    ->where('periodo_id', $periodoId)
                    ->update([
                        'nivel_desempeno' => $nivelDesempeno,
                        'observaciones' => $observaciones,
                        'fecha_asignacion' => $fechaAsignacion,
                    ]);
            } else {
                // 3. Crear nuevos registros para logros que no existían
                \App\Models\EstudianteLogro::create([
                    'estudiante_id' => $estudianteId,
                    'logro_id' => $logroId,
                    'periodo_id' => $periodoId,
                    'nivel_desempeno' => $nivelDesempeno,
                    'observaciones' => $observaciones,
                    'fecha_asignacion' => $fechaAsignacion,
                ]);
            }
        }
        
        // Notificar al usuario sobre la actualización
        $countActualizados = count(array_intersect($logrosExistentes, $logrosSeleccionados));
        $countNuevos = count(array_diff($logrosSeleccionados, $logrosExistentes));
        $countEliminados = count(array_diff($logrosExistentes, $logrosSeleccionados));
        
        $mensaje = "Se han actualizado {$countActualizados} logros existentes";
        if ($countNuevos > 0) $mensaje .= ", agregado {$countNuevos} nuevos";
        if ($countEliminados > 0) $mensaje .= " y eliminado {$countEliminados}";
        $mensaje .= ".";
        
        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Logros actualizados')
            ->body($mensaje)
            ->send();
        
        // Devolver el registro original actualizado
        $record->nivel_desempeno = $nivelDesempeno;
        $record->observaciones = $observaciones;
        $record->fecha_asignacion = $fechaAsignacion;
        $record->save();
        
        return $record;
    }
    
    // Asegurar que los logros aparezcan correctamente en el formulario
    protected function fillForm(): void
    {
        parent::fillForm();
        
        // Si tenemos un registro, cargar todos los logros relacionados y los datos actuales
        if ($this->record) {
            $materiaId = $this->record->logro->materia_id;
            $estudianteId = $this->record->estudiante_id;
            $periodoId = $this->record->periodo_id;
            
            // Obtener todos los IDs de logros relacionados con esta materia para este estudiante/periodo
            $logrosRelacionados = \App\Models\EstudianteLogro::whereHas('logro', function ($query) use ($materiaId) {
                $query->where('materia_id', $materiaId);
            })
            ->where('estudiante_id', $estudianteId)
            ->where('periodo_id', $periodoId)
            ->get();
            
            $logrosIds = $logrosRelacionados->pluck('logro_id')->toArray();
            
            // Verificamos si hay más de un registro y si todos comparten el mismo nivel de desempeño y fecha
            $todosNivelIgual = $logrosRelacionados->pluck('nivel_desempeno')->unique()->count() === 1;
            $todosFechaIgual = $logrosRelacionados->pluck('fecha_asignacion')->unique()->count() === 1;
            $todasObsIgual = $logrosRelacionados->pluck('observaciones')->unique()->count() === 1;
            
            $data = [
                'logros' => $logrosIds
            ];
            
            // Si todos los registros tienen el mismo nivel de desempeño, usamos ese
            if ($todosNivelIgual) {
                $data['nivel_desempeno'] = $logrosRelacionados->first()->nivel_desempeno;
            }
            
            // Si todos los registros tienen la misma fecha, usamos esa
            if ($todosFechaIgual) {
                $data['fecha_asignacion'] = $logrosRelacionados->first()->fecha_asignacion;
            }
            
            // Si todos los registros tienen las mismas observaciones, usamos esas
            if ($todasObsIgual) {
                $data['observaciones'] = $logrosRelacionados->first()->observaciones;
            }
            
            $this->form->fill($data);
        }
    }
}