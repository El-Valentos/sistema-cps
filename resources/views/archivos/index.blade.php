<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bandeja de Entrada - Archivos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Órdenes pendientes de archivo definitivo</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $orden->numero_orden }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->cheque->numero_cheque ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->beneficiario_nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($orden->neto_pagar, 2) }} Bs.</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->fecha_cierre ? \Carbon\Carbon::parse($orden->fecha_cierre)->format('d/m/Y') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex gap-2">
                                                <a href="{{ route('ordenes-pago.show', $orden) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalle
                                                </a>
                                                <a href="{{ route('cheques.show', $orden->cheque) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                                                    <i class="fas fa-money-check mr-1"></i> Ver Cheque
                                                </a>
                                                <a href="{{ url('/archivos/' . $orden->id . '/archivar') }}" 
                                                   class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
                                                   onclick="event.preventDefault(); if(confirm('¿Seguro que desea archivar esta orden definitivamente?')) { fetch(this.href, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => window.location.reload()); }">
                                                    <i class="fas fa-folder mr-1"></i> Archivar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay órdenes pendientes de archivo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $ordenes->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
