<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Auditoría de Cheques Entregados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <form id="form-masivo" method="POST" action="{{ route('contabilidad.enviarArchivosMasivo') }}">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                            <label for="select-all" class="text-sm font-medium text-gray-700">Seleccionar todos para envío a Archivos</label>
                        </div>
                        <button type="submit" id="btn-masivo" class="hidden bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                            <i class="fas fa-folder mr-2"></i> Enviar Seleccionados a Archivos
                        </button>
                    </div>

                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 w-10"></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Orden</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Cheque</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Entrega</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($ordenes as $orden)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="ordenes[]" value="{{ $orden->id }}" class="checkbox-item rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->cheque->numero_cheque ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($orden->neto_pagar, 2) }} Bs.</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->fecha_cierre ? \Carbon\Carbon::parse($orden->fecha_cierre)->format('d/m/Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('ordenes-pago.show', $orden) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalle
                                                </a>
                                                <a href="{{ url('/contabilidad/' . $orden->id . '/enviar-archivos') }}" 
                                                   class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ml-2"
                                                   onclick="event.preventDefault(); if(confirm('¿Auditar y enviar a Archivos?')) { fetch(this.href, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.reload()); }">
                                                    <i class="fas fa-check mr-1"></i> Auditar y Enviar
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No hay cheques pendientes de auditoría.</td>
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

    <script>
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
