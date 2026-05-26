<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cheques - Contabilidad') }}
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

            <form id="form-cheques-masivo" method="POST" action="{{ route('contabilidad.enviarAdministracionMasivo') }}">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="select-all-cheques" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                <label for="select-all-cheques" class="text-sm font-medium text-gray-700">Seleccionar todos</label>
                            </div>
                            <button type="submit" id="btn-enviar-masivo" class="hidden bg-primary-800 hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                                📤 Enviar Seleccionados a Administración
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10"></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Cheque</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Pago</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($cheques as $cheque)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($cheque->ordenPago->estado != 'enviado_administracion')
                                                <input type="checkbox" name="cheques[]" value="{{ $cheque->id }}" class="checkbox-cheque rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $cheque->numero_cheque }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $cheque->ordenPago->numero_orden }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $cheque->ordenPago->beneficiario_nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($cheque->monto, 2) }} Bs.</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $cheque->fecha_pago ? \Carbon\Carbon::parse($cheque->fecha_pago)->format('d/m/Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded 
                                                    @if($cheque->ordenPago->estado == 'enviado_presupuesto') bg-yellow-100 text-yellow-800
                                                    @elseif($cheque->ordenPago->estado == 'rechazado_presupuesto') bg-red-100 text-red-800
                                                    @elseif($cheque->ordenPago->estado == 'enviado_administracion') bg-primary-100 text-primary-900
                                                    @else bg-purple-100 text-purple-800 @endif">
                                                    {{ str_replace('_', ' ', ucfirst($cheque->ordenPago->estado)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-2">
                                                <a href="{{ route('contabilidad.showCheque', $cheque) }}" class="text-primary-700 hover:text-primary-900" title="Ver detalle">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                
                                                @if($cheque->ordenPago->estado != 'enviado_administracion')
                                                <form action="{{ route('contabilidad.enviarAdministracion', $cheque) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded font-semibold"
                                                        onclick="return confirm('¿Enviar directamente a Administración?')">
                                                        📤 Enviar Admin
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-4 text-center text-sm">No hay cheques registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $cheques->links() }}</div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Cheque -->
    <div id="modal-editar" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Editar Cheque</h3>
                <form id="form-editar" method="POST" class="mt-2 text-left">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                        <input type="number" name="monto" step="0.01" class="mt-1 block w-full border rounded-md p-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Fecha Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="mt-1 block w-full border rounded-md p-2" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea name="observaciones" rows="3" class="mt-1 block w-full border rounded-md p-2"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="cerrarModalEditar()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editarCheque(id) {
            document.getElementById('form-editar').action = `/contabilidad/cheques/${id}/editar`;
            document.getElementById('modal-editar').classList.remove('hidden');
        }
        function cerrarModalEditar() {
            document.getElementById('modal-editar').classList.add('hidden');
        }

        // Script para selección masiva
        document.getElementById('select-all-cheques').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-cheque');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBtnMasivo();
        });

        document.querySelectorAll('.checkbox-cheque').forEach(cb => {
            cb.addEventListener('change', toggleBtnMasivo);
        });

        function toggleBtnMasivo() {
            const checked = document.querySelectorAll('.checkbox-cheque:checked');
            document.getElementById('btn-enviar-masivo').classList.toggle('hidden', checked.length === 0);
        }
    </script>
</x-app-layout>