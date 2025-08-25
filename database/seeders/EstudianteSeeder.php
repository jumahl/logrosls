<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use Illuminate\Database\Seeder;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los grados disponibles
        $grados = \App\Models\Grado::all()->keyBy('nombre');
        
        if ($grados->isEmpty()) {
            $this->command->warn('No se encontraron grados. Ejecutar GradoSeeder primero.');
            return;
        }

        // Generar estudiantes para cada grado
        $estudiantes = [];
        
        // Preescolar (5-6 años)
        if ($grados->has('Preescolar')) {
            $estudiantesPreescolar = [
                ['nombre' => 'Sofía', 'apellido' => 'García', 'documento' => '1100000001', 'fecha_nacimiento' => '2019-03-15'],
                ['nombre' => 'Mateo', 'apellido' => 'López', 'documento' => '1100000002', 'fecha_nacimiento' => '2019-05-22'],
                ['nombre' => 'Isabella', 'apellido' => 'Martínez', 'documento' => '1100000003', 'fecha_nacimiento' => '2019-01-10'],
                ['nombre' => 'Santiago', 'apellido' => 'Rodríguez', 'documento' => '1100000004', 'fecha_nacimiento' => '2019-07-08'],
                ['nombre' => 'Valentina', 'apellido' => 'Hernández', 'documento' => '1100000005', 'fecha_nacimiento' => '2019-02-18'],
            ];
            foreach ($estudiantesPreescolar as $est) {
                $est['grado_id'] = $grados['Preescolar']->id;
                $estudiantes[] = $est;
            }
        }

        // Transición (6-7 años)
        if ($grados->has('Transición')) {
            $estudiantesTransicion = [
                ['nombre' => 'Carlos', 'apellido' => 'González', 'documento' => '1100000006', 'fecha_nacimiento' => '2018-05-15'],
                ['nombre' => 'Laura', 'apellido' => 'Martínez', 'documento' => '1100000007', 'fecha_nacimiento' => '2018-03-20'],
                ['nombre' => 'Juan', 'apellido' => 'Rodríguez', 'documento' => '1100000008', 'fecha_nacimiento' => '2018-07-10'],
                ['nombre' => 'Ana', 'apellido' => 'Silva', 'documento' => '1100000009', 'fecha_nacimiento' => '2018-09-25'],
                ['nombre' => 'Diego', 'apellido' => 'Torres', 'documento' => '1100000010', 'fecha_nacimiento' => '2018-11-12'],
                ['nombre' => 'Camila', 'apellido' => 'Vargas', 'documento' => '1100000011', 'fecha_nacimiento' => '2018-04-30'],
            ];
            foreach ($estudiantesTransicion as $est) {
                $est['grado_id'] = $grados['Transición']->id;
                $estudiantes[] = $est;
            }
        }

        // Primero (7-8 años)
        if ($grados->has('Primero')) {
            $estudiantesPrimero = [
                ['nombre' => 'Alejandro', 'apellido' => 'Morales', 'documento' => '1100000012', 'fecha_nacimiento' => '2017-06-14'],
                ['nombre' => 'María', 'apellido' => 'Castro', 'documento' => '1100000013', 'fecha_nacimiento' => '2017-08-19'],
                ['nombre' => 'Sebastián', 'apellido' => 'Jiménez', 'documento' => '1100000014', 'fecha_nacimiento' => '2017-12-03'],
                ['nombre' => 'Lucía', 'apellido' => 'Ramírez', 'documento' => '1100000015', 'fecha_nacimiento' => '2017-10-27'],
                ['nombre' => 'David', 'apellido' => 'Peña', 'documento' => '1100000016', 'fecha_nacimiento' => '2017-05-16'],
                ['nombre' => 'Paula', 'apellido' => 'Osorio', 'documento' => '1100000017', 'fecha_nacimiento' => '2017-09-21'],
                ['nombre' => 'Andrés', 'apellido' => 'Gutiérrez', 'documento' => '1100000018', 'fecha_nacimiento' => '2017-03-08'],
            ];
            foreach ($estudiantesPrimero as $est) {
                $est['grado_id'] = $grados['Primero']->id;
                $estudiantes[] = $est;
            }
        }

        // Segundo (8-9 años)
        if ($grados->has('Segundo')) {
            $estudiantesSegundo = [
                ['nombre' => 'Nicole', 'apellido' => 'Mendoza', 'documento' => '1100000019', 'fecha_nacimiento' => '2016-04-12'],
                ['nombre' => 'Julián', 'apellido' => 'Vega', 'documento' => '1100000020', 'fecha_nacimiento' => '2016-07-25'],
                ['nombre' => 'Gabriela', 'apellido' => 'Ruiz', 'documento' => '1100000021', 'fecha_nacimiento' => '2016-11-17'],
                ['nombre' => 'Kevin', 'apellido' => 'Paredes', 'documento' => '1100000022', 'fecha_nacimiento' => '2016-02-09'],
                ['nombre' => 'Daniela', 'apellido' => 'Aguilar', 'documento' => '1100000023', 'fecha_nacimiento' => '2016-08-13'],
                ['nombre' => 'Fernando', 'apellido' => 'Delgado', 'documento' => '1100000024', 'fecha_nacimiento' => '2016-12-01'],
            ];
            foreach ($estudiantesSegundo as $est) {
                $est['grado_id'] = $grados['Segundo']->id;
                $estudiantes[] = $est;
            }
        }

        // Tercero (9-10 años)
        if ($grados->has('Tercero')) {
            $estudiantesTercero = [
                ['nombre' => 'Ximena', 'apellido' => 'Cortés', 'documento' => '1100000025', 'fecha_nacimiento' => '2015-05-20'],
                ['nombre' => 'Ricardo', 'apellido' => 'Navarro', 'documento' => '1100000026', 'fecha_nacimiento' => '2015-09-14'],
                ['nombre' => 'Valeria', 'apellido' => 'Campos', 'documento' => '1100000027', 'fecha_nacimiento' => '2015-01-28'],
                ['nombre' => 'Emilio', 'apellido' => 'Sandoval', 'documento' => '1100000028', 'fecha_nacimiento' => '2015-06-11'],
                ['nombre' => 'Carolina', 'apellido' => 'Mejía', 'documento' => '1100000029', 'fecha_nacimiento' => '2015-10-07'],
                ['nombre' => 'Esteban', 'apellido' => 'Ramos', 'documento' => '1100000030', 'fecha_nacimiento' => '2015-03-23'],
            ];
            foreach ($estudiantesTercero as $est) {
                $est['grado_id'] = $grados['Tercero']->id;
                $estudiantes[] = $est;
            }
        }

        // Cuarto (10-11 años)
        if ($grados->has('Cuarto')) {
            $estudiantesCuarto = [
                ['nombre' => 'Isabella', 'apellido' => 'Molina', 'documento' => '1100000031', 'fecha_nacimiento' => '2014-04-16'],
                ['nombre' => 'Joaquín', 'apellido' => 'Serrano', 'documento' => '1100000032', 'fecha_nacimiento' => '2014-08-29'],
                ['nombre' => 'Mariana', 'apellido' => 'León', 'documento' => '1100000033', 'fecha_nacimiento' => '2014-12-05'],
                ['nombre' => 'Bruno', 'apellido' => 'Guerrero', 'documento' => '1100000034', 'fecha_nacimiento' => '2014-02-21'],
                ['nombre' => 'Fernanda', 'apellido' => 'Parra', 'documento' => '1100000035', 'fecha_nacimiento' => '2014-07-18'],
            ];
            foreach ($estudiantesCuarto as $est) {
                $est['grado_id'] = $grados['Cuarto']->id;
                $estudiantes[] = $est;
            }
        }

        // Quinto (11-12 años)
        if ($grados->has('Quinto')) {
            $estudiantesQuinto = [
                ['nombre' => 'Renata', 'apellido' => 'Vargas', 'documento' => '1100000036', 'fecha_nacimiento' => '2013-05-03'],
                ['nombre' => 'Matías', 'apellido' => 'Herrera', 'documento' => '1100000037', 'fecha_nacimiento' => '2013-09-26'],
                ['nombre' => 'Antonella', 'apellido' => 'Restrepo', 'documento' => '1100000038', 'fecha_nacimiento' => '2013-01-15'],
                ['nombre' => 'Samuel', 'apellido' => 'Álvarez', 'documento' => '1100000039', 'fecha_nacimiento' => '2013-06-08'],
                ['nombre' => 'Salomé', 'apellido' => 'Cruz', 'documento' => '1100000040', 'fecha_nacimiento' => '2013-11-24'],
            ];
            foreach ($estudiantesQuinto as $est) {
                $est['grado_id'] = $grados['Quinto']->id;
                $estudiantes[] = $est;
            }
        }

        // Sexto (12-13 años)
        if ($grados->has('Sexto')) {
            $estudiantesSexto = [
                ['nombre' => 'Emilia', 'apellido' => 'Rojas', 'documento' => '1100000041', 'fecha_nacimiento' => '2012-03-11'],
                ['nombre' => 'Nicolás', 'apellido' => 'Cardona', 'documento' => '1100000042', 'fecha_nacimiento' => '2012-07-19'],
                ['nombre' => 'Julieta', 'apellido' => 'Medina', 'documento' => '1100000043', 'fecha_nacimiento' => '2012-11-02'],
                ['nombre' => 'Tomás', 'apellido' => 'Arias', 'documento' => '1100000044', 'fecha_nacimiento' => '2012-04-27'],
            ];
            foreach ($estudiantesSexto as $est) {
                $est['grado_id'] = $grados['Sexto']->id;
                $estudiantes[] = $est;
            }
        }

        // Séptimo (13-14 años)
        if ($grados->has('Séptimo')) {
            $estudiantesSeptimo = [
                ['nombre' => 'Victoria', 'apellido' => 'Sánchez', 'documento' => '1100000045', 'fecha_nacimiento' => '2011-05-14'],
                ['nombre' => 'Felipe', 'apellido' => 'Gómez', 'documento' => '1100000046', 'fecha_nacimiento' => '2011-09-07'],
                ['nombre' => 'Constanza', 'apellido' => 'Muñoz', 'documento' => '1100000047', 'fecha_nacimiento' => '2011-01-22'],
                ['nombre' => 'Maximiliano', 'apellido' => 'Ortega', 'documento' => '1100000048', 'fecha_nacimiento' => '2011-06-30'],
            ];
            foreach ($estudiantesSeptimo as $est) {
                $est['grado_id'] = $grados['Séptimo']->id;
                $estudiantes[] = $est;
            }
        }

        // Octavo (14-15 años)
        if ($grados->has('Octavo')) {
            $estudiantesOctavo = [
                ['nombre' => 'Agustina', 'apellido' => 'Cano', 'documento' => '1100000049', 'fecha_nacimiento' => '2010-02-18'],
                ['nombre' => 'Benjamín', 'apellido' => 'Duarte', 'documento' => '1100000050', 'fecha_nacimiento' => '2010-08-12'],
                ['nombre' => 'Manuela', 'apellido' => 'Espinoza', 'documento' => '1100000051', 'fecha_nacimiento' => '2010-12-25'],
                ['nombre' => 'Lorenzo', 'apellido' => 'Miranda', 'documento' => '1100000052', 'fecha_nacimiento' => '2010-04-09'],
            ];
            foreach ($estudiantesOctavo as $est) {
                $est['grado_id'] = $grados['Octavo']->id;
                $estudiantes[] = $est;
            }
        }

        // Noveno (15-16 años)
        if ($grados->has('Noveno')) {
            $estudiantesNoveno = [
                ['nombre' => 'Esperanza', 'apellido' => 'Quintero', 'documento' => '1100000053', 'fecha_nacimiento' => '2009-03-06'],
                ['nombre' => 'Ignacio', 'apellido' => 'Bermúdez', 'documento' => '1100000054', 'fecha_nacimiento' => '2009-07-21'],
                ['nombre' => 'Amparo', 'apellido' => 'Figueroa', 'documento' => '1100000055', 'fecha_nacimiento' => '2009-11-14'],
                ['nombre' => 'Rodrigo', 'apellido' => 'Galindo', 'documento' => '1100000056', 'fecha_nacimiento' => '2009-05-28'],
            ];
            foreach ($estudiantesNoveno as $est) {
                $est['grado_id'] = $grados['Noveno']->id;
                $estudiantes[] = $est;
            }
        }

        // Décimo (16-17 años)
        if ($grados->has('Décimo')) {
            $estudiantesDecimo = [
                ['nombre' => 'Esperanza', 'apellido' => 'Villareal', 'documento' => '1100000057', 'fecha_nacimiento' => '2008-04-13'],
                ['nombre' => 'Gonzalo', 'apellido' => 'Pacheco', 'documento' => '1100000058', 'fecha_nacimiento' => '2008-08-26'],
                ['nombre' => 'Clemencia', 'apellido' => 'Bautista', 'documento' => '1100000059', 'fecha_nacimiento' => '2008-12-17'],
            ];
            foreach ($estudiantesDecimo as $est) {
                $est['grado_id'] = $grados['Décimo']->id;
                $estudiantes[] = $est;
            }
        }

        // Once (17-18 años)
        if ($grados->has('Once')) {
            $estudiantesOnce = [
                ['nombre' => 'Esperanza', 'apellido' => 'Calderón', 'documento' => '1100000060', 'fecha_nacimiento' => '2007-05-01'],
                ['nombre' => 'Patricio', 'apellido' => 'Vásquez', 'documento' => '1100000061', 'fecha_nacimiento' => '2007-09-15'],
                ['nombre' => 'Mercedes', 'apellido' => 'Castillo', 'documento' => '1100000062', 'fecha_nacimiento' => '2007-01-29'],
            ];
            foreach ($estudiantesOnce as $est) {
                $est['grado_id'] = $grados['Once']->id;
                $estudiantes[] = $est;
            }
        }

        // Agregar datos comunes a todos los estudiantes
        foreach ($estudiantes as &$estudiante) {
            $estudiante['direccion'] = 'Calle ' . rand(1, 100) . ' #' . rand(10, 99) . '-' . rand(10, 99);
            $estudiante['telefono'] = '300' . rand(1000000, 9999999);
            $estudiante['email'] = strtolower($estudiante['nombre'] . '.' . $estudiante['apellido'] . '@estudiante.liceo.edu.co');
            $estudiante['activo'] = true;
        }

        // Crear todos los estudiantes
        foreach ($estudiantes as $estudianteData) {
            \App\Models\Estudiante::firstOrCreate(
                ['documento' => $estudianteData['documento']], 
                $estudianteData
            );
        }

        $this->command->info('Estudiantes creados exitosamente: ' . count($estudiantes) . ' estudiantes distribuidos en todos los grados.');
    }
} 