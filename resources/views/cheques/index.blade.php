<x-app-layout>
    <x-slot name="header">Cheques</x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-money-check mr-2 text-primary-800"></i>Gestión de Cheques
                </h2>
            </div>

            @if($ordenesPendientes->count() > 0)
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-bold text-yellow-800 mb-4 flex items-center">
                    <i class="fas fa-file-invoice mr-2"></i>Órdenes de Pago Pendientes de Cheque
                    <span class="ml-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">{{ $ordenesPendientes->count() }}</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-yellow-100 text-yellow-800 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">N° Orden</th>
                                <th class="px-4 py-3 text-left">Beneficiario</th>
                                <th class="px-4 py-3 text-left">CI/NIT/N° Patronal</th>
                                <th class="px-4 py-3 text-right">Monto</th>
                                <th class="px-4 py-3 text-center">Fecha</th>
                                <th class="px-4 py-3 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-yellow-100">
                            @foreach($ordenesPendientes as $op)
                            <tr class="hover:bg-yellow-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-gray-800">{{ $op->numero_orden }}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $op->beneficiario_nombre ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $op->beneficiario_ci_nit ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-green-700">Bs. {{ number_format($op->neto_pagar, 2) }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $op->fecha_orden?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cheques.create', ['ordenPago' => $op->id]) }}" class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
                                        <i class="fas fa-money-check mr-1"></i> Generar Cheque
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                        <i class="fas fa-list mr-2 text-gray-500"></i>Cheques Emitidos
                    </h3>
                </div>
                <form method="GET" class="bg-gray-50 p-4 flex gap-3 flex-wrap items-end border-b border-gray-100">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Estado</label>
                        <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none bg-white">
                            <option value="">Todos</option>
                            @foreach(['emitido'=>'Emitido','impreso'=>'Impreso','entregado'=>'Entregado','anulado'=>'Anulado'] as $v=>$l)
                            <option value="{{ $v }}" {{ request('estado')==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                    </div>
                    <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-search mr-1"></i>Filtrar
                    </button>
                </form>

                <form id="form-cheques" method="POST" action="{{ route('cheques.enviar-masivo') }}">
                    @csrf
                    <div class="p-3 bg-gray-50 border-b flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="select-all-cheques" class="w-4 h-4 text-primary-800 rounded focus:ring-primary-500">
                            <label for="select-all-cheques" class="text-sm text-gray-600 cursor-pointer">Seleccionar todos</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" id="btn-imprimir-cheques" class="hidden bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-print mr-1"></i> Imprimir Seleccionados
                            </button>
                            <button type="submit" formaction="{{ route('cheques.enviar-masivo') }}" id="btn-enviar-cheques" class="hidden bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar a Presupuesto
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-center w-10"></th>
                                <th class="px-4 py-3 text-left">N° Cheque</th>
                                <th class="px-4 py-3 text-left">Beneficiario</th>
                                <th class="px-4 py-3 text-left">Fecha Emisión</th>
                                <th class="px-4 py-3 text-right">Monto</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($cheques as $ch)
                            @php $c=['emitido'=>'yellow','impreso'=>'blue','entregado'=>'green','anulado'=>'red'][$ch->estado]??'gray'; @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    @if(in_array($ch->estado, ['emitido', 'impreso']) && in_array($ch->ordenPago->estado, ['cheque_generado', 'rechazado_presupuesto']))
                                    <input type="checkbox" name="cheques[]" value="{{ $ch->id }}" class="w-4 h-4 text-primary-800 rounded focus:ring-primary-500 checkbox-cheque">
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono font-bold {{ $ch->numero_cheque ? 'text-gray-800' : 'text-red-600' }}">{{ $ch->numero_cheque ?? 'Pendiente' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $ch->ordenPago->beneficiario_nombre ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $ch->fecha_emision?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-green-700">Bs. {{ number_format($ch->monto,2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">{{ ucfirst($ch->estado) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('cheques.show', $ch) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-800 px-2 py-1.5 rounded-lg text-sm font-medium transition-colors" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <i class="fas fa-money-check text-4xl mb-3 block opacity-30"></i>
                                    <p>No hay cheques registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="mt-4">{{ $cheques->withQueryString()->links() }}</div>
        </div>
    </div>

    <script>
        document.getElementById('select-all-cheques').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-cheque');
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBtnCheques();
        });

        document.querySelectorAll('.checkbox-cheque').forEach(cb => {
            cb.addEventListener('change', toggleBtnCheques);
        });

        function toggleBtnCheques() {
            const checked = document.querySelectorAll('.checkbox-cheque:checked');
            document.getElementById('btn-enviar-cheques').classList.toggle('hidden', checked.length === 0);
            document.getElementById('btn-imprimir-cheques').classList.toggle('hidden', checked.length === 0);
        }

        document.getElementById('btn-imprimir-cheques').addEventListener('click', function() {
            const checked = document.querySelectorAll('.checkbox-cheque:checked');
            if (checked.length === 0) return;
            if (checked.length > 4) {
                alert('Solo se pueden imprimir máximo 4 cheques a la vez');
                return;
            }
            const ids = Array.from(checked).map(cb => cb.value).join(',');
            const url = '{{ route("cheques.imprimir-seleccionados") }}?ids=' + ids;
            window.open(url, '_blank');
        });

        document.getElementById('btn-enviar-cheques').addEventListener('click', function(e) {
            const checked = document.querySelectorAll('.checkbox-cheque:checked');
            if (checked.length === 0) {
                e.preventDefault();
                return;
            }
        });
    </script>
</x-app-layout>