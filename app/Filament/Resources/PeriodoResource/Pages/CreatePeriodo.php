<?php

namespace App\Filament\Resources\PeriodoResource\Pages;

use App\Filament\Resources\PeriodoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePeriodo extends CreateRecord
{
    protected static string $resource = PeriodoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Crear año escolar automáticamente si no existe
        $anioEscolar = \App\Models\AnioEscolar::where('anio', $data['anio_escolar'])->first();
        
        if (!$anioEscolar) {
            // Verificar si ya existe un año escolar activo
            $hayAnioActivo = \App\Models\AnioEscolar::where('activo', true)->exists();
            
            // Calcular fechas del año escolar
            $fechaInicio = \Carbon\Carbon::createFromDate($data['anio_escolar'], 2, 1); // 1 de febrero
            $fechaFin = \Carbon\Carbon::createFromDate($data['anio_escolar'], 11, 30); // 30 de noviembre
            
            // Si no hay año activo, crear este como activo
            $activo = !$hayAnioActivo;
            
            \App\Models\AnioEscolar::create([
                'anio' => $data['anio_escolar'],
                'activo' => $activo,
                'finalizado' => false,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'observaciones' => "Año escolar creado automáticamente al crear período" . 
                                 ($activo ? " (establecido como activo)" : "")
            ]);
            
            $mensaje = "Se creó automáticamente el año escolar {$data['anio_escolar']}";
            if ($activo) {
                $mensaje .= " y se estableció como año activo";
            }
            
            Notification::make()
                ->title('Año escolar creado')
                ->body($mensaje)
                ->success()
                ->send();
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Período creado')
            ->body('El período ha sido creado exitosamente.');
    }
}
