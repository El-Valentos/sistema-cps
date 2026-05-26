<x-app-layout>
    <x-slot name="header">Beneficiario</x-slot>
    <div class="py-6 max-w-4xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('beneficiarios.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">{{ $beneficiario->nombre_razon_social }} {{ $beneficiario->apellidos }}</h2>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $beneficiario->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $beneficiario->activo ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
                <h3 class="font-semibold text-gray-700 border-b pb-2">Información Personal</h3>
                <div class="text-sm"><span class="text-gray-500">Tipo:</span> <span class="font-medium">{{ ucfirst($beneficiario->tipo) }}</span></div>
                <div class="text-sm"><span class="text-gray-500">CI/NIT/N° Patronal:</span> <span class="font-medium">{{ $beneficiario->ci_nit ?? '-' }}</span></div>
                <div class="text-sm"><span class="text-gray-500">Teléfono:</span> <span class="font-medium">{{ $beneficiario->telefono ?? '-' }}</span></div>
                <div class="text-sm"><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $beneficiario->email ?? '-' }}</span></div>
                <div class="text-sm"><span class="text-gray-500">Dirección:</span> <span class="font-medium">{{ $beneficiario->direccion ?? '-' }}</span></div>
            </div>
        </div>
        @if($beneficiario->ordenesPago->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Últimas Órdenes de Pago</h3>
            <table class="w-full text-sm">
                <thead class="text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left pb-2">N° Orden</th>
                        <th class="text-left pb-2">Fecha</th>
                        <th class="text-left pb-2">Concepto</th>
                        <th class="text-right pb-2">Monto</th>
                        <th class="text-center pb-2">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($beneficiario->ordenesPago as $op)
                    <tr>
                        <td class="py-2">{{ $op->numero_orden }}</td>
                        <td class="py-2">{{ $op->fecha_orden?->format('d/m/Y') }}</td>
                        <td class="py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($op->concepto, 40) }}</td>
                        <td class="py-2 text-right font-medium">Bs. {{ number_format($op->monto_total, 2) }}</td>
                        <td class="py-2 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-primary-100 text-primary-800">{{ $op->estado_label }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <div class="flex gap-3 mt-6">
            @can('editar_beneficiario')
            <a href="{{ route('beneficiarios.edit', $beneficiario) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            @endcan
            <a href="{{ route('beneficiarios.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Volver</a>
        </div>
    </div>
</x-app-layout>
