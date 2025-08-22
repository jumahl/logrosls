<?php

namespace App\Filament\Resources\NotaResource\Pages;

use App\Filament\Resources\NotaResource;
use App\Models\EstudianteLogro;
use App\Models\ReporteMateria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListNotas extends ListRecords
{
    protected static string $resource = NotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        
        // Esta subquery selecciona un ID representativo por cada combinación 
        // de estudiante, materia y período
        $subQuery = DB::table('estudiante_logros AS el')
            ->select(DB::raw('MIN(el.id) AS id'))
            ->join('logros AS l', 'el.logro_id', '=', 'l.id')
            ->groupBy('el.estudiante_id', 'l.materia_id', 'el.periodo_id');
            
        // Filtrar por materias del profesor si aplica
        if ($user && $user->hasRole('profesor')) {
            $materiaIds = $user->materias()->pluck('id');
            $subQuery->whereIn('l.materia_id', $materiaIds);
        }
            
        // La query principal selecciona solo los registros identificados en la subquery
        $query = EstudianteLogro::whereIn('id', $subQuery);
        
        return $query;
    }
} 