<x-app-layout>
    <x-slot name="header">Tracking de Orden</x-slot>
    <div class="py-6 max-w-4xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('tracking.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Tracking: {{ $ordenPago->numero_orden }}</h2>
        </div>

        <!-- Pipeline de estados -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            @php
            $estados = ['pendiente_tesoreria','enviado_financiera','enviado_contabilidad','enviado_presupuesto','enviado_financiera_cheque','enviado_administracion','en_caja','entregado_contabilidad','archivado'];
            $labels  = ['Tesorería','Financiera','Contabilidad','Presupuesto','Financiera','Administración','Caja','Auditoría','Archivo'];
            $idx     = array_search($ordenPago->estado, $estados);
            if ($idx === false && $ordenPago->estado === 'rechazado_financiera') $idx = 1;
            if ($idx === false && $ordenPago->estado === 'rechazado_contabilidad') $idx = 2;
            if ($idx === false && $ordenPago->estado === 'rechazado_presupuesto') $idx = 3;
            if ($idx === false && $ordenPago->estado === 'rechazado_administracion') $idx = 5;
            if ($idx === false && $ordenPago->estado === 'entregado') $idx = 6;
            if ($idx === false && $ordenPago->estado === 'enviado_archivos') $idx = 7;
            if ($idx === false && $ordenPago->estado === 'cerrado') $idx = 8;
            @endphp
            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach($estados as $i => $est)
                @php $done = $i <= $idx; $current = $i === $idx; @endphp
                <div class="flex items-center gap-1 flex-shrink-0">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $done ? 'bg-primary-700 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $done ? '✓' : ($i+1) }}
                        </div>
                        <span class="text-xs mt-1 {{ $current ? 'text-primary-800 font-semibold' : 'text-gray-400' }}">{{ $labels[$i] }}</span>
                    </div>
                    @if(!$loop->last)
                    <div class="w-8 h-0.5 mb-4 {{ $i < $idx ? 'bg-primary-700' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Info de la orden -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div><span class="text-gray-500">Beneficiario:</span> <span class="font-semibold">{{ $ordenPago->beneficiario_nombre ?? '-' }}</span></div>
                <div><span class="text-gray-500">Concepto:</span> <span class="font-medium">{{ \Illuminate\Support\Str::limit($ordenPago->concepto,40) }}</span></div>
                <div><span class="text-gray-500">Neto a Pagar:</span> <span class="font-bold text-green-700">Bs. {{ number_format($ordenPago->neto_pagar,2) }}</span></div>
            </div>
        </div>

        <!-- Historial -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Historial de Movimientos</h3>
            @if($tracking->count() > 0)
            <div class="relative">
                <div class="absolute left-3 top-0 bottom-0 w-px bg-gray-200"></div>
                <div class="space-y-4">
                    @foreach($tracking as $t)
                    <div class="flex gap-4 relative">
                        <div class="w-6 h-6 rounded-full bg-primary-700 flex items-center justify-center z-10 flex-shrink-0">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <div class="flex-1 pb-2">
                            <div class="flex items-baseline justify-between">
                                <span class="font-semibold text-sm text-gray-800">{{ ucfirst(str_replace('_',' ',$t->accion)) }}</span>
                                <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($t->fecha_hora)->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($t->comentario)<p class="text-sm text-gray-600 mt-0.5">{{ $t->comentario }}</p>@endif
                            @if($t->estado_anterior && $t->estado_nuevo)
                            <p class="text-xs text-gray-400 mt-0.5">
                                <span class="text-gray-500">{{ $t->estado_anterior }}</span> → <span class="text-primary-700 font-medium">{{ $t->estado_nuevo }}</span>
                            </p>
                            @endif
                            <p class="text-xs text-gray-400">Por: {{ $t->usuario->name ?? '-' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <p class="text-gray-400 text-sm">Sin movimientos registrados.</p>
            @endif
        </div>

        <!-- Acciones disponibles -->
        @if(count($estadosDisponibles) > 0)
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Actualizar Estado</h3>
            <form method="POST" action="{{ route('tracking.actualizar', $ordenPago) }}" class="flex gap-3 flex-wrap items-end">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado</label>
                    <select name="nuevo_estado" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        @foreach($estadosDisponibles as $est)
                        <option value="{{ $est['value'] }}">{{ $est['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Comentario</label>
                    <input type="text" name="comentario" placeholder="Opcional..." autocomplete="off" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-right mr-2"></i>Actualizar
                </button>
            </form>
        </div>
        @endif

        <!-- Registrar entrega -->
        @can('actualizar_tracking')
        @if(in_array($ordenPago->estado, ['cheque_generado', 'en_caja']))
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Registrar Entrega de Cheque</h3>
            <form method="POST" action="{{ route('tracking.entrega', $ordenPago) }}" class="grid grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recibido por <span class="text-red-500">*</span></label>
                    <input type="text" name="recibido_por" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CI del receptor <span class="text-red-500">*</span></label>
                    <input type="text" name="ci_recibido" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Entrega <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_entrega" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <input type="text" name="observaciones" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div class="col-span-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-handshake mr-2"></i>Registrar Entrega
                    </button>
                </div>
            </form>
        </div>
        @endif
        @endcan

        <a href="{{ route('tracking.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-block">Volver al listado</a>
    </div>
</x-app-layout>
