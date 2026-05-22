<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Cheque') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">N° Cheque</p>
                            <p class="font-medium">{{ $cheque->numero_cheque }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° Orden</p>
                            <p class="font-medium">{{ $cheque->ordenPago->numero_orden }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Beneficiario</p>
                            <p class="font-medium">{{ $cheque->ordenPago->beneficiario_nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Monto</p>
                            <p class="font-medium text-lg">{{ number_format($cheque->monto, 2) }} Bs.</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha Emisión</p>
                            <p class="font-medium">{{ \Carbon\Carbon::parse($cheque->fecha_emision)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                @if($cheque->ordenPago->estado == 'cheque_generado') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $cheque->ordenPago->estado_label }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <form action="{{ route('presupuesto.aprobar', $cheque) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" onclick="return confirm('¿Aprobar y enviar a Administración?')">
                                Aprobar
                            </button>
                        </form>
                        <button onclick="document.getElementById('modal-rechazo').classList.remove('hidden')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Rechazar
                        </button>
                        <a href="{{ route('presupuesto.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Motivo de Rechazo</h3>
            <form method="POST" action="{{ route('presupuesto.rechazar', $cheque) }}">
                @csrf
                <textarea name="motivo_rechazo" rows="4" class="w-full border rounded-md p-2" required placeholder="Explique el motivo..."></textarea>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="document.getElementById('modal-rechazo').classList.add('hidden')" class="px-4 py-2 bg-gray-500 text-white rounded">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>