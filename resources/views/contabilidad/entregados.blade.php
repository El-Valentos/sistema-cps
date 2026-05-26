<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cheques Entregados - Archivo') }}
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
                                            <form action="{{ route('contabilidad.archivar', $orden) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white px-3 py-1 rounded text-xs font-bold" onclick="return confirm('¿Seguro que desea archivar esta orden definitivamente?')">
                                                    📁 Archivar
                                                </button>
                                            </form>
                                            <a href="{{ route('ordenes-pago.show', $orden) }}" class="ml-2 text-primary-600 hover:text-primary-900 text-xs font-bold">Ver Detalle</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay órdenes entregadas pendientes de archivo.</td>
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
