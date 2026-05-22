<x-app-layout>
    <x-slot name="header">Tracking</x-slot>
    <div class="py-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Seguimiento de Órdenes</h2>
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex gap-3 flex-wrap">
            <input type="text" name="numero_orden" value="{{ request('numero_orden') }}" placeholder="N° de Orden..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none flex-1 min-w-48">
            <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">Todos los estados</option>
                @foreach(['pendiente_tesoreria'=>'Pendiente Tesorería','enviado_financiera'=>'Enviado Financiera','enviado_contabilidad'=>'Enviado Contabilidad','enviado_presupuesto'=>'Enviado Presupuesto','enviado_financiera_cheque'=>'Revisión Financiera','enviado_administracion'=>'Enviado Administración','en_caja'=>'En Caja','entregado'=>'Entregado','cerrado'=>'Cerrado'] as $v=>$l)
                <option value="{{ $v }}" {{ request('estado')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>Buscar</button>
        </form>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">N° Orden</th>
                        <th class="px-4 py-3 text-left">Beneficiario</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3 text-center">Estado Actual</th>
                        <th class="px-4 py-3 text-left">Último Movimiento</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ordenes as $op)
                    @php $c=['pendiente_tesoreria'=>'yellow','enviado_financiera'=>'blue','enviado_contabilidad'=>'indigo','enviado_presupuesto'=>'orange','enviado_financiera_cheque'=>'teal','enviado_administracion'=>'purple','en_caja'=>'pink','entregado'=>'green','cerrado'=>'gray'][$op->estado]??'gray'; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-medium text-primary-800">{{ $op->numero_orden }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $op->beneficiario_nombre ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold">Bs. {{ number_format($op->neto_pagar,2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700">{{ $op->estado_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            @if($op->trackingHistorial->first())
                            {{ $op->trackingHistorial->first()->accion }} — {{ \Carbon\Carbon::parse($op->trackingHistorial->first()->fecha_hora)->format('d/m/Y H:i') }}
                            @else — @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('tracking.show', $op) }}" class="text-primary-700 hover:text-primary-900 text-xs px-3 py-1 rounded bg-primary-50 hover:bg-primary-100 font-medium">
                                Ver tracking
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400"><i class="fas fa-route text-4xl mb-3 block opacity-30"></i>No hay órdenes para mostrar</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $ordenes->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
