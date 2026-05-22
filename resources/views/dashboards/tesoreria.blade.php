@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Tesorería</h1>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Órdenes</p>
                    <p class="text-2xl font-bold">{{ $stats['total_ordenes'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $pendientes }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Mis Órdenes</p>
                    <p class="text-2xl font-bold">{{ $misOrdenes }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Monto Total</p>
                    <p class="text-2xl font-bold">Bs. {{ number_format($stats['monto_total'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico por Categoría -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Órdenes por Categoría</h3>
            <canvas id="categoriasChart" height="200"></canvas>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Acciones Rápidas</h3>
            <div class="space-y-3">
                <a href="{{ route('ordenes-pago.create') }}" class="block w-full text-center px-4 py-2 bg-primary-700 text-white rounded-md hover:bg-primary-700">
                    + Nueva Orden de Pago
                </a>
                <a href="{{ route('tracking.index') }}" class="block w-full text-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Ver Tracking de Trámites
                </a>
                <a href="{{ route('reportes.index') }}" class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Generar Reportes
                </a>
            </div>
        </div>
    </div>
    
    <!-- Últimas Órdenes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Últimas Órdenes de Pago</h3>
            <a href="{{ route('ordenes-pago.index') }}" class="text-primary-700 hover:text-primary-900 text-sm">Ver todas →</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">N° Orden</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Beneficiario</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Concepto</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Monto</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($ultimasOrdenes as $orden)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-sm">{{ $orden->numero_orden }}</td>
                        <td class="px-4 py-2">{{ $orden->beneficiario->nombre_completo }}</td>
                        <td class="px-4 py-2 max-w-xs truncate">{{ Str::limit($orden->concepto, 40) }}</td>
                        <td class="px-4 py-2 text-right">Bs. {{ number_format($orden->neto_pagar, 2) }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $orden->estado_color }}-100 text-{{ $orden->estado_color }}-800">
                                {{ $orden->estado_label }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('ordenes-pago.show', $orden) }}" class="text-primary-700 hover:text-primary-900">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('categoriasChart').getContext('2d');
    const categoriasData = @json($porCategoria);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categoriasData.map(item => item.categoria_gasto.nombre),
            datasets: [{
                label: 'Monto (Bs.)',
                data: categoriasData.map(item => item.monto),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection