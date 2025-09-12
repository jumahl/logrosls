<?php

namespace App\Filament\Resources\NotaResource\Pages;

use App\Filament\Resources\NotaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\DesempenoMateria;
use App\Models\EstudianteLogro;
use Filament\Notifications\Notification;

class CreateNota extends CreateRecord
{
    protected static string $resource = NotaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Calificación creada')
            ->body('La calificación ha sido creada exitosamente.');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Extraer los logros del array de datos
        $logrosIds = $data['logros'] ?? [];
        unset($data['logros']);
        
        // Eliminar grado_id del array ya que no es campo de DesempenoMateria
        unset($data['grado_id']);

        // Verificar si ya existe una calificación para esta combinación
        $existeDesempeno = DesempenoMateria::where('estudiante_id', $data['estudiante_id'])
            ->where('materia_id', $data['materia_id'])
            ->where('periodo_id', $data['periodo_id'])
            ->first();

        if ($existeDesempeno) {
            Notification::make()
                ->warning()
                ->title('Calificación existente')
                ->body('Ya existe una calificación para este estudiante en esta materia y período.')
                ->send();
            
            return $existeDesempeno;
        }

        // Crear el desempeño de materia
        $desempeno = DesempenoMateria::create($data);

        // Crear los registros de logros asociados
        $logrosCreados = 0;
        foreach ($logrosIds as $logroId) {
            // Verificar que el logro pertenece a la materia
            $logro = \App\Models\Logro::where('id', $logroId)
                ->where('materia_id', $data['materia_id'])
                ->where('activo', true)
                ->first();

            if ($logro) {
                EstudianteLogro::create([
                    'logro_id' => $logroId,
                    'desempeno_materia_id' => $desempeno->id,
                    'alcanzado' => true, // Por defecto los logros seleccionados están alcanzados
                ]);
                $logrosCreados++;
            }
        }

        return $desempeno;
    }
} 