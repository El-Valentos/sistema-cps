@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Administración</h1>
    
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
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Pagado</p>
                    <p class="text-2xl font-bold">Bs. {{ number_format($stats['monto_pagado'], 2) }}</p>
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
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Cheques</p>
                    <p class="text-2xl font-bold">{{ $stats['total_cheques'] }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Órdenes por Mes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Órdenes por Mes</h3>
            <canvas id="ordenesPorMesChart" height="200"></canvas>
        </div>
        
        <!-- Distribución por Estado -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Distribución por Estado</h3>
            <canvas id="estadosChart" height="200"></canvas>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Usuarios por Rol -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Usuarios por Rol</h3>
            <canvas id="rolesChart" height="200"></canvas>
        </div>
        
        <!-- Top Beneficiarios -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Top 5 Beneficiarios</h3>
            <div class="space-y-3">
                @foreach($topBeneficiarios as $beneficiario)
                <div class="flex justify-between items-center border-b pb-2">
                    <div>
                        <p class="font-medium">{{ $beneficiario->beneficiario->nombre_completo }}</p>
                        <p class="text-xs text-gray-500">{{ $beneficiario->beneficiario_ci_nit }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">Bs. {{ number_format($beneficiario->total, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ $beneficiario->total_ordenes ?? 0 }} órdenes</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de órdenes por mes
    const ordenesData = @json($ordenesPorMes);
    new Chart(document.getElementById('ordenesPorMesChart'), {
        type: 'line',
        data: {
            labels: ordenesData.map(item => `${item.month}/${item.year}`),
            datasets: [{
                label: 'Órdenes',
                data: ordenesData.map(item => item.total),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3
            }, {
                label: 'Monto (Bs.)',
                data: ordenesData.map(item => item.monto),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Órdenes' } },
                y1: { position: 'right', beginAtZero: true, title: { display: true, text: 'Monto (Bs.)' } }
            }
        }
    });
    
    // Gráfico de estados
    const estadosData = @json($estadosDistribucion);
    const estadosLabels = {
        'pendiente_tesoreria': 'Pendiente Tesorería',
        'enviado_contabilidad': 'Enviado Contabilidad',
        'cheque_generado': 'Cheque Generado',
        'en_caja': 'En Caja',
        'entregado': 'Entregado',
        'cerrado': 'Cerrado'
    };
    new Chart(document.getElementById('estadosChart'), {
        type: 'doughnut',
        data: {
            labels: estadosData.map(item => estadosLabels[item.estado] || item.estado),
            datasets: [{
                data: estadosData.map(item => item.total),
                backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#6B7280']
            }]
        },
        options: { responsive: true }
    });
    
    // Gráfico de roles
    const rolesData = @json($usuariosPorRol);
    new Chart(document.getElementById('rolesChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(rolesData),
            datasets: [{
                label: 'Usuarios',
                data: Object.values(rolesData),
                backgroundColor: 'rgba(139, 92, 246, 0.5)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
</script>
@endpush
@endsection