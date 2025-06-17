<?php

namespace App\Filament\Widgets;

use App\Models\Materia;
use Filament\Widgets\ChartWidget;

class LogrosByMateria extends ChartWidget
{
    protected static ?string $heading = 'Logros por Materia';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $materias = Materia::withCount('logros')->get();
        
        return [
            'datasets' => [
                [
                    'label' => 'Logros por Materia',
                    'data' => $materias->pluck('logros_count')->toArray(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $materias->pluck('nombre')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
} 