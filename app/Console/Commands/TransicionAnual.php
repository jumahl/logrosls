<?php

namespace App\Console\Commands;

use App\Models\AnioEscolar;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\HistoricoEstudiante;
use App\Models\HistoricoDesempeno;
use App\Models\HistoricoLogro;
use App\Models\DesempenoMateria;
use App\Models\EstudianteLogro;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class TransicionAnual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transicion:anual 
                            {anio_finalizar : Año escolar que se va a finalizar}
                            {anio_nuevo : Nuevo año escolar que iniciará}
                            {--simular : Solo simular sin hacer cambios reales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta la transición de año escolar, archivando datos y promoviendo estudiantes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $anioFinalizar = $this->argument('anio_finalizar');
        $anioNuevo = $this->argument('anio_nuevo');
        $simular = $this->option('simular');

        $this->info("🎓 Iniciando transición de año escolar");
        $this->info("📅 Año a finalizar: {$anioFinalizar}");
        $this->info("📅 Nuevo año: {$anioNuevo}");
        
        if ($simular) {
            $this->warn("🧪 MODO SIMULACIÓN - No se harán cambios reales");
        }

        try {
            // Validaciones iniciales
            $this->validarParametros($anioFinalizar, $anioNuevo);

            // Obtener estadísticas antes de la transición
            $estadisticas = $this->obtenerEstadisticas();
            $this->mostrarEstadisticas($estadisticas);

            if (!$simular) {
                $this->line('');
                if (!$this->confirm('¿Está seguro de proceder con la transición? Esta acción no se puede deshacer.')) {
                    $this->info('❌ Transición cancelada por el usuario.');
                    return Command::FAILURE;
                }
            }

            DB::transaction(function () use ($anioFinalizar, $anioNuevo, $simular) {
                // Paso 1: Crear/verificar años escolares
                $this->paso1_verificarAniosEscolares($anioFinalizar, $anioNuevo, $simular);

                // Paso 2: Archivar datos históricos
                $this->paso2_archivarDatos($anioFinalizar, $simular);

                // Paso 3: Promover estudiantes
                $this->paso3_promoverEstudiantes($anioNuevo, $simular);

                // Paso 4: Finalizar año anterior
                $this->paso4_finalizarAnio($anioFinalizar, $simular);

                if ($simular) {
                    // Rollback en simulación
                    throw new Exception('SIMULACIÓN_COMPLETADA');
                }
            });

            if (!$simular) {
                $this->info('');
                $this->info('✅ Transición completada exitosamente!');
                $this->info('📋 Revise los datos y ajuste manualmente los estudiantes que no deban continuar.');
            }

            return Command::SUCCESS;

        } catch (Exception $e) {
            if ($e->getMessage() === 'SIMULACIÓN_COMPLETADA') {
                $this->info('');
                $this->info('✅ Simulación completada. No se realizaron cambios reales.');
                return Command::SUCCESS;
            }

            $this->error('❌ Error durante la transición: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function validarParametros($anioFinalizar, $anioNuevo)
    {
        if ($anioNuevo <= $anioFinalizar) {
            throw new Exception('El año nuevo debe ser mayor al año a finalizar.');
        }

        if ($anioNuevo - $anioFinalizar !== 1) {
            throw new Exception('El año nuevo debe ser consecutivo al año que se finaliza.');
        }
    }

    private function obtenerEstadisticas()
    {
        return [
            'estudiantes_activos' => Estudiante::where('activo', true)->count(),
            'total_grados' => Grado::count(),
            'desempenos_registrados' => DesempenoMateria::count(),
            'logros_registrados' => EstudianteLogro::count(),
        ];
    }

    private function mostrarEstadisticas($stats)
    {
        $this->info('');
        $this->info('📊 Estadísticas actuales:');
        $this->line("   👥 Estudiantes activos: {$stats['estudiantes_activos']}");
        $this->line("   🎓 Grados: {$stats['total_grados']}");
        $this->line("   📝 Desempeños registrados: {$stats['desempenos_registrados']}");
        $this->line("   🏆 Logros registrados: {$stats['logros_registrados']}");
    }

    private function paso1_verificarAniosEscolares($anioFinalizar, $anioNuevo, $simular)
    {
        $this->info('');
        $this->info('📋 Paso 1: Verificando años escolares...');

        // Verificar año a finalizar
        $anioActual = AnioEscolar::where('anio', $anioFinalizar)->first();
        if (!$anioActual) {
            if (!$simular) {
                AnioEscolar::create([
                    'anio' => $anioFinalizar,
                    'activo' => true,
                    'finalizado' => false,
                    'fecha_inicio' => now()->startOfYear(),
                    'fecha_fin' => now()->endOfYear(),
                ]);
            }
            $this->line("   ✅ Año {$anioFinalizar} creado");
        } else {
            $this->line("   ✅ Año {$anioFinalizar} encontrado");
        }

        // Crear año nuevo
        $anioExistente = AnioEscolar::where('anio', $anioNuevo)->first();
        if (!$anioExistente) {
            if (!$simular) {
                AnioEscolar::create([
                    'anio' => $anioNuevo,
                    'activo' => false,
                    'finalizado' => false,
                    'fecha_inicio' => now()->addYear()->startOfYear(),
                    'fecha_fin' => now()->addYear()->endOfYear(),
                ]);
            }
            $this->line("   ✅ Año {$anioNuevo} creado");
        } else {
            $this->line("   ✅ Año {$anioNuevo} ya existe");
        }
    }

    private function paso2_archivarDatos($anioFinalizar, $simular)
    {
        $this->info('');
        $this->info('📦 Paso 2: Archivando datos históricos...');

        $estudiantesArchivados = 0;
        $desempenosArchivados = 0;
        $logrosArchivados = 0;

        // Archivar estudiantes y sus grados
        $estudiantes = Estudiante::where('activo', true)->with('grado')->get();
        
        foreach ($estudiantes as $estudiante) {
            if (!$simular) {
                HistoricoEstudiante::create([
                    'anio_escolar' => $anioFinalizar,
                    'estudiante_id' => $estudiante->id,
                    'estudiante_nombre' => $estudiante->nombres,
                    'estudiante_apellido' => $estudiante->apellidos,
                    'estudiante_documento' => $estudiante->documento,
                    'grado_id' => $estudiante->grado_id,
                    'grado_nombre' => $estudiante->grado->nombre,
                    'grado_grupo' => $estudiante->grado->grupo ?? '',
                    'resultado_final' => 'promovido', // Por defecto todos son promovidos
                ]);
            }
            $estudiantesArchivados++;
        }

        // Archivar desempeños
        $desempenos = DesempenoMateria::with(['estudiante', 'materia', 'periodo'])->get();
        
        foreach ($desempenos as $desempeno) {
            if (!$simular) {
                HistoricoDesempeno::create([
                    'anio_escolar' => $anioFinalizar,
                    'estudiante_id' => $desempeno->estudiante_id,
                    'materia_id' => $desempeno->materia_id,
                    'periodo_id' => $desempeno->periodo_id,
                    'estudiante_nombre' => $desempeno->estudiante->nombres,
                    'estudiante_apellido' => $desempeno->estudiante->apellidos,
                    'estudiante_documento' => $desempeno->estudiante->documento,
                    'materia_nombre' => $desempeno->materia->nombre,
                    'materia_codigo' => $desempeno->materia->codigo ?? '',
                    'periodo_nombre' => $desempeno->periodo->nombre,
                    'periodo_corte' => $desempeno->periodo->corte ?? 1,
                    'periodo_numero' => $desempeno->periodo->numero ?? 1,
                    'nivel_desempeno' => $desempeno->desempeno,
                    'observaciones_finales' => $desempeno->observaciones,
                    'docente_nombre' => $desempeno->materia->usuario->name ?? '',
                    'director_grupo' => '',
                ]);
            }
            $desempenosArchivados++;
        }

        // Archivar logros
        $logros = EstudianteLogro::with(['desempenoMateria.estudiante', 'logro'])->get();
        
        foreach ($logros as $logro) {
            if (!$simular) {
                HistoricoLogro::create([
                    'anio_escolar' => $anioFinalizar,
                    'estudiante_id' => $logro->desempenoMateria->estudiante_id,
                    'logro_id' => $logro->logro_id,
                    'alcanzado' => $logro->alcanzado,
                    'observaciones' => $logro->desempenoMateria->observaciones ?? '',
                    'estudiante_nombres' => $logro->desempenoMateria->estudiante->nombres,
                    'estudiante_apellidos' => $logro->desempenoMateria->estudiante->apellidos,
                    'logro_descripcion' => $logro->logro->descripcion,
                ]);
            }
            $logrosArchivados++;
        }

        $this->line("   📁 {$estudiantesArchivados} estudiantes archivados");
        $this->line("   📝 {$desempenosArchivados} desempeños archivados");
        $this->line("   🏆 {$logrosArchivados} logros archivados");
    }

    private function paso3_promoverEstudiantes($anioNuevo, $simular)
    {
        $this->info('');
        $this->info('🎓 Paso 3: Promoviendo estudiantes...');

        $promociones = [
            'preescolar' => 'primero',
            'primero' => 'segundo',
            'segundo' => 'tercero',
            'tercero' => 'cuarto',
            'cuarto' => 'quinto',
            'quinto' => 'sexto',
            'sexto' => 'septimo',
            'septimo' => 'octavo',
            'octavo' => 'noveno',
            'noveno' => 'decimo',
            'decimo' => 'once',
            'once' => 'media_academica',
            'media_academica' => null, // Se gradúan
        ];

        $promovidos = 0;
        $graduados = 0;

        foreach ($promociones as $gradoActual => $gradoSiguiente) {
            $grado = Grado::where('nombre', $gradoActual)->first();
            if (!$grado) continue;

            $estudiantes = Estudiante::where('grado_id', $grado->id)
                                   ->where('activo', true)
                                   ->get();

            foreach ($estudiantes as $estudiante) {
                if ($gradoSiguiente) {
                    // Promover al siguiente grado
                    $gradoDestino = Grado::where('nombre', $gradoSiguiente)->first();
                    if ($gradoDestino) {
                        if (!$simular) {
                            $estudiante->update(['grado_id' => $gradoDestino->id]);
                        }
                        $promovidos++;
                    }
                } else {
                    // Marcar como graduado (inactivo)
                    if (!$simular) {
                        $estudiante->update(['activo' => false]);
                    }
                    $graduados++;
                }
            }
        }

        $this->line("   🎯 {$promovidos} estudiantes promovidos");
        $this->line("   🎓 {$graduados} estudiantes graduados");
    }

    private function paso4_finalizarAnio($anioFinalizar, $simular)
    {
        $this->info('');
        $this->info('🔒 Paso 4: Finalizando año escolar...');

        if (!$simular) {
            // Finalizar año anterior
            AnioEscolar::where('anio', $anioFinalizar)
                      ->update([
                          'activo' => false,
                          'finalizado' => true
                      ]);

            // Activar año nuevo
            AnioEscolar::where('activo', true)->update(['activo' => false]);
            AnioEscolar::where('anio', $anioFinalizar + 1)
                      ->update(['activo' => true]);

            // Limpiar datos del año anterior
            DesempenoMateria::truncate();
            EstudianteLogro::truncate();
        }

        $this->line("   ✅ Año {$anioFinalizar} finalizado");
        $this->line("   ✅ Datos del año anterior limpiados");
        $this->line("   ✅ Año " . ($anioFinalizar + 1) . " activado");
    }
}
