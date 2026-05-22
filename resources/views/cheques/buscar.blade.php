<x-app-layout>
    <x-slot name="header">Buscar Cheque</x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Buscar Cheque</h2>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <form method="POST" action="{{ route('cheques.buscarPost') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cheque</label>
                        <input type="text" name="numero_cheque" value="{{ old('numero_cheque') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Beneficiario</label>
                        <input type="text" name="nombre_beneficiario" value="{{ old('nombre_beneficiario') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CI/NIT/N° Patronal</label>
                        <input type="text" name="ci_nit" value="{{ old('ci_nit') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto Desde</label>
                        <input type="number" name="monto_desde" value="{{ old('monto_desde') }}" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto Hasta</label>
                        <input type="number" name="monto_hasta" value="{{ old('monto_hasta') }}" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                    <a href="{{ route('cheques.buscar') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">Limpiar</a>
                </div>
            </form>
        </div>

        @if(isset($cheques))
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">N° Cheque</th>
                        <th class="px-4 py-3">Beneficiario</th>
                        <th class="px-4 py-3">CI/NIT/N° Patronal</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cheques as $cheque)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $cheque->numero_cheque }}</td>
                        <td class="px-4 py-3">{{ $cheque->ordenPago->beneficiario_nombre ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $cheque->ordenPago->beneficiario_ci_nit ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">Bs. {{ number_format($cheque->monto, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $cheque->estado === 'emitido' ? 'bg-yellow-100 text-yellow-700' : 
                                   ($cheque->estado === 'impreso' ? 'bg-primary-100 text-primary-800' : 
                                   ($cheque->estado === 'anulado' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ ucfirst($cheque->estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $cheque->fecha_emision ? \Carbon\Carbon::parse($cheque->fecha_emision)->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('cheques.show', $cheque) }}" class="text-primary-700 hover:text-primary-900 text-sm mr-2">Ver</a>
                            @if($cheque->ordenPago)
                            <a href="{{ route('tracking.show', $cheque->ordenPago) }}" class="text-green-600 hover:text-green-800 text-sm">Tracking</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No se encontraron cheques</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-app-layout>