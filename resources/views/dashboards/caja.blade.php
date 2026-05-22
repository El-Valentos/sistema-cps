@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard - Caja</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Por Entregar</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $porEntregar }}</p>
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
                    <p class="text-sm text-gray-500">Entregas Hoy</p>
                    <p class="text-2xl font-bold">{{ $entregasHoy }}</p>
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
                    <p class="text-sm text-gray-500">Monto por Entregar</p>
                    <p class="text-2xl font-bold">Bs. {{ number_format($montoPorEntregar, 2) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Cheques Listos</p>
                    <p class="text-2xl font-bold">{{ $chequesListos->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cheques Listos para Entregar -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Cheques Listos para Entrega</h3>
            @if($chequesListos->count() > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($chequesListos as $cheque)
                    <div class="border rounded-lg p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono font-bold">{{ $cheque->numero_cheque }}</p>
                                <p class="text-sm">{{ $cheque->ordenPago->beneficiario->nombre_completo }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-green-600">Bs. {{ number_format($cheque->monto, 2) }}</p>
                                <button onclick="abrirModalEntrega({{ $cheque->ordenPago->id }})" class="mt-2 px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                    Registrar Entrega
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No hay cheques listos para entregar</p>
            @endif
        </div>
        
        <!-- Entregas Recientes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Entregas Recientes</h3>
            @if($entregasRecientes->count() > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($entregasRecientes as $entrega)
                    <div class="border rounded-lg p-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono text-sm">{{ $entrega->numero_orden }}</p>
                                <p class="font-medium">{{ $entrega->beneficiario->nombre_completo }}</p>
                                <p class="text-xs text-gray-500">Cheque N° {{ $entrega->cheque->numero_cheque ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">Bs. {{ number_format($entrega->neto_pagar, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ $entrega->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No hay entregas recientes</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal Entrega -->
<div id="modalEntrega" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Registrar Entrega de Cheque</h3>
            <form id="formEntrega" method="POST" class="mt-4">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Recibido por *</label>
                        <input type="text" name="recibido_por" required class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CI / NIT *</label>
                        <input type="text" name="ci_recibido" required class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Entrega *</label>
                        <input type="date" name="fecha_entrega" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="w-full px-3 py-2 border rounded-md"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="cerrarModalEntrega()" class="px-4 py-2 bg-gray-300 rounded-md">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md">Registrar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalEntrega(ordenId) {
        const modal = document.getElementById('modalEntrega');
        const form = document.getElementById('formEntrega');
        form.action = `/tracking/${ordenId}/entregar`;
        modal.classList.remove('hidden');
    }
    
    function cerrarModalEntrega() {
        const modal = document.getElementById('modalEntrega');
        modal.classList.add('hidden');
    }
</script>
@endpush
@endsection