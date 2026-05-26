<x-app-layout>
    <x-slot name="header">Módulo de Caja</x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Cheques para Entrega</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" action="{{ route('caja.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="numero_orden" value="{{ request('numero_orden') }}" placeholder="Buscar por N° de Orden..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div class="w-48">
                    <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="">Todos los estados</option>
                        <option value="cheque_generado" {{ request('estado') === 'cheque_generado' ? 'selected' : '' }}>Cheque Generado</option>
                        <option value="en_caja" {{ request('estado') === 'en_caja' ? 'selected' : '' }}>En Caja</option>
                        <option value="entregado" {{ request('estado') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                    </select>
                </div>
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                @if(request()->anyFilled(['numero_orden', 'estado']))
                <a href="{{ route('caja.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                    Limpiar
                </a>
                @endif
            </form>
        </div>

        <form id="form-masivo" method="POST" action="{{ route('caja.enviarContabilidadMasivo') }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        <label for="select-all" class="text-sm font-medium text-gray-700">Seleccionar todos los entregados</label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" id="btn-masivo-archivos" class="hidden bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                            📤 Enviar Seleccionados a Archivos
                        </button>
                        <button type="button" id="btn-masivo-revalidar" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all">
                            Revalidar Seleccionados
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                                <th class="px-6 py-4 w-10"></th>
                                <th class="px-6 py-4">Orden</th>
                                <th class="px-6 py-4">Beneficiario</th>
                                <th class="px-6 py-4">Cheque N°</th>
                                <th class="px-6 py-4">Monto</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ordenes as $orden)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($orden->estado === 'entregado')
                                    <input type="checkbox" name="ordenes[]" value="{{ $orden->id }}" class="checkbox-item rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">{{ $orden->numero_orden }}</td>
                                <td class="px-6 py-4">{{ $orden->beneficiario_nombre ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $orden->cheque->numero_cheque ?? '-' }}</td>
                                <td class="px-6 py-4 font-bold text-green-700">Bs. {{ number_format($orden->neto_pagar, 2) }}</td>
                                <td class="px-6 py-4">
                                    @if($orden->estado === 'cheque_generado')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Cheque Generado</span>
                                    @elseif($orden->estado === 'en_caja')
                                    <span class="px-2 py-1 bg-primary-100 text-primary-800 rounded-full text-xs font-medium">En Caja</span>
                                    @elseif($orden->estado === 'entregado')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Entregado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('caja.show', $orden) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-eye mr-1"></i> Ver y Entregar
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                    <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
                                    <p>No hay cheques pendientes en caja.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($ordenes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $ordenes->links() }}
                </div>
                @endif
            </div>
        </form>

        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.checkbox-item');
                checkboxes.forEach(cb => cb.checked = this.checked);
                toggleBtnArchivos();
            });

            document.querySelectorAll('.checkbox-item').forEach(cb => {
                cb.addEventListener('change', toggleBtnArchivos);
            });

            document.getElementById('btn-masivo-revalidar').addEventListener('click', function() {
                if (!confirm('¿Revalidar los cheques seleccionados?')) return;
                const form = document.getElementById('form-masivo');
                form.action = '{{ route("caja.revalidarMasivo") }}';
                form.submit();
            });

            function toggleBtnArchivos() {
                const checked = document.querySelectorAll('.checkbox-item:checked');
                document.getElementById('btn-masivo-archivos').classList.toggle('hidden', checked.length === 0);
            }
        </script>
    </div>
</x-app-layout>
