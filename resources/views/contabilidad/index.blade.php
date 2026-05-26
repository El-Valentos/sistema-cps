<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bandeja Contabilidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <form id="form-masivo" method="POST" action="{{ route('contabilidad.aprobarMasivo') }}">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                <label for="select-all" class="text-sm font-medium text-gray-700">Seleccionar todos</label>
                            </div>
                            <button type="submit" id="btn-masivo" class="hidden bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                                🚀 Aprobar Seleccionados y Generar Cheques
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10"></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($ordenes as $orden)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <input type="checkbox" name="ordenes[]" value="{{ $orden->id }}" class="checkbox-item rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-6 py-4 text-sm">{{ Str::limit($orden->concepto, 50) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($orden->neto_pagar, 2) }} Bs.</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-2">
                                                <a href="{{ route('ordenes-pago.show', $orden) }}" class="text-primary-700 hover:text-primary-900 bg-primary-100 px-3 py-1 rounded">Ver</a>
                                                
                                                <form action="{{ route('contabilidad.aprobar', $orden) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded" onclick="return confirm('¿Aprobar y generar cheque?')">Aprobar</button>
                                                </form>

                                                <button type="button" onclick="rechazarOrden({{ $orden->id }})" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded">Rechazar</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-sm">No hay órdenes pendientes de contablidad.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $ordenes->links() }}</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Motivo de Rechazo</h3>
                <form id="form-rechazo" method="POST" class="mt-2 text-left">
                    @csrf
                    <textarea name="motivo_rechazo" rows="4" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" required placeholder="Explique el motivo..."></textarea>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="cerrarModalRechazo()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Rechazar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rechazarOrden(id) {
            document.getElementById('form-rechazo').action = `/contabilidad/${id}/rechazar`;
            document.getElementById('modal-rechazo').classList.remove('hidden');
        }
        function cerrarModalRechazo() {
            document.getElementById('modal-rechazo').classList.add('hidden');
        }

        // Script para selección masiva
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBtnMasivo();
        });

        document.querySelectorAll('.checkbox-item').forEach(cb => {
            cb.addEventListener('change', toggleBtnMasivo);
        });

        function toggleBtnMasivo() {
            const checked = document.querySelectorAll('.checkbox-item:checked');
            document.getElementById('btn-masivo').classList.toggle('hidden', checked.length === 0);
        }
    </script>
</x-app-layout>