<?php

namespace App\Filament\Widgets;

use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Logro;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user->hasRole('admin')) {
            $stats = [
                Stat::make('Total Estudiantes', Estudiante::where('activo', true)->count())
                    ->description('Estudiantes activos en el sistema')
                    ->descriptionIcon('heroicon-m-users')
                    ->color('success'),
                Stat::make('Total Grados', Grado::where('activo', true)->count())
                    ->description('Grados activos')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),
                Stat::make('Total Materias', Materia::where('activa', true)->count())
                    ->description('Materias activas')
                    ->descriptionIcon('heroicon-m-book-open')
                    ->color('warning'),
                Stat::make('Período Activo', Periodo::where('activo', true)->first()?->periodo_completo ?? 'No hay período activo')
                    ->description('Período académico actual')
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color('primary'),
            ];
        } elseif ($user->hasRole('profesor')) {
            // Si el profesor es director de grupo, mostrar estadísticas de su grupo
            if ($user->isDirectorGrupo()) {
                $estudiantesGrupo = $user->estudiantesGrupo()->where('activo', true)->count();
                $stats = [
                    Stat::make('Mi Grupo', $user->directorGrado->nombre)
                        ->description("Director de grupo de {$estudiantesGrupo} estudiantes")
                        ->descriptionIcon('heroicon-m-academic-cap')
                        ->color('success'),
                    Stat::make('Estudiantes en mi Grupo', $estudiantesGrupo)
                        ->description('Estudiantes activos en mi grupo')
                        ->descriptionIcon('heroicon-m-users')
                        ->color('info'),
                    Stat::make('Mis Materias', $user->materias()->where('activa', true)->count())
                        ->description('Materias que imparto')
                        ->descriptionIcon('heroicon-m-book-open')
                        ->color('warning'),
                    Stat::make('Período Activo', Periodo::where('activo', true)->first()?->periodo_completo ?? 'No hay período activo')
                        ->description('Período académico actual')
                        ->descriptionIcon('heroicon-m-calendar')
                        ->color('primary'),
                ];
            } else {
                // Profesor normal (no director de grupo)
                $gradoIds = $user->materias()->with('grados')->get()->pluck('grados')->flatten()->pluck('id')->unique();
                $estudiantesMaterias = Estudiante::whereIn('grado_id', $gradoIds)->where('activo', true)->count();
                
                $stats = [
                    Stat::make('Mis Materias', $user->materias()->where('activa', true)->count())
                        ->description('Materias que imparto')
                        ->descriptionIcon('heroicon-m-book-open')
                        ->color('success'),
                    Stat::make('Estudiantes', $estudiantesMaterias)
                        ->description('Estudiantes en mis materias')
                        ->descriptionIcon('heroicon-m-users')
                        ->color('info'),
                    Stat::make('Grados', $gradoIds->count())
                        ->description('Grados donde imparto clases')
                        ->descriptionIcon('heroicon-m-academic-cap')
                        ->color('warning'),
                    Stat::make('Período Activo', Periodo::where('activo', true)->first()?->periodo_completo ?? 'No hay período activo')
                        ->description('Período académico actual')
                        ->descriptionIcon('heroicon-m-calendar')
                        ->color('primary'),
                ];
            }
        }

        return $stats;
    }
} 