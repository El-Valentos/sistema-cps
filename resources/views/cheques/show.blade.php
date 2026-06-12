<x-app-layout>
    <x-slot name="header">Cheque</x-slot>
    <div class="py-6 max-w-3xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('cheques.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Cheque {{ $cheque->numero_cheque ? "N° {$cheque->numero_cheque}" : '(N° pendiente)' }}</h2>
            @php $c=['emitido'=>'blue','impreso'=>'indigo','anulado'=>'red'][$cheque->estado]??'gray'; @endphp
            <span class="px-3 py-1 rounded-full text-sm bg-{{ $c }}-100 text-{{ $c }}-700">{{ ucfirst($cheque->estado) }}</span>
        </div>

        @if(!$cheque->numero_cheque)
        <div class="bg-red-50 border-2 border-red-400 rounded-xl p-5 mb-6">
            <h3 class="text-sm font-bold text-red-700 mb-3"><i class="fas fa-exclamation-triangle mr-2"></i>Asignar Número de Cheque</h3>
            <form method="POST" action="{{ route('cheques.asignar-numero', $cheque) }}" class="flex items-end gap-3">
                @csrf
                @method('PUT')
                <div class="flex-1">
                    <label class="block text-xs text-red-600 mb-1 font-medium">Número de Cheque</label>
                    <input type="text" name="numero_cheque" placeholder="Ej: 12345" required
                        class="w-full border-2 border-red-400 rounded-lg px-3 py-2 text-sm focus:border-red-600 focus:ring focus:ring-red-200">
                    @error('numero_cheque') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-save mr-1"></i>Guardar
                </button>
            </form>
        </div>
        @endif
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Fecha Emisión:</span> <span class="font-medium">{{ $cheque->fecha_emision?->format('d/m/Y') }}</span></div>
                <div><span class="text-gray-500">Beneficiario:</span> <span class="font-semibold">{{ $cheque->ordenPago->beneficiario_nombre ?? '-' }}</span></div>
                <div><span class="text-gray-500">Orden de Pago:</span> <a href="{{ route('ordenes-pago.show', $cheque->ordenPago) }}" class="text-primary-700 hover:underline font-medium">{{ $cheque->ordenPago->numero_orden }}</a></div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                <p class="text-xs text-green-600">Monto</p>
                <p class="text-3xl font-bold text-green-700">Bs. {{ number_format($cheque->monto,2) }}</p>
                <p class="text-xs text-green-600 mt-1 italic">{{ $cheque->monto_literal }}</p>
            </div>
        </div>
        <div class="flex gap-3 flex-wrap">
            @if($cheque->estado === 'emitido')
            <form method="POST" action="{{ route('cheques.confirmar', $cheque) }}" class="inline-block">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" title="Confirmar y enviar a Presupuesto">
                    <i class="fas fa-check mr-2"></i>Confirmar y Enviar
                </button>
            </form>
            <a href="{{ route('cheques.editar', $cheque) }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            @endif

            @if($cheque->ordenPago->estado === 'enviado_presupuesto' && auth()->user()->hasRole('Presupuesto'))
            <form method="POST" action="{{ route('presupuesto.aprobar', $cheque) }}" class="inline-block">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" title="Aprobar y enviar a Financiera">
                    <i class="fas fa-check mr-2"></i>Aprobar (Enviar a Financiera)
                </button>
            </form>
            @endif

            @if($cheque->ordenPago->estado === 'enviado_financiera_cheque' && auth()->user()->hasRole('Financiera'))
            <form method="POST" action="{{ route('financiera.cheques.aprobar', $cheque) }}" class="inline-block">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" title="Aprobar y enviar a Administración">
                    <i class="fas fa-check mr-2"></i>Aprobar (Enviar a Administración)
                </button>
            </form>
            @endif

            @if($cheque->ordenPago->estado === 'enviado_administracion' && auth()->user()->hasRole('Administración'))
            <form method="POST" action="{{ route('administracion.aprobar', $cheque) }}" class="inline-block">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" title="Aprobar y enviar a Caja">
                    <i class="fas fa-check mr-2"></i>Aprobar (Enviar a Caja)
                </button>
            </form>
            @endif
            <a href="{{ route('cheques.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Volver</a>
        </div>
    </div>


</x-app-layout>
