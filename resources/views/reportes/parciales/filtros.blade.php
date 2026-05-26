<div class="bg-gray-50 p-4 rounded-lg mb-4">
    <h4 class="font-medium text-gray-800 mb-3">Filtros aplicados</h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        <div><span class="text-gray-500">Período:</span> {{ $filtros['fecha_desde'] }} al {{ $filtros['fecha_hasta'] }}</div>
        @if(isset($filtros['estado']) && $filtros['estado'])
        <div><span class="text-gray-500">Estado:</span> {{ $filtros['estado'] }}</div>
        @endif
        @if(isset($filtros['beneficiario_id']) && $filtros['beneficiario_id'])
        <div><span class="text-gray-500">Beneficiario:</span> {{ $filtros['beneficiario_nombre'] ?? 'Seleccionado' }}</div>
        @endif
        @if(isset($filtros['categoria_id']) && $filtros['categoria_id'])
        <div><span class="text-gray-500">Categoría:</span> {{ $filtros['categoria_nombre'] ?? 'Seleccionada' }}</div>
        @endif
    </div>
</div>