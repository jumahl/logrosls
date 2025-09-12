<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Grado;

class DirectorGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener profesores disponibles
        $profesores = User::whereHas('roles', function($query) {
            $query->where('name', 'profesor');
        })->get();

        // Obtener grados disponibles
        $grados = Grado::all();

        if ($profesores->isEmpty() || $grados->isEmpty()) {
            $this->command->warn('No se encontraron profesores o grados. Ejecutar ShieldPermissionSeeder y GradoSeeder primero.');
            return;
        }

        // Asignar directores de grupo para los grados principales
        $asignaciones = [
            'Transición' => 'María Elena Rodríguez',
            'Primero' => 'Carlos Alberto Pérez',
            'Segundo' => 'Ana Sofía Martínez',
            'Tercero' => 'Luis Fernando González',
            'Cuarto' => 'Patricia Isabel López',
            'Quinto' => 'Roberto David Silva',
            'Sexto' => 'Carmen Rosa Jiménez',
            'Séptimo' => 'Miguel Ángel Torres',
            'Octavo' => 'Laura Beatriz Morales',
            'Noveno' => 'Jorge Andrés Vargas',
        ];

        foreach ($asignaciones as $nombreGrado => $nombreProfesor) {
            $grado = $grados->where('nombre', $nombreGrado)->first();
            $profesor = $profesores->where('name', $nombreProfesor)->first();

            if ($grado && $profesor) {
                // Asignar el profesor como director del grado
                $profesor->update(['director_grado_id' => $grado->id]);
                
                $this->command->info("Asignado: {$nombreProfesor} como director de {$nombreGrado}");
            }
        }

        $this->command->info('Directores de grupo asignados exitosamente.');
    }
}
