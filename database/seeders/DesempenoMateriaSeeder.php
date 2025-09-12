<?php

namespace Database\Seeders;

use App\Models\DesempenoMateria;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Periodo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesempenoMateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $estudiantes = Estudiante::all();
        $materias = Materia::all();
        $periodos = Periodo::all();

        // Limpiar tabla existente usando DELETE en lugar de TRUNCATE
        // debido a foreign key constraints
        DesempenoMateria::query()->delete();

        $desempenos = [];
        $niveles = ['E', 'S', 'A', 'I'];
        $estados = ['borrador', 'publicado', 'revisado'];

        foreach ($estudiantes as $estudiante) {
            foreach ($materias as $materia) {
                // Verificar si el estudiante puede tener esta materia (por grado)
                if ($this->estudiantePuedeTenerMateria($estudiante, $materia)) {
                    foreach ($periodos as $periodo) {
                        // Crear desempeño con probabilidad del 80%
                        if (rand(1, 100) <= 80) {
                            $estado = fake()->randomElement($estados);
                            $bloqueado = $estado === 'publicado' && rand(1, 100) <= 30; // 30% bloqueadas
                            
                            $desempenos[] = [
                                'estudiante_id' => $estudiante->id,
                                'materia_id' => $materia->id,
                                'periodo_id' => $periodo->id,
                                'nivel_desempeno' => fake()->randomElement($niveles),
                                'observaciones_finales' => $this->generarObservaciones(),
                                'fecha_asignacion' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
                                'estado' => $estado,
                                'locked_at' => $bloqueado ? fake()->dateTimeBetween('-30 days', 'now') : null,
                                'locked_by' => $bloqueado ? rand(1, 3) : null, // Usuarios 1-3
                                'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                                'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
                            ];
                        }
                    }
                }
            }
        }

        // Insertar en chunks para mejor rendimiento
        $chunks = array_chunk($desempenos, 500);
        foreach ($chunks as $chunk) {
            DB::table('desempenos_materia')->insert($chunk);
        }

        $this->command->info('✅ Creados ' . count($desempenos) . ' desempeños de materia');
    }

    /**
     * Verificar si un estudiante puede tener una materia según su grado.
     */
    private function estudiantePuedeTenerMateria(Estudiante $estudiante, Materia $materia): bool
    {
        // Obtener grados asociados a la materia
        $gradosMateria = DB::table('grado_materia')
            ->where('materia_id', $materia->id)
            ->pluck('grado_id')
            ->toArray();

        // Si no hay restricciones, la materia está disponible para todos
        if (empty($gradosMateria)) {
            return true;
        }

        // Verificar si el grado del estudiante está en la lista
        return in_array($estudiante->grado_id, $gradosMateria);
    }

    /**
     * Generar observaciones realistas para el desempeño.
     */
    private function generarObservaciones(): ?string
    {
        $observaciones = [
            'El estudiante demuestra un excelente dominio de los conceptos trabajados.',
            'Se evidencia progreso significativo en el desarrollo de las competencias.',
            'Requiere refuerzo en algunos aspectos específicos del área.',
            'Muestra gran interés y participación activa en las actividades.',
            'Es necesario implementar estrategias de apoyo adicional.',
            'Presenta dificultades que requieren acompañamiento personalizado.',
            'Su desempeño es consistente y cumple con los objetivos propuestos.',
            'Se recomienda continuar fortaleciendo las habilidades adquiridas.',
            null, // Algunas sin observaciones
        ];

        return fake()->randomElement($observaciones);
    }
}
