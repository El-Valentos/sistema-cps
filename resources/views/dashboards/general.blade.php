@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Cheques</p>
                    <p class="text-2xl font-bold">{{ $stats['total_cheques'] }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mis Solicitudes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Mis Últimas Solicitudes</h3>
            @if($misSolicitudes->count() > 0)
                <div class="space-y-3">
                    @foreach($misSolicitudes as $solicitud)
                    <div class="border rounded-lg p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono text-sm">{{ $solicitud->numero_orden }}</p>
                                <p class="text-sm">{{ $solicitud->beneficiario->nombre_completo }}</p>
                                <p class="text-xs text-gray-500">{{ Str::limit($solicitud->concepto, 50) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">Bs. {{ number_format($solicitud->neto_pagar, 2) }}</p>
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $solicitud->estado_color }}-100">{{ $solicitud->estado_label }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No tiene solicitudes realizadas</p>
            @endif
        </div>
        
        <!-- Tracking Reciente -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Mi Actividad Reciente</h3>
            @if($trackingReciente->count() > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($trackingReciente as $track)
                    <div class="border-l-4 border-primary-500 pl-3 py-2">
                        <p class="text-sm font-medium">{{ $track->accion_label }}</p>
                        <p class="text-xs text-gray-500">Orden: {{ $track->ordenPago->numero_orden }}</p>
                        <p class="text-xs text-gray-400">{{ $track->created_at->diffForHumans() }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No hay actividad reciente</p>
            @endif
        </div>
    </div>
</div>
@endsection