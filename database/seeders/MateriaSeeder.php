<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\User;
use App\Models\Grado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos profesores creados previamente
        $profesores = User::whereHas('roles', function($query) {
            $query->where('name', 'profesor');
        })->get();

        if ($profesores->isEmpty()) {
            $this->command->warn('No se encontraron profesores. Ejecutar ShieldPermissionSeeder primero.');
            return;
        }

        // Obtener grados
        $gradoPreescolar = Grado::where('nombre', 'Preescolar')->first();
        $gradoTransicion = Grado::where('nombre', 'Transición')->first();
        $gradoPrimero = Grado::where('nombre', 'Primero')->first();
        $gradoSegundo = Grado::where('nombre', 'Segundo')->first();
        $gradoTercero = Grado::where('nombre', 'Tercero')->first();
        $gradoCuarto = Grado::where('nombre', 'Cuarto')->first();
        $gradoQuinto = Grado::where('nombre', 'Quinto')->first();
        $gradoSexto = Grado::where('nombre', 'Sexto')->first();
        $gradoSeptimo = Grado::where('nombre', 'Séptimo')->first();
        $gradoOctavo = Grado::where('nombre', 'Octavo')->first();
        $gradoNoveno = Grado::where('nombre', 'Noveno')->first();
        $gradoDecimo = Grado::where('nombre', 'Décimo')->first();
        $gradoOnce = Grado::where('nombre', 'Once')->first();

        // Crear materias por área
        $materias = [
            // Matemáticas
            [
                'nombre' => 'Matemáticas',
                'codigo' => 'MAT001',
                'descripcion' => 'Desarrollo del pensamiento lógico-matemático',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto]
            ],
            [
                'nombre' => 'Álgebra',
                'codigo' => 'MAT002',
                'descripcion' => 'Fundamentos algebraicos y ecuaciones',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno]
            ],
            [
                'nombre' => 'Cálculo',
                'codigo' => 'MAT003',
                'descripcion' => 'Introducción al cálculo diferencial e integral',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoDecimo, $gradoOnce]
            ],
            
            // Lenguaje
            [
                'nombre' => 'Lenguaje',
                'codigo' => 'LEN001',
                'descripcion' => 'Desarrollo de competencias comunicativas',
                'docente_id' => $profesores->skip(1)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero]
            ],
            [
                'nombre' => 'Lengua Castellana',
                'codigo' => 'LEN002',
                'descripcion' => 'Literatura y gramática avanzada',
                'docente_id' => $profesores->skip(1)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Ciencias Naturales
            [
                'nombre' => 'Ciencias Naturales',
                'codigo' => 'CIE001',
                'descripcion' => 'Exploración del mundo natural',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto]
            ],
            [
                'nombre' => 'Biología',
                'codigo' => 'CIE002',
                'descripcion' => 'Estudio de los seres vivos',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            [
                'nombre' => 'Química',
                'codigo' => 'CIE003',
                'descripcion' => 'Fundamentos de la química',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoDecimo, $gradoOnce]
            ],
            [
                'nombre' => 'Física',
                'codigo' => 'CIE004',
                'descripcion' => 'Principios físicos fundamentales',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoDecimo, $gradoOnce]
            ],
            
            // Ciencias Sociales
            [
                'nombre' => 'Ciencias Sociales',
                'codigo' => 'SOC001',
                'descripcion' => 'Historia, geografía y cívica',
                'docente_id' => $profesores->skip(3)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Inglés
            [
                'nombre' => 'Inglés',
                'codigo' => 'ING001',
                'descripcion' => 'Idioma extranjero inglés',
                'docente_id' => $profesores->skip(6)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Educación Física
            [
                'nombre' => 'Educación Física',
                'codigo' => 'EDF001',
                'descripcion' => 'Desarrollo motor y deportivo',
                'docente_id' => $profesores->skip(5)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Educación Artística
            [
                'nombre' => 'Educación Artística',
                'codigo' => 'ART001',
                'descripcion' => 'Desarrollo de la creatividad y expresión artística',
                'docente_id' => $profesores->skip(4)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno]
            ],
            
            // Tecnología e Informática
            [
                'nombre' => 'Tecnología e Informática',
                'codigo' => 'TEC001',
                'descripcion' => 'Competencias tecnológicas y digitales',
                'docente_id' => $profesores->skip(7)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Ética y Valores
            [
                'nombre' => 'Ética y Valores',
                'codigo' => 'ETI001',
                'descripcion' => 'Formación en valores y convivencia',
                'docente_id' => $profesores->skip(8)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ],
            
            // Religión
            [
                'nombre' => 'Educación Religiosa',
                'codigo' => 'REL001',
                'descripcion' => 'Formación espiritual y religiosa',
                'docente_id' => $profesores->skip(8)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'grados' => [$gradoPreescolar, $gradoTransicion, $gradoPrimero, $gradoSegundo, $gradoTercero, $gradoCuarto, $gradoQuinto, $gradoSexto, $gradoSeptimo, $gradoOctavo, $gradoNoveno, $gradoDecimo, $gradoOnce]
            ]
        ];

        foreach ($materias as $materiaData) {
            $grados = $materiaData['grados'];
            unset($materiaData['grados']);
            
            $materia = Materia::firstOrCreate(
                ['codigo' => $materiaData['codigo']],
                $materiaData
            );
            
            // Asignar los grados a la materia usando la relación muchos a muchos
            $gradosIds = array_filter(array_map(function($grado) {
                return $grado ? $grado->id : null;
            }, $grados));
            
            if (!empty($gradosIds)) {
                $materia->grados()->syncWithoutDetaching($gradosIds);
            }
        }

        $this->command->info('Materias creadas exitosamente: ' . count($materias) . ' materias con sus respectivos grados asignados.');
    }
} 