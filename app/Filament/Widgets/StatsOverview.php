<?php

namespace App\Filament\Widgets;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Logro;
use App\Models\Materia;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Estudiantes', Estudiante::count())
                ->description('Estudiantes registrados')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            
            Stat::make('Total Materias', Materia::count())
                ->description('Materias activas')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
            
            Stat::make('Total Logros', Logro::count())
                ->description('Logros asignados')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
            
            Stat::make('Total Grados', Grado::count())
                ->description('Grados registrados')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
        ];
    }
} 