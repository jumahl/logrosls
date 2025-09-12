<x-filament-panels::page>
    <div class="space-y-6">
        
        {{-- Estadísticas del Sistema --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $estadisticas = $this->getEstadisticas();
            @endphp
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-user-group class="h-5 w-5 text-blue-500" />
                        Estudiantes Activos
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">
                        {{ number_format($estadisticas['estudiantes_activos']) }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Total de estudiantes
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="h-5 w-5 text-green-500" />
                        Grados con Estudiantes
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">
                        {{ $estadisticas['grados_con_estudiantes'] }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Grados activos
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-book-open class="h-5 w-5 text-purple-500" />
                        Materias Activas
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $estadisticas['total_materias'] }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Materias configuradas
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-calendar class="h-5 w-5 text-orange-500" />
                        Año Escolar Actual
                    </div>
                </x-slot>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600">
                        {{ $estadisticas['anio_actual'] }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Periodo activo
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Información de Advertencia --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-500" />
                    ⚠️ Información Importante sobre la Transición
                </div>
            </x-slot>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-amber-800">Proceso de Transición de Año Escolar</p>
                            <p class="text-sm text-amber-700 mt-1">
                                Este proceso archivará todos los datos del año actual y promoverá a los estudiantes al siguiente año escolar.
                            </p>
                        </div>
                    </div>
                    
                    <div class="border-t border-amber-200 pt-3">
                        <h4 class="font-medium text-amber-800 mb-2">Qué hace la transición:</h4>
                        <ul class="list-disc list-inside text-sm text-amber-700 space-y-1">
                            <li>Archiva datos históricos de estudiantes, desempeños y logros</li>
                            <li>Promueve estudiantes al siguiente grado automáticamente</li>
                            <li>Finaliza el año escolar actual</li>
                            <li>Activa el nuevo año escolar seleccionado</li>
                            <li>Resetea estados de períodos y calificaciones</li>
                        </ul>
                    </div>
                    
                    <div class="border-t border-amber-200 pt-3">
                        <h4 class="font-medium text-red-800 mb-2">⚠️ ADVERTENCIAS:</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            <li><strong>Esta acción NO se puede deshacer</strong></li>
                            <li>Se recomienda realizar un backup completo antes de ejecutar</li>
                            <li>Ejecutar solo cuando esté seguro de finalizar el año escolar</li>
                            <li>Todos los usuarios serán notificados del cambio</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Formulario de Configuración --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-cog-8-tooth class="h-5 w-5 text-blue-500" />
                    Configuración de la Transición
                </div>
            </x-slot>
            
            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Pasos del Proceso --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="h-5 w-5 text-indigo-500" />
                    Pasos del Proceso de Transición
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">1</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Validación de Datos</h4>
                        <p class="text-sm text-gray-600">Verificación de la integridad de los datos antes de proceder</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">2</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Archivo de Datos Históricos</h4>
                        <p class="text-sm text-gray-600">Backup de estudiantes, desempeños y logros del año actual</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">3</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Promoción de Estudiantes</h4>
                        <p class="text-sm text-gray-600">Avance automático de estudiantes al siguiente grado</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">4</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Limpieza y Reset</h4>
                        <p class="text-sm text-gray-600">Limpieza de datos temporales y reset de configuraciones</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-green-600">✓</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Activación del Nuevo Año</h4>
                        <p class="text-sm text-gray-600">Activación del nuevo año escolar y notificación a usuarios</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Output de Simulación --}}
        @if(session()->has('simulacion_output'))
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="h-5 w-5 text-green-500" />
                    Resultado de la Simulación
                </div>
            </x-slot>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ session('simulacion_output') }}</pre>
            </div>
        </x-filament::section>
        @endif

        {{-- Recomendaciones Finales --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="h-5 w-5 text-yellow-500" />
                    Recomendaciones Antes de Ejecutar
                </div>
            </x-slot>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="space-y-3">
                    <h4 class="font-medium text-blue-800">Lista de Verificación:</h4>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" class="mt-1 rounded border-blue-300 text-blue-600">
                            <span class="text-sm text-blue-700">He realizado un backup completo de la base de datos</span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input type="checkbox" class="mt-1 rounded border-blue-300 text-blue-600">
                            <span class="text-sm text-blue-700">He verificado que todos los boletines están generados</span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input type="checkbox" class="mt-1 rounded border-blue-300 text-blue-600">
                            <span class="text-sm text-blue-700">He notificado a todos los usuarios sobre la transición</span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input type="checkbox" class="mt-1 rounded border-blue-300 text-blue-600">
                            <span class="text-sm text-blue-700">He ejecutado una simulación exitosa</span>
                        </label>
                        <label class="flex items-start gap-3">
                            <input type="checkbox" class="mt-1 rounded border-blue-300 text-blue-600">
                            <span class="text-sm text-blue-700">Confirmo que deseo finalizar el año escolar actual</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
