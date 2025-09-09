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

        // Obtener grados (incluyendo todos los grupos A y B)
        $gradoPreescolar = Grado::where('nombre', 'Preescolar')->get();
        $gradoTransicion = Grado::where('nombre', 'Transición')->get();
        $gradoPrimero = Grado::where('nombre', 'Primero')->get();
        $gradoSegundo = Grado::where('nombre', 'Segundo')->get();
        $gradoTercero = Grado::where('nombre', 'Tercero')->get();
        $gradoCuarto = Grado::where('nombre', 'Cuarto')->get();
        $gradoQuinto = Grado::where('nombre', 'Quinto')->get();
        $gradoSexto = Grado::where('nombre', 'Sexto')->get();
        $gradoSeptimo = Grado::where('nombre', 'Séptimo')->get();
        $gradoOctavo = Grado::where('nombre', 'Octavo')->get();
        $gradoNoveno = Grado::where('nombre', 'Noveno')->get();
        $gradoDecimo = Grado::where('nombre', 'Décimo')->get();
        $gradoOnce = Grado::where('nombre', 'Once')->get();

        // Crear materias por área
        $materias = [
            // Matemáticas
            [
                'nombre' => 'Matemáticas',
                'codigo' => 'MAT001',
                'descripcion' => 'Desarrollo del pensamiento lógico-matemático',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'area' => 'matematicas',
                'grados' => $gradoPrimero->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)
            ],
            [
                'nombre' => 'Álgebra',
                'codigo' => 'MAT002',
                'descripcion' => 'Fundamentos algebraicos y ecuaciones',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'area' => 'matematicas',
                'grados' => $gradoSexto->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)
            ],
            [
                'nombre' => 'Cálculo',
                'codigo' => 'MAT003',
                'descripcion' => 'Introducción al cálculo diferencial e integral',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'area' => 'matematicas',
                'grados' => $gradoDecimo->merge($gradoOnce)
            ],
            
            // Lenguaje - Humanidades
            [
                'nombre' => 'Lenguaje',
                'codigo' => 'LEN001',
                'descripcion' => 'Desarrollo de competencias comunicativas',
                'docente_id' => $profesores->skip(1)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'humanidades',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)
            ],
            [
                'nombre' => 'Lengua Castellana',
                'codigo' => 'LEN002',
                'descripcion' => 'Literatura y gramática avanzada',
                'docente_id' => $profesores->skip(1)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'humanidades',
                'grados' => $gradoCuarto->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Ciencias Naturales
            [
                'nombre' => 'Ciencias Naturales',
                'codigo' => 'CIE001',
                'descripcion' => 'Exploración del mundo natural',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'ciencias_naturales_y_educacion_ambiental',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)
            ],
            [
                'nombre' => 'Biología',
                'codigo' => 'CIE002',
                'descripcion' => 'Estudio de los seres vivos',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'ciencias_naturales_y_educacion_ambiental',
                'grados' => $gradoSexto->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            [
                'nombre' => 'Química',
                'codigo' => 'CIE003',
                'descripcion' => 'Fundamentos de la química',
                'docente_id' => $profesores->skip(2)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'ciencias_naturales_y_educacion_ambiental',
                'grados' => $gradoDecimo->merge($gradoOnce)
            ],
            [
                'nombre' => 'Física',
                'codigo' => 'CIE004',
                'descripcion' => 'Principios físicos fundamentales',
                'docente_id' => $profesores->first()->id,
                'activa' => true,
                'area' => 'ciencias_naturales_y_educacion_ambiental',
                'grados' => $gradoDecimo->merge($gradoOnce)
            ],
            
            // Ciencias Sociales
            [
                'nombre' => 'Ciencias Sociales',
                'codigo' => 'SOC001',
                'descripcion' => 'Historia, geografía y cívica',
                'docente_id' => $profesores->skip(3)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'ciencias_sociales',
                'grados' => $gradoPrimero->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Inglés - Humanidades
            [
                'nombre' => 'Inglés',
                'codigo' => 'ING001',
                'descripcion' => 'Idioma extranjero inglés',
                'docente_id' => $profesores->skip(6)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'humanidades',
                'grados' => $gradoTercero->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Educación Física
            [
                'nombre' => 'Educación Física',
                'codigo' => 'EDF001',
                'descripcion' => 'Desarrollo motor y deportivo',
                'docente_id' => $profesores->skip(5)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'educacion_fisica_recreacion_y_deporte',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Educación Artística
            [
                'nombre' => 'Educación Artística',
                'codigo' => 'ART001',
                'descripcion' => 'Desarrollo de la creatividad y expresión artística',
                'docente_id' => $profesores->skip(4)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'educacion_artistica',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)
            ],
            
            // Tecnología e Informática
            [
                'nombre' => 'Tecnología e Informática',
                'codigo' => 'TEC001',
                'descripcion' => 'Competencias tecnológicas y digitales',
                'docente_id' => $profesores->skip(7)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'tecnologia_e_informatica',
                'grados' => $gradoCuarto->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Ética y Valores
            [
                'nombre' => 'Ética y Valores',
                'codigo' => 'ETI001',
                'descripcion' => 'Formación en valores y convivencia',
                'docente_id' => $profesores->skip(8)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'educacion_etica_y_valores_humanos',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
            ],
            
            // Religión
            [
                'nombre' => 'Educación Religiosa',
                'codigo' => 'REL001',
                'descripcion' => 'Formación espiritual y religiosa',
                'docente_id' => $profesores->skip(8)->first()?->id ?? $profesores->first()->id,
                'activa' => true,
                'area' => 'educacion_religiosa',
                'grados' => $gradoPreescolar->merge($gradoTransicion)->merge($gradoPrimero)->merge($gradoSegundo)->merge($gradoTercero)->merge($gradoCuarto)->merge($gradoQuinto)->merge($gradoSexto)->merge($gradoSeptimo)->merge($gradoOctavo)->merge($gradoNoveno)->merge($gradoDecimo)->merge($gradoOnce)
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
            $gradosIds = $grados->pluck('id')->toArray();
            
            if (!empty($gradosIds)) {
                $materia->grados()->syncWithoutDetaching($gradosIds);
            }
        }

        $this->command->info('Materias creadas exitosamente: ' . count($materias) . ' materias con sus respectivos grados asignados.');
    }
} 