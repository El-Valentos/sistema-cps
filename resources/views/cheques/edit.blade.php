<x-app-layout>
    <x-slot name="header">Editar Cheque</x-slot>
    <div class="py-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-edit mr-2 text-primary-800"></i>Editar Cheque N° {{ $cheque->numero_cheque }}
                </h2>
                
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('cheques.update', $cheque) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-sm text-gray-600 mb-3">
                            <span class="font-bold">Orden de Pago:</span> {{ $cheque->ordenPago->numero_orden }}
                        </div>
                        <div class="text-sm text-gray-600 mb-3">
                            <span class="font-bold">Beneficiario:</span> {{ $cheque->ordenPago->beneficiario_nombre }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-bold">Monto:</span> Bs. {{ number_format($cheque->monto, 2) }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banco <span class="text-red-500">*</span></label>
                        <input type="text" name="banco" value="{{ old('banco', $cheque->banco) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                        <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta', $cheque->numero_cuenta) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $cheque->fecha_emision?->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                        <a href="{{ route('cheques.show', $cheque) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>