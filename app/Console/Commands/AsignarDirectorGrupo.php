<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Grado;
use Illuminate\Console\Command;

class AsignarDirectorGrupo extends Command
{
    protected $signature = 'director:asignar {user_id} {grado_id}';
    protected $description = 'Asigna un usuario como director de grupo de un grado específico';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $gradoId = $this->argument('grado_id');

        $user = User::find($userId);
        if (!$user) {
            $this->error('Usuario no encontrado.');
            return 1;
        }

        $grado = Grado::find($gradoId);
        if (!$grado) {
            $this->error('Grado no encontrado.');
            return 1;
        }

        // Verificar si el grado ya tiene un director
        $directorExistente = User::where('director_grado_id', $gradoId)->first();
        if ($directorExistente && $directorExistente->id !== $userId) {
            $this->error("El grado {$grado->nombre} ya tiene un director asignado: {$directorExistente->name}");
            return 1;
        }

        // Asignar director de grupo
        $user->director_grado_id = $gradoId;
        $user->save();

        $this->info("Usuario {$user->name} asignado como director de grupo del grado {$grado->nombre}.");
        return 0;
    }
} 