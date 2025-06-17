<?php

namespace App\Console\Commands;

use App\Models\Estudiante;
use App\Models\Grado;
use Illuminate\Console\Command;

class ActualizarGradosEstudiantes extends Command
{
    protected $signature = 'estudiantes:actualizar-grados';
    protected $description = 'Actualiza automáticamente los grados de los estudiantes al inicio del año escolar';

    public function handle()
    {
        $this->info('Iniciando actualización de grados...');

        // Obtener todos los estudiantes activos
        $estudiantes = Estudiante::where('activo', true)->get();

        foreach ($estudiantes as $estudiante) {
            $gradoActual = $estudiante->grado;
            
            // Encontrar el siguiente grado
            $siguienteGrado = Grado::where('tipo', $gradoActual->tipo)
                ->where('orden', '>', $gradoActual->orden)
                ->orderBy('orden')
                ->first();

            if ($siguienteGrado) {
                $estudiante->grado_id = $siguienteGrado->id;
                $estudiante->save();
                
                $this->info("Estudiante {$estudiante->nombre} actualizado al grado {$siguienteGrado->nombre}");
            } else {
                $this->warn("No se encontró siguiente grado para {$estudiante->nombre} en {$gradoActual->nombre}");
            }
        }

        $this->info('Actualización de grados completada.');
    }
} 