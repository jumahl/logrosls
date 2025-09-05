<?php

namespace App\Filament\Pages;

use App\Models\AnioEscolar;
use App\Models\Estudiante;
use App\Models\Materia;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TransicionAnual extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationLabel = 'Transición de Año';
    
    protected static ?string $title = 'Transición de Año Escolar';
    
    protected static string $view = 'filament.pages.transicion-anual';
    
    protected static ?string $navigationGroup = 'Administración';
    
    // Solo admins pueden ver esta página
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        $anioActual = AnioEscolar::where('activo', true)->first();
        
        return $form
            ->schema([
                Select::make('anio_origen')
                    ->label('Año Escolar Actual')
                    ->options(function () {
                        $anioActivo = AnioEscolar::where('activo', true)->first();
                        return $anioActivo ? [$anioActivo->anio => $anioActivo->anio] : [];
                    })
                    ->default(function () {
                        $anioActivo = AnioEscolar::where('activo', true)->first();
                        return $anioActivo?->anio;
                    })
                    ->disabled()
                    ->required()
                    ->helperText($anioActual ? "Año actualmente en curso" : "⚠️ No hay año escolar activo configurado"),
                    
                Select::make('anio_destino')
                    ->label('Año Escolar Destino')
                    ->options(AnioEscolar::disponiblesParaTransicion()->pluck('anio', 'anio'))
                    ->required()
                    ->helperText('Selecciona el año al que se promoverán los estudiantes')
                    ->searchable(),
                    
                Textarea::make('observaciones')
                    ->label('Observaciones de la Transición')
                    ->placeholder('Notas adicionales sobre el proceso de transición...')
                    ->rows(3)
            ])
            ->statePath('data');
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('simular')
                ->label('Simular Transición')
                ->icon('heroicon-o-eye')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Simular Transición de Año Escolar')
                ->modalDescription('Esta simulación te mostrará qué pasaría con cada estudiante sin hacer cambios reales.')
                ->action(function () {
                    $this->simularTransicion();
                }),
                
            Action::make('ejecutar')
                ->label('Ejecutar Transición')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('¿Ejecutar Transición de Año Escolar?')
                ->modalDescription('Esta acción promoverá a todos los estudiantes al siguiente año. NO se puede deshacer.')
                ->action(function () {
                    $this->ejecutarTransicion();
                })
        ];
    }
    
    public function simularTransicion()
    {
        try {
            $data = $this->form->getState();
            
            if (!$data['anio_destino']) {
                Notification::make()
                    ->title('Error')
                    ->body('Debes seleccionar un año destino')
                    ->danger()
                    ->send();
                return;
            }
            
            // Obtener año actual
            $anioActual = AnioEscolar::where('activo', true)->first();
            if (!$anioActual) {
                Notification::make()
                    ->title('Error')
                    ->body('No hay un año escolar activo configurado')
                    ->danger()
                    ->send();
                return;
            }
            
            // Ejecutar simulación usando el comando
            $exitCode = \Illuminate\Support\Facades\Artisan::call('transicion:anual', [
                'anio_finalizar' => $anioActual->anio,
                'anio_nuevo' => $data['anio_destino'],
                '--simular' => true,
                '--force' => true  // Agregar --force para evitar problemas con STDIN
            ]);
            
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if ($exitCode === 0) {
                Notification::make()
                    ->title('Simulación completada')
                    ->body('La simulación se ejecutó correctamente. Revisa los logs para más detalles.')
                    ->success()
                    ->send();
                    
                // Guardar output para mostrar en la vista
                session()->flash('simulacion_output', $output);
            } else {
                Notification::make()
                    ->title('Error en simulación')
                    ->body('Hubo un error durante la simulación.')
                    ->danger()
                    ->send();
            }
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error durante la simulación: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function ejecutarTransicion()
    {
        try {
            $data = $this->form->getState();
            
            if (!$data['anio_destino']) {
                Notification::make()
                    ->title('Error')
                    ->body('Debes seleccionar un año destino')
                    ->danger()
                    ->send();
                return;
            }
            
            // Obtener año actual
            $anioActual = AnioEscolar::where('activo', true)->first();
            if (!$anioActual) {
                Notification::make()
                    ->title('Error')
                    ->body('No hay un año escolar activo configurado')
                    ->danger()
                    ->send();
                return;
            }
            
            // Ejecutar transición real
            $exitCode = \Illuminate\Support\Facades\Artisan::call('transicion:anual', [
                'anio_finalizar' => $anioActual->anio,
                'anio_nuevo' => $data['anio_destino'],
                '--force' => true  // Agregar --force para evitar problemas con STDIN en web
            ]);
            
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if ($exitCode === 0) {
                Notification::make()
                    ->title('✅ Transición Completada')
                    ->body('La transición de año escolar se ejecutó exitosamente')
                    ->success()
                    ->persistent()
                    ->send();
                    
                // Redirigir para refrescar la página
                $this->redirect(static::getUrl());
            } else {
                Notification::make()
                    ->title('Error en transición')
                    ->body('Hubo un error durante la transición.')
                    ->danger()
                    ->send();
            }
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error durante la transición: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function getEstadisticas()
    {
        try {
            $anioActivo = AnioEscolar::where('activo', true)->first();
            
            return [
                'estudiantes_activos' => Estudiante::where('activo', true)->count() ?? 0,
                'grados_con_estudiantes' => Estudiante::where('activo', true)->distinct('grado_id')->count('grado_id') ?? 0,
                'total_materias' => Materia::where('activa', true)->count() ?? 0,
                'anio_actual' => $anioActivo?->anio ?? 'No Configurado'
            ];
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en getEstadisticas TransicionAnual: ' . $e->getMessage());
            
            // Retornar valores por defecto en caso de error
            return [
                'estudiantes_activos' => 0,
                'grados_con_estudiantes' => 0,
                'total_materias' => 0,
                'anio_actual' => 'Error'
            ];
        }
    }
}
