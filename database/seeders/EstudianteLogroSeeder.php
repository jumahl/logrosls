<?php

namespace Database\Seeders;

use App\Models\EstudianteLogro;
use Illuminate\Database\Seeder;

class EstudianteLogroSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener datos necesarios
        $estudiantes = \App\Models\Estudiante::all();
        $logros = \App\Models\Logro::all();
        $periodos = \App\Models\Periodo::all();

        if ($estudiantes->isEmpty() || $logros->isEmpty() || $periodos->isEmpty()) {
            $this->command->warn('No se encontraron estudiantes, logros o períodos. Ejecutar seeders correspondientes primero.');
            return;
        }

        $niveles = ['E', 'S', 'A', 'I']; // E=Excelente, S=Sobresaliente, A=Aceptable, I=Insuficiente
        $observaciones = [
            'E' => [
                'Demuestra un dominio excepcional del tema, superando las expectativas.',
                'Excellent trabajo, muestra creatividad y pensamiento crítico avanzado.',
                'Participación sobresaliente y liderazgo en actividades grupales.',
                'Aplicación correcta y creativa de los conceptos aprendidos.',
            ],
            'S' => [
                'Buen manejo de los conceptos, con algunas demostraciones de excelencia.',
                'Trabajo de calidad que cumple con todos los requisitos establecidos.',
                'Participación activa y aportes significativos en clase.',
                'Comprende claramente el tema y lo aplica correctamente.',
            ],
            'A' => [
                'Comprende los conceptos básicos pero requiere refuerzo en algunos aspectos.',
                'Trabajo satisfactorio que cumple con los requisitos mínimos.',
                'Participación regular, aunque podría ser más activa.',
                'Necesita practicar más para afianzar los conocimientos.',
            ],
            'I' => [
                'Presenta dificultades para comprender los conceptos básicos.',
                'Requiere apoyo adicional y acompañamiento personalizado.',
                'Se recomienda trabajo de refuerzo y práctica adicional.',
                'Necesita dedicar más tiempo y esfuerzo al estudio del tema.',
            ]
        ];

        $evaluaciones = [];

        // Generar evaluaciones para algunos estudiantes de diferentes grados
        foreach ($estudiantes->take(20) as $estudiante) {
            // Obtener logros de las materias que corresponden al grado del estudiante
            $logrosDelGrado = $logros->filter(function($logro) use ($estudiante) {
                return $logro->materia && $logro->materia->grados->contains($estudiante->grado);
            });

            if ($logrosDelGrado->isEmpty()) {
                continue;
            }

            // Evaluar en algunos períodos
            foreach ($periodos->take(2) as $periodo) {
                // Evaluar algunos logros aleatoriamente
                $logrosParaEvaluar = $logrosDelGrado->random(min(5, $logrosDelGrado->count()));
                
                foreach ($logrosParaEvaluar as $logro) {
                    // Simular diferentes niveles de desempeño con distribución realista
                    $probabilidades = [
                        'E' => 15, // 15% Excelente
                        'S' => 35, // 35% Sobresaliente  
                        'A' => 40, // 40% Aceptable
                        'I' => 10  // 10% Insuficiente
                    ];
                    
                    $random = rand(1, 100);
                    $nivelSeleccionado = 'A'; // valor por defecto
                    
                    if ($random <= 15) {
                        $nivelSeleccionado = 'E';
                    } elseif ($random <= 50) {
                        $nivelSeleccionado = 'S';
                    } elseif ($random <= 90) {
                        $nivelSeleccionado = 'A';
                    } else {
                        $nivelSeleccionado = 'I';
                    }

                    $observacion = $observaciones[$nivelSeleccionado][array_rand($observaciones[$nivelSeleccionado])];

                    $evaluaciones[] = [
                        'estudiante_id' => $estudiante->id,
                        'logro_id' => $logro->id,
                        'periodo_id' => $periodo->id,
                        'nivel_desempeno' => $nivelSeleccionado,
                        'observaciones' => $observacion,
                        'fecha_asignacion' => $periodo->fecha_inicio->addDays(rand(1, 30)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insertar todas las evaluaciones de una vez (más eficiente)
        foreach (array_chunk($evaluaciones, 100) as $chunk) {
            \App\Models\EstudianteLogro::insert($chunk);
        }

        $this->command->info('Evaluaciones de logros creadas exitosamente: ' . count($evaluaciones) . ' evaluaciones para ' . min(20, $estudiantes->count()) . ' estudiantes.');
    }
} 