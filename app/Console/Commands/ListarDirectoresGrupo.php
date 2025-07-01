<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Grado;
use Illuminate\Console\Command;

class ListarDirectoresGrupo extends Command
{
    protected $signature = 'director:listar';
    protected $description = 'Lista todos los directores de grupo asignados';

    public function handle()
    {
        $this->info('Directores de Grupo Asignados:');
        $this->info('==============================');

        $directores = User::whereNotNull('director_grado_id')->with('directorGrado')->get();

        if ($directores->isEmpty()) {
            $this->warn('No hay directores de grupo asignados.');
            return 0;
        }

        $headers = ['ID', 'Nombre', 'Email', 'Grado', 'Estudiantes'];
        $rows = [];

        foreach ($directores as $director) {
            $estudiantesCount = $director->estudiantesGrupo()->where('activo', true)->count();
            $rows[] = [
                $director->id,
                $director->name,
                $director->email,
                $director->directorGrado->nombre,
                $estudiantesCount
            ];
        }

        $this->table($headers, $rows);

        // Mostrar grados sin director
        $gradosSinDirector = Grado::where('activo', true)
            ->whereNotIn('id', $directores->pluck('director_grado_id'))
            ->get();

        if ($gradosSinDirector->isNotEmpty()) {
            $this->info('');
            $this->info('Grados sin Director de Grupo:');
            $this->info('============================');
            
            foreach ($gradosSinDirector as $grado) {
                $this->line("- {$grado->nombre}");
            }
        }

        return 0;
    }
} 