<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle del Cheque') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">N° Cheque</p>
                            <p class="font-medium text-lg">{{ $cheque->numero_cheque }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° Orden</p>
                            <p class="font-medium text-lg">{{ $cheque->ordenPago->numero_orden }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Beneficiario</p>
                            <p class="font-medium">{{ $cheque->ordenPago->beneficiario_nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Monto</p>
                            <p class="font-medium text-xl">{{ number_format($cheque->monto, 2) }} Bs.</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha Emisión</p>
                            <p class="font-medium">{{ \Carbon\Carbon::parse($cheque->fecha_emision)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha Pago</p>
                            <p class="font-medium">{{ $cheque->fecha_pago ? \Carbon\Carbon::parse($cheque->fecha_pago)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                @if($cheque->estado == 'emitido') bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($cheque->estado) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Concepto</p>
                            <p class="font-medium">{{ $cheque->ordenPago->concepto }}</p>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <p class="text-sm text-gray-500 mb-2">Acciones</p>
                        <div class="flex flex-wrap gap-2">
                            <!-- Editar -->
                            <button onclick="document.getElementById('modal-editar').classList.remove('hidden')" 
                                class="bg-primary-700 text-white px-4 py-2 rounded hover:bg-primary-700">
                                Editar
                            </button>

                            <!-- Enviar a Presupuesto -->
                            @if($cheque->estado == 'emitido')
                            <form action="{{ route('contabilidad.enviarPresupuesto', $cheque) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="bg-cyan-600 text-white px-4 py-2 rounded hover:bg-cyan-700"
                                    onclick="return confirm('¿Enviar a Presupuesto?')">
                                    Enviar a Presupuesto
                                </button>
                            </form>

                            <!-- Enviar a Administración -->
                            <form action="{{ route('contabilidad.enviarAdministracion', $cheque) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                                    onclick="return confirm('¿Enviar a Administración?')">
                                    Enviar a Administración
                                </button>
                            </form>

                            <!-- Anular -->
                            <form action="{{ route('contabilidad.anularCheque', $cheque) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                                    onclick="return confirm('¿Anular el cheque? Esta acción no se puede deshacer.')">
                                    Anular
                                </button>
                            </form>
                            @endif

                            <a href="{{ route('contabilidad.cheques') }}" 
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="modal-editar" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Cheque</h3>
            <form method="POST" action="{{ route('contabilidad.editarCheque', $cheque) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Monto</label>
                    <input type="number" name="monto" step="0.01" value="{{ $cheque->monto }}" 
                        class="mt-1 block w-full border rounded-md p-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Fecha Pago</label>
                    <input type="date" name="fecha_pago" 
                        value="{{ $cheque->fecha_pago ? \Carbon\Carbon::parse($cheque->fecha_pago)->format('Y-m-d') : '' }}" 
                        class="mt-1 block w-full border rounded-md p-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones" rows="3" 
                        class="mt-1 block w-full border rounded-md p-2">{{ $cheque->ordenPago->observaciones }}</textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-editar').classList.add('hidden')" 
                        class="px-4 py-2 bg-gray-500 text-white rounded">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-700 text-white rounded">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>