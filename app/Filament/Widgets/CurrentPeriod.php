<?php

namespace App\Filament\Widgets;

use App\Models\Periodo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentPeriod extends BaseWidget
{
    protected function getStats(): array
    {
        $periodoActivo = Periodo::where('activo', true)->first();
        
        if (!$periodoActivo) {
            return [
                Stat::make('Período Activo', 'No hay período activo')
                    ->description('Configure un período activo')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle'),
            ];
        }

        $diasRestantes = now()->diffInDays($periodoActivo->fecha_fin, false);
        $diasRestantes = round($diasRestantes);
        $porcentajeCompletado = $this->calcularPorcentajeCompletado($periodoActivo);
        $periodoCompleto = $periodoActivo->nombre . ' - ' . $periodoActivo->corte . ' ' . $periodoActivo->año_escolar;

        return [
            Stat::make('Período Activo', $periodoCompleto)
                ->description($periodoActivo->corte)
                ->color($this->getColorByCorte($periodoActivo->corte))
                ->icon($this->getIconByCorte($periodoActivo->corte)),

            Stat::make('Progreso', $porcentajeCompletado . '%')
                ->description('Completado del período')
                ->color($this->getColorByProgreso($porcentajeCompletado))
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Días Restantes', $diasRestantes > 0 ? $diasRestantes : 'Finalizado')
                ->description($diasRestantes > 0 ? 'Días para finalizar' : 'Período terminado')
                ->color($diasRestantes > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-calendar'),
        ];
    }

    private function calcularPorcentajeCompletado(Periodo $periodo): int
    {
        $totalDias = $periodo->fecha_inicio->diffInDays($periodo->fecha_fin);
        $diasTranscurridos = $periodo->fecha_inicio->diffInDays(now());
        
        if ($totalDias === 0) return 100;
        
        $porcentaje = ($diasTranscurridos / $totalDias) * 100;
        
        return min(100, max(0, round($porcentaje)));
    }

    private function getColorByCorte(string $corte): string
    {
        return match($corte) {
            'Primer Corte' => 'info',
            'Segundo Corte' => 'success',
            default => 'gray'
        };
    }

    private function getIconByCorte(string $corte): string
    {
        return match($corte) {
            'Primer Corte' => 'heroicon-o-document-text',
            'Segundo Corte' => 'heroicon-o-academic-cap',
            default => 'heroicon-o-calendar'
        };
    }

    private function getColorByProgreso(int $porcentaje): string
    {
        return match(true) {
            $porcentaje >= 80 => 'success',
            $porcentaje >= 60 => 'warning',
            $porcentaje >= 40 => 'info',
            default => 'danger'
        };
    }
} 