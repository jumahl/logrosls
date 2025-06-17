<?php

namespace App\Filament\Widgets;

use App\Models\Periodo;
use Filament\Widgets\Widget;

class CurrentPeriod extends Widget
{
    protected static string $view = 'filament.widgets.current-period';
    
    protected int|string|array $columnSpan = 'full';

    public function getCurrentPeriod(): ?Periodo
    {
        return Periodo::where('activo', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->first();
    }
} 