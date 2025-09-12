<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

class LogroSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener materias disponibles
        $materias = \App\Models\Materia::all()->keyBy('codigo');
        
        if ($materias->isEmpty()) {
            $this->command->warn('No se encontraron materias. Ejecutar MateriaSeeder primero.');
            return;
        }

        $logros = [];
        $orden = 1;

        // Logros de Matemáticas (Preescolar y Primaria)
        if ($materias->has('MAT001')) {
            $logrosMat = [
                ['titulo' => 'Conteo básico', 'desempeno' => 'Identifica y cuenta números del 1 al 10 en diferentes contextos y situaciones cotidianas'],
                ['titulo' => 'Formas geométricas', 'desempeno' => 'Reconoce y nombra figuras geométricas básicas como círculo, cuadrado, triángulo y rectángulo'],
                ['titulo' => 'Operaciones básicas', 'desempeno' => 'Resuelve problemas simples de suma y resta con números del 1 al 10'],
                ['titulo' => 'Patrones y secuencias', 'desempeno' => 'Identifica y continúa patrones simples con objetos, colores y formas'],
                ['titulo' => 'Medidas de tiempo', 'desempeno' => 'Comprende conceptos básicos de tiempo como ayer, hoy, mañana, antes y después'],
                ['titulo' => 'Clasificación', 'desempeno' => 'Clasifica objetos por tamaño, color, forma y otras características observables'],
            ];
            foreach ($logrosMat as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'MAT001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['MAT001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Álgebra (Secundaria)
        if ($materias->has('MAT002')) {
            $logrosAlg = [
                ['titulo' => 'Números enteros', 'desempeno' => 'Opera con números enteros aplicando las propiedades de las operaciones básicas'],
                ['titulo' => 'Ecuaciones lineales', 'desempeno' => 'Resuelve ecuaciones lineales de primer grado con una incógnita'],
                ['titulo' => 'Sistema de ecuaciones', 'desempeno' => 'Resuelve sistemas de ecuaciones lineales 2x2 por diferentes métodos'],
                ['titulo' => 'Funciones lineales', 'desempeno' => 'Interpreta y construye gráficas de funciones lineales y afines'],
            ];
            foreach ($logrosAlg as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'MAT002-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['MAT002']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Lenguaje (Preescolar y Primaria)
        if ($materias->has('LEN001')) {
            $logrosLen = [
                ['titulo' => 'Reconocimiento de letras', 'desempeno' => 'Identifica y diferencia todas las vocales y las consonantes más comunes'],
                ['titulo' => 'Escritura inicial', 'desempeno' => 'Escribe su nombre completo y palabras sencillas de manera legible'],
                ['titulo' => 'Comprensión oral', 'desempeno' => 'Sigue instrucciones orales de dos o tres pasos en secuencia'],
                ['titulo' => 'Expresión oral', 'desempeno' => 'Se expresa oralmente con claridad y coherencia sobre temas familiares'],
                ['titulo' => 'Lectura inicial', 'desempeno' => 'Lee palabras y oraciones cortas con comprensión del significado'],
                ['titulo' => 'Producción textual', 'desempeno' => 'Produce textos cortos y sencillos con propósito comunicativo claro'],
            ];
            foreach ($logrosLen as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'LEN001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['LEN001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Lengua Castellana (Primaria Superior y Secundaria)
        if ($materias->has('LEN002')) {
            $logrosLenCast = [
                ['titulo' => 'Comprensión lectora', 'desempeno' => 'Comprende textos narrativos, informativos y descriptivos identificando ideas principales'],
                ['titulo' => 'Análisis literario', 'desempeno' => 'Analiza elementos básicos de textos literarios como personajes, ambiente y trama'],
                ['titulo' => 'Producción escrita', 'desempeno' => 'Produce textos coherentes y cohesivos aplicando reglas ortográficas y gramaticales'],
                ['titulo' => 'Expresión oral formal', 'desempeno' => 'Participa en debates y exposiciones demostrando habilidades comunicativas'],
            ];
            foreach ($logrosLenCast as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'LEN002-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['LEN002']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Ciencias Naturales (Preescolar y Primaria)
        if ($materias->has('CIE001')) {
            $logrosCN = [
                ['titulo' => 'Partes del cuerpo', 'desempeno' => 'Reconoce y nombra las partes principales del cuerpo humano y su función básica'],
                ['titulo' => 'Los sentidos', 'desempeno' => 'Identifica los cinco sentidos y explica su importancia para explorar el entorno'],
                ['titulo' => 'Seres vivos', 'desempeno' => 'Diferencia seres vivos de objetos inertes y clasifica animales por su hábitat'],
                ['titulo' => 'Estados de la materia', 'desempeno' => 'Distingue los estados sólido, líquido y gaseoso en materiales cotidianos'],
                ['titulo' => 'Ciclos naturales', 'desempeno' => 'Describe ciclos naturales simples como el día y la noche, las estaciones'],
            ];
            foreach ($logrosCN as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'CIE001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['CIE001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Biología (Secundaria)
        if ($materias->has('CIE002')) {
            $logrosBio = [
                ['titulo' => 'Célula', 'desempeno' => 'Comprende la célula como unidad básica de la vida y sus componentes principales'],
                ['titulo' => 'Ecosistemas', 'desempeno' => 'Analiza las relaciones entre los seres vivos y su ambiente en diferentes ecosistemas'],
                ['titulo' => 'Biodiversidad', 'desempeno' => 'Valora la importancia de la biodiversidad y propone estrategias para su conservación'],
                ['titulo' => 'Genética básica', 'desempeno' => 'Comprende los principios básicos de la herencia y la transmisión de características'],
            ];
            foreach ($logrosBio as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'CIE002-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['CIE002']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Ciencias Sociales
        if ($materias->has('SOC001')) {
            $logrosSoc = [
                ['titulo' => 'Identidad personal', 'desempeno' => 'Reconoce su identidad personal, familiar y cultural dentro de su comunidad'],
                ['titulo' => 'Orientación espacial', 'desempeno' => 'Se orienta en el espacio y representa lugares mediante mapas y croquis sencillos'],
                ['titulo' => 'Historia familiar', 'desempeno' => 'Reconstruye la historia familiar y personal mediante diferentes fuentes'],
                ['titulo' => 'Normas de convivencia', 'desempeno' => 'Comprende la importancia de las normas para la convivencia en sociedad'],
                ['titulo' => 'Diversidad cultural', 'desempeno' => 'Reconoce y respeta la diversidad cultural de su región y país'],
            ];
            foreach ($logrosSoc as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'SOC001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['SOC001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Inglés
        if ($materias->has('ING001')) {
            $logrosIng = [
                ['titulo' => 'Vocabulario básico', 'desempeno' => 'Utiliza vocabulario básico relacionado con temas familiares y cotidianos'],
                ['titulo' => 'Comprensión auditiva', 'desempeno' => 'Comprende instrucciones y diálogos sencillos en inglés sobre situaciones conocidas'],
                ['titulo' => 'Expresión oral', 'desempeno' => 'Se expresa oralmente en inglés usando frases y oraciones simples'],
                ['titulo' => 'Lectura básica', 'desempeno' => 'Lee y comprende textos cortos y sencillos en inglés sobre temas familiares'],
            ];
            foreach ($logrosIng as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'ING001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['ING001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Educación Física
        if ($materias->has('EDF001')) {
            $logrosEF = [
                ['titulo' => 'Coordinación motriz', 'desempeno' => 'Demuestra coordinación en movimientos básicos como correr, saltar y lanzar'],
                ['titulo' => 'Juegos cooperativos', 'desempeno' => 'Participa en juegos y actividades físicas respetando reglas y compañeros'],
                ['titulo' => 'Hábitos saludables', 'desempeno' => 'Reconoce la importancia del ejercicio físico para mantener una vida saludable'],
                ['titulo' => 'Expresión corporal', 'desempeno' => 'Utiliza el cuerpo como medio de expresión y comunicación de ideas y emociones'],
            ];
            foreach ($logrosEF as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'EDF001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['EDF001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Educación Artística
        if ($materias->has('ART001')) {
            $logrosArt = [
                ['titulo' => 'Expresión plástica', 'desempeno' => 'Utiliza diferentes técnicas y materiales para crear expresiones artísticas'],
                ['titulo' => 'Apreciación estética', 'desempeno' => 'Aprecia y valora diferentes manifestaciones artísticas de su entorno'],
                ['titulo' => 'Creatividad', 'desempeno' => 'Desarrolla su creatividad a través de proyectos artísticos individuales y grupales'],
                ['titulo' => 'Patrimonio cultural', 'desempeno' => 'Reconoce y valora las expresiones artísticas de su región y país'],
            ];
            foreach ($logrosArt as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'ART001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['ART001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Tecnología e Informática
        if ($materias->has('TEC001')) {
            $logrosTec = [
                ['titulo' => 'Herramientas básicas', 'desempeno' => 'Identifica y utiliza herramientas tecnológicas básicas de su entorno'],
                ['titulo' => 'Computación básica', 'desempeno' => 'Maneja conceptos básicos de computación y navegación en internet'],
                ['titulo' => 'Seguridad digital', 'desempeno' => 'Aplica normas de seguridad y uso responsable de las tecnologías'],
                ['titulo' => 'Proyectos tecnológicos', 'desempeno' => 'Desarrolla proyectos simples aplicando el proceso tecnológico'],
            ];
            foreach ($logrosTec as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'TEC001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['TEC001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Logros de Ética y Valores
        if ($materias->has('ETI001')) {
            $logrosEti = [
                ['titulo' => 'Valores fundamentales', 'desempeno' => 'Identifica y practica valores como respeto, honestidad y responsabilidad'],
                ['titulo' => 'Resolución de conflictos', 'desempeno' => 'Aplica estrategias pacíficas para resolver conflictos interpersonales'],
                ['titulo' => 'Participación democrática', 'desempeno' => 'Participa activamente en la toma de decisiones grupales de manera democrática'],
                ['titulo' => 'Proyecto de vida', 'desempeno' => 'Reflexiona sobre sus metas personales y académicas a corto y largo plazo'],
            ];
            foreach ($logrosEti as $index => $logro) {
                $logros[] = array_merge($logro, [
                    'codigo' => 'ETI001-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'materia_id' => $materias['ETI001']->id,
                    'orden' => $orden++,
                    'activo' => true
                ]);
            }
        }

        // Crear todos los logros
        foreach ($logros as $logro) {
            \App\Models\Logro::firstOrCreate(
                ['codigo' => $logro['codigo']], 
                $logro
            );
        }

        $this->command->info('Logros creados exitosamente: ' . count($logros) . ' logros distribuidos por todas las materias.');
    }
} 