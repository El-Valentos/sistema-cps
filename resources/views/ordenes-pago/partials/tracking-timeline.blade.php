<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <svg class="w-5 h-5 mr-2 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Línea de Tiempo - Seguimiento
    </h3>
    
    <div class="relative">
        <!-- Línea vertical -->
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
        
        @php
            $iconos = [
                'creacion' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'envio_contabilidad' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'generacion_cheque' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'entrega' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'cierre' => 'M5 13l4 4L19 7'
            ];
            $colores = [
                'creacion' => 'bg-primary-500',
                'envio_contabilidad' => 'bg-indigo-500',
                'generacion_cheque' => 'bg-purple-500',
                'entrega' => 'bg-green-500',
                'cierre' => 'bg-gray-500'
            ];
        @endphp
        
        @foreach($tracking as $evento)
            <div class="relative pl-12 pb-8">
                <div class="absolute left-0 top-0 w-8 h-8 rounded-full flex items-center justify-center {{ $colores[$evento->accion] ?? 'bg-gray-400' }}">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconos[$evento->accion] ?? 'M5 13l4 4L19 7' }}"></path>
                    </svg>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-semibold text-gray-800">
                                {{ $evento->accion_label }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                Por: {{ $evento->usuario->name }}
                                @if($evento->areaOrigen)
                                    ({{ $evento->areaOrigen->nombre }})
                                @endif
                            </p>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $evento->fecha_hora->format('d/m/Y H:i:s') }}
                        </span>
                    </div>
                    
                    @if($evento->estado_anterior && $evento->estado_nuevo)
                        <div class="mt-2 text-sm">
                            <span class="text-gray-500">Estado:</span>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-200">{{ $evento->estado_anterior_label }}</span>
                            <svg class="inline-block w-4 h-4 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">{{ $evento->estado_nuevo_label }}</span>
                        </div>
                    @endif
                    
                    @if($evento->comentario)
                        <div class="mt-2 p-2 bg-white rounded border border-gray-200">
                            <p class="text-sm text-gray-700">{{ $evento->comentario }}</p>
                        </div>
                    @endif
                    
                    @if($evento->metadata && count($evento->metadata) > 0)
                        <div class="mt-2 text-xs text-gray-500">
                            @foreach($evento->metadata as $key => $value)
                                <span class="inline-block mr-3"><strong>{{ $key }}:</strong> {{ $value }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>