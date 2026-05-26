<x-app-layout>
    <x-slot name="header">Órdenes de Pago</x-slot>
    <div class="py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Listado de Órdenes de Pago</h2>
            @can('crear_orden_pago')
            <a href="{{ route('ordenes-pago.create') }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Nueva Orden
            </a>
            @endcan
        </div>
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex gap-3 flex-wrap">
            <input type="text" name="beneficiario" value="{{ request('beneficiario') }}" placeholder="Buscar beneficiario..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none flex-1 min-w-48">
            <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">Todos los estados</option>
                @foreach(['pendiente_tesoreria'=>'Pendiente Tesorería','enviado_contabilidad'=>'Enviado Contabilidad','cheque_generado'=>'Cheque Generado','en_caja'=>'En Caja','entregado'=>'Entregado','cerrado'=>'Cerrado'] as $val=>$label)
                <option value="{{ $val }}" {{ request('estado')==$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>Buscar</button>
            @if(request()->hasAny(['beneficiario','estado','fecha_desde','fecha_hasta']))
            <a href="{{ route('ordenes-pago.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">Limpiar</a>
            @endif
        </form>
        
        <form id="form-enviar-masivo" method="POST" action="{{ route('ordenes-pago.enviar-masivo') }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <div class="p-3 bg-gray-50 border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all" class="w-4 h-4 text-primary-800 rounded focus:ring-primary-500">
                        <label for="select-all" class="text-sm text-gray-600 cursor-pointer">Seleccionar todos</label>
                    </div>
                    <button type="submit" id="btn-enviar-seleccionados" class="hidden bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Seleccionados a Financiera
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-center w-10"></th>
                                <th class="px-4 py-3 text-left">N° Orden</th>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Beneficiario</th>
                                <th class="px-4 py-3 text-left">Concepto</th>
                                <th class="px-4 py-3 text-right">Neto a Pagar</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ordenes as $op)
                            @php
                            $colors = ['pendiente_tesoreria'=>'yellow','enviado_contabilidad'=>'blue','cheque_generado'=>'indigo','en_caja'=>'purple','entregado'=>'green','cerrado'=>'gray','anulado'=>'red'];
                            $c = $colors[$op->estado] ?? 'gray';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">
                                    @if($op->estado === 'pendiente_tesoreria')
                                    <input type="checkbox" name="ordenes[]" value="{{ $op->id }}" class="w-4 h-4 text-primary-800 rounded focus:ring-primary-500 checkbox-item">
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono font-medium text-primary-800">{{ $op->numero_orden }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $op->fecha_orden?->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $op->beneficiario_nombre ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($op->concepto, 35) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800">Bs. {{ number_format($op->neto_pagar, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700">{{ $op->estado_label }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('ordenes-pago.show', $op) }}" class="text-primary-700 hover:text-primary-900 text-xs px-2 py-1 rounded bg-primary-50 hover:bg-primary-100"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('ordenes-pago.pdf', $op) }}" target="_blank" class="text-red-600 hover:text-red-800 text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100"><i class="fas fa-file-pdf"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400"><i class="fas fa-file-invoice text-4xl mb-3 block opacity-30"></i>No hay órdenes de pago</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
        <div class="mt-4">{{ $ordenes->withQueryString()->links() }}</div>
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBtnEnviar();
        });

        document.querySelectorAll('.checkbox-item').forEach(cb => {
            cb.addEventListener('change', toggleBtnEnviar);
        });

        function toggleBtnEnviar() {
            const checked = document.querySelectorAll('.checkbox-item:checked');
            document.getElementById('btn-enviar-seleccionados').classList.toggle('hidden', checked.length === 0);
        }
    </script>
</x-app-layout>
