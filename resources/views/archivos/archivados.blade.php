<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Órdenes Archivadas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Historial de Órdenes Archivadas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Orden</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Cheque</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beneficiario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Archivo</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $orden->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('ordenes-pago.show', $orden) }}" class="text-primary-600 hover:text-primary-900 text-xs font-bold">Ver Historial</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay órdenes archivadas aún.</td>
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
