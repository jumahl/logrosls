<?php

namespace App\Filament\Resources\NotaResource\Pages;

use App\Filament\Resources\NotaResource;
use App\Models\DesempenoMateria;
use App\Models\EstudianteLogro;
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
                ->visible(fn() => !$this->record->locked_at && auth()->user()->can('delete', $this->record)),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Verificar si la calificación está bloqueada
        if ($record->locked_at) {
            Notification::make()
                ->warning()
                ->title('Calificación bloqueada')
                ->body('No se puede editar una calificación que está bloqueada.')
                ->send();
            
            return $record;
        }

        // Extraer los logros del array de datos
        $logrosIds = $data['logros'] ?? [];
        unset($data['logros']);
        
        // Eliminar grado_id del array ya que no es campo de DesempenoMateria
        unset($data['grado_id']);

        // Actualizar el desempeño de materia
        $record->update($data);

        // Obtener los logros actuales asociados a este desempeño
        $logrosActuales = $record->estudianteLogros()->pluck('logro_id')->toArray();

        // Eliminar logros que ya no están seleccionados
        $logrosAEliminar = array_diff($logrosActuales, $logrosIds);
        if (!empty($logrosAEliminar)) {
            EstudianteLogro::where('desempeno_materia_id', $record->id)
                ->whereIn('logro_id', $logrosAEliminar)
                ->delete();
        }

        // Agregar nuevos logros
        $logrosNuevos = array_diff($logrosIds, $logrosActuales);
        foreach ($logrosNuevos as $logroId) {
            // Verificar que el logro pertenece a la materia
            $logro = \App\Models\Logro::where('id', $logroId)
                ->where('materia_id', $record->materia_id)
                ->where('activo', true)
                ->first();

            if ($logro) {
                EstudianteLogro::create([
                    'logro_id' => $logroId,
                    'desempeno_materia_id' => $record->id,
                    'alcanzado' => true,
                ]);
            }
        }

        // Los logros existentes ya están vinculados al desempeño
        // No necesitamos actualizar fecha_asignacion porque se maneja en DesempenoMateria

        $mensaje = 'Calificación actualizada correctamente.';
        if (!empty($logrosAEliminar)) {
            $mensaje .= ' Se eliminaron ' . count($logrosAEliminar) . ' logro(s).';
        }
        if (!empty($logrosNuevos)) {
            $mensaje .= ' Se agregaron ' . count($logrosNuevos) . ' logro(s).';
        }

        Notification::make()
            ->success()
            ->title('Actualización completada')
            ->body($mensaje)
            ->send();

        return $record;
    }
    
    protected function fillForm(): void
    {
        parent::fillForm();
        
        if ($this->record) {
            // Cargar los logros asociados al desempeño
            $logrosIds = $this->record->estudianteLogros()->pluck('logro_id')->toArray();
            
            $data = $this->record->toArray();
            $data['logros'] = $logrosIds;
            $data['grado_id'] = $this->record->estudiante->grado_id;
            
            $this->form->fill($data);
        }
    }
}