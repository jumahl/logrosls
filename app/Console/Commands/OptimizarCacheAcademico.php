<?php

namespace App\Console\Commands;

use App\Services\CacheAcademicoService;
use Illuminate\Console\Command;

class OptimizarCacheAcademico extends Command
{
    protected $signature = 'cache:optimizar-academico 
                            {--limpiar : Limpiar todo el cache académico}
                            {--precargar : Precargar cache para todos los profesores}';
                            
    protected $description = 'Optimiza el cache académico del sistema';

    public function handle(CacheAcademicoService $cacheService): int
    {
        if ($this->option('limpiar')) {
            $this->info('Limpiando cache académico...');
            $cacheService->limpiarTodoCache();
            $this->info('✅ Cache académico limpiado exitosamente.');
        }

        if ($this->option('precargar')) {
            $this->info('Precargando cache para profesores...');
            $cacheService->precargaCacheProfesores();
            $this->info('✅ Cache precargado exitosamente.');
        }

        if (!$this->option('limpiar') && !$this->option('precargar')) {
            $this->info('Ejecutando optimización completa...');
            
            // Limpiar cache existente
            $this->info('1. Limpiando cache existente...');
            $cacheService->limpiarTodoCache();
            
            // Precargar cache nuevo
            $this->info('2. Precargando cache optimizado...');
            $cacheService->precargaCacheProfesores();
            
            $this->info('✅ Optimización completa terminada.');
        }

        return 0;
    }
}
