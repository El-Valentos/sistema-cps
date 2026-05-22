<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bandeja Presupuesto - Cheques') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="form-masivo" method="POST" action="{{ route('presupuesto.aprobarMasivo') }}">
                        @csrf
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                <label for="select-all" class="text-sm font-medium text-gray-700">Seleccionar todos</label>
                            </div>
                            <button type="submit" id="btn-masivo" class="hidden bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar Seleccionados a Financiera
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10"></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cheque N°</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($cheques as $cheque)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <input type="checkbox" name="cheques[]" value="{{ $cheque->id }}" class="checkbox-item rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $cheque->numero_cheque }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cheque->ordenPago->numero_orden }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $cheque->ordenPago->beneficiario_nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($cheque->monto, 2) }} Bs.</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex gap-2">
                                                    <a href="{{ route('cheques.show', $cheque) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                        <i class="fas fa-eye mr-1"></i> Ver
                                                    </a>

                                                    <button type="button" onclick="aprobarIndividual({{ $cheque->id }})" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                        <i class="fas fa-check mr-1"></i> Aprobar
                                                    </button>

                                                    <button type="button" onclick="rechazarCheque({{ $cheque->id }})" class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                        <i class="fas fa-times mr-1"></i> Rechazar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-sm">No hay cheques pendientes para Presupuesto.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <div class="mt-4">{{ $cheques->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms ocultos para acciones individuales -->
    <form id="form-individual" method="POST" style="display: none;">
        @csrf
    </form>

    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Motivo de Rechazo del Cheque</h3>
                <form id="form-rechazo" method="POST" class="mt-2 text-left">
                    @csrf
                    <textarea name="motivo_rechazo" rows="4" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" required placeholder="Explique el motivo del rechazo..."></textarea>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="cerrarModalRechazo()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Rechazar Cheque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function aprobarIndividual(id) {
            if(confirm('¿Aprobar y enviar cheque a Financiera?')) {
                const form = document.getElementById('form-individual');
                form.action = `/presupuesto/${id}/aprobar`;
                form.submit();
            }
        }

        function rechazarCheque(id) {
            document.getElementById('form-rechazo').action = `/presupuesto/${id}/rechazar`;
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
        </div>
    </div>

    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Motivo de Rechazo del Cheque</h3>
                <form id="form-rechazo" method="POST" class="mt-2 text-left">
                    @csrf
                    <textarea name="motivo_rechazo" rows="4" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" required placeholder="Explique el motivo del rechazo..."></textarea>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="cerrarModalRechazo()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Rechazar Cheque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rechazarCheque(id) {
            document.getElementById('form-rechazo').action = `/presupuesto/${id}/rechazar`;
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