<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $period = $this->getCurrentPeriod();
        @endphp

        @if($period)
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Periodo Actual: {{ $period->nombre }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $period->fecha_inicio->format('d/m/Y') }} - {{ $period->fecha_fin->format('d/m/Y') }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                        Activo
                    </span>
                </div>
            </div>
        @else
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    No hay periodo activo
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    No se encontró ningún periodo activo en el rango de fechas actual.
                </p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget> 