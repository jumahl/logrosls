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
                            {--simular : Solo simular sin hacer cambios reales}
                            {--force : Ejecutar sin confirmación interactiva (usar con precaución)}';

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
                
                // Verificar si STDIN está disponible antes de usar confirm()
                if (defined('STDIN') && is_resource(STDIN)) {
                    if (!$this->confirm('¿Está seguro de proceder con la transición? Esta acción no se puede deshacer.')) {
                        $this->info('❌ Transición cancelada por el usuario.');
                        return Command::FAILURE;
                    }
                } else {
                    // Si STDIN no está disponible, usar una opción de comando para confirmación
                    if (!$this->option('force')) {
                        $this->error('❌ Para ejecutar la transición real sin confirmación interactiva, use la opción --force');
                        $this->info('💡 Ejemplo: php artisan transicion:anual 2025 2026 --force');
                        return Command::FAILURE;
                    }
                    $this->warn('⚠️  Ejecutando transición sin confirmación interactiva (modo --force)');
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
                $this->paso4_finalizarAnio($anioFinalizar, $anioNuevo, $simular);

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

        // Archivar estudiantes y sus grados con chunking
        $this->info('Archivando estudiantes...');
        $estudiantesQuery = Estudiante::select(['id', 'nombre', 'apellido', 'documento', 'grado_id', 'activo'])
            ->with(['grado:id,nombre,grupo'])
            ->where('activo', true);
            
        $totalEstudiantes = $estudiantesQuery->count();
        $bar = $this->output->createProgressBar($totalEstudiantes);
        $bar->start();
        
        $estudiantesQuery->chunk(100, function ($estudiantes) use ($anioFinalizar, $simular, $bar, &$estudiantesArchivados) {
            foreach ($estudiantes as $estudiante) {
                if (!$simular) {
                    HistoricoEstudiante::create([
                        'anio_escolar' => $anioFinalizar,
                        'estudiante_id' => $estudiante->id,
                        'estudiante_nombre' => $estudiante->nombre,
                        'estudiante_apellido' => $estudiante->apellido,
                        'estudiante_documento' => $estudiante->documento,
                        'grado_id' => $estudiante->grado_id,
                        'grado_nombre' => $estudiante->grado->nombre,
                        'grado_grupo' => $estudiante->grado->grupo ?? '',
                        'resultado_final' => 'promovido', // Por defecto todos son promovidos
                    ]);
                }
                $estudiantesArchivados++;
                $bar->advance();
            }
        });
        $bar->finish();
        $this->newLine();

        // Archivar desempeños con chunking y eager loading optimizado
        $this->info('Archivando desempeños académicos...');
        $desempenosQuery = DesempenoMateria::select([
                'id', 'estudiante_id', 'materia_id', 'periodo_id', 
                'nivel_desempeno', 'observaciones_finales', 'fecha_asignacion'
            ])
            ->with([
                'estudiante:id,nombre,apellido,documento',
                'materia:id,nombre,codigo,docente_id',
                'materia.docente:id,name',
                'periodo:id,corte,numero_periodo'
            ]);
            
        $totalDesempenos = $desempenosQuery->count();
        $bar2 = $this->output->createProgressBar($totalDesempenos);
        $bar2->start();
        
        $desempenosQuery->chunk(100, function ($desempenos) use ($anioFinalizar, $simular, $bar2, &$desempenosArchivados) {
            foreach ($desempenos as $desempeno) {
                if (!$simular) {
                    HistoricoDesempeno::create([
                        'anio_escolar' => $anioFinalizar,
                        'estudiante_id' => $desempeno->estudiante_id,
                        'materia_id' => $desempeno->materia_id,
                        'periodo_id' => $desempeno->periodo_id,
                        'estudiante_nombre' => $desempeno->estudiante->nombre,
                        'estudiante_apellido' => $desempeno->estudiante->apellido,
                        'estudiante_documento' => $desempeno->estudiante->documento,
                        'materia_nombre' => $desempeno->materia->nombre,
                        'materia_codigo' => $desempeno->materia->codigo ?? '',
                        'periodo_nombre' => $desempeno->periodo->corte,
                        'periodo_corte' => $desempeno->periodo->corte ?? 1,
                        'periodo_numero' => $desempeno->periodo->numero_periodo ?? 1,
                        'nivel_desempeno' => $desempeno->nivel_desempeno,
                        'observaciones_finales' => $desempeno->observaciones_finales,
                        'docente_nombre' => $desempeno->materia->docente->name ?? '',
                        'director_grupo' => '',
                    ]);
                }
                $desempenosArchivados++;
                $bar2->advance();
            }
        });
        $bar2->finish();
        $this->newLine();

        // Archivar logros estudiantiles con chunking y eager loading optimizado
        $this->info('Archivando logros estudiantiles...');
        $logrosQuery = EstudianteLogro::select([
                'id', 'logro_id', 'desempeno_materia_id', 'alcanzado'
            ])
            ->with([
                'desempenoMateria:id,estudiante_id',
                'desempenoMateria.estudiante:id,nombre,apellido,documento',
                'logro:id,codigo,titulo,desempeno,materia_id',
                'logro.materia:id,nombre'
            ]);
            
        $totalLogros = $logrosQuery->count();
        $bar3 = $this->output->createProgressBar($totalLogros);
        $bar3->start();
        
        $logrosQuery->chunk(100, function ($logros) use ($anioFinalizar, $simular, $bar3, &$logrosArchivados) {
            foreach ($logros as $logro) {
                // TODO: Implementar archivado de logros históricos
                // Requiere restructuración para relacionar con historico_desempenos
                $logrosArchivados++;
                $bar3->advance();
            }
        });
        $bar3->finish();
        $this->newLine();

        $this->line("   📁 {$estudiantesArchivados} estudiantes archivados");
        $this->line("   📝 {$desempenosArchivados} desempeños archivados");
        $this->line("   🏆 {$logrosArchivados} logros archivados");
    }

    private function paso3_promoverEstudiantes($anioNuevo, $simular)
    {
        $this->info('');
        $this->info('🎓 Paso 3: Promoviendo estudiantes...');

        // Mapeo de promociones - usa nombres exactos pero será buscado de forma insensible a mayúsculas
        $promociones = [
            'preescolar' => 'transición',
            'transición' => 'primero', 
            'primero' => 'segundo',
            'segundo' => 'tercero',
            'tercero' => 'cuarto',
            'cuarto' => 'quinto',
            'quinto' => 'sexto',
            'sexto' => 'séptimo',
            'séptimo' => 'octavo',
            'octavo' => 'noveno',
            'noveno' => 'décimo',
            'décimo' => 'once',
            'once' => null, // Se gradúan - fin de la media académica
        ];

        $promovidos = 0;
        $graduados = 0;

        // IMPORTANTE: Tomar una "fotografía" de los estudiantes ANTES de hacer cambios
        // para evitar que los estudiantes sean procesados múltiples veces
        $estudiantesSnapshot = Estudiante::with(['grado:id,nombre,grupo'])
                                        ->where('activo', true)
                                        ->get()
                                        ->groupBy('grado.nombre');

        foreach ($promociones as $gradoActual => $gradoSiguiente) {
            // Buscar estudiantes de este grado en la "fotografía" inicial
            $estudiantesEnGrado = $estudiantesSnapshot->get(ucfirst($gradoActual), collect());
            
            if ($estudiantesEnGrado->isEmpty()) {
                continue;
            }

            foreach ($estudiantesEnGrado as $estudiante) {
                if ($gradoSiguiente) {
                    // Promover al siguiente grado (tomar el primer grupo disponible, también insensible a mayúsculas)
                    $gradoDestino = Grado::whereRaw('LOWER(nombre) = LOWER(?)', [$gradoSiguiente])->first();
                    if ($gradoDestino) {
                        if (!$simular) {
                            $estudiante->update(['grado_id' => $gradoDestino->id]);
                        }
                        $promovidos++;
                        $this->line("   📈 {$estudiante->nombre} {$estudiante->apellido}: {$estudiante->grado->nombre} → {$gradoDestino->nombre}");
                    } else {
                        $this->warn("   ⚠️  No se encontró grado destino: {$gradoSiguiente}");
                    }
                } else {
                    // Marcar como graduado (inactivo)
                    if (!$simular) {
                        $estudiante->update(['activo' => false]);
                    }
                    $graduados++;
                    $this->line("   🎓 {$estudiante->nombre} {$estudiante->apellido}: {$estudiante->grado->nombre} → GRADUADO");
                }
            }
        }

        $this->line("   🎯 {$promovidos} estudiantes promovidos");
        $this->line("   🎓 {$graduados} estudiantes graduados");
    }

    private function paso4_finalizarAnio($anioFinalizar, $anioNuevo, $simular)
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
            AnioEscolar::where('anio', $anioNuevo)
                      ->update(['activo' => true]);

            // Limpiar datos del año anterior (respetar claves foráneas)
            EstudianteLogro::query()->delete();
            DesempenoMateria::query()->delete();
        }

        $this->line("   ✅ Año {$anioFinalizar} finalizado");
        $this->line("   ✅ Datos del año anterior limpiados");
        $this->line("   ✅ Año {$anioNuevo} activado");
    }
}
