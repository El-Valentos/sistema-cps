<x-app-layout>
    <x-slot name="header">Gestión de Áreas</x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Áreas y Departamentos</h2>
            <a href="{{ route('areas.create') }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Nueva Área
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Orden</th>
                            <th class="px-6 py-4">Código</th>
                            <th class="px-6 py-4">Nombre / Descripción</th>
                            <th class="px-6 py-4 text-center">Usuarios</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($areas as $area)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-xs font-bold">
                                    {{ $area->orden_flujo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $area->codigo }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $area->nombre }}</p>
                                @if($area->descripcion)
                                <p class="text-xs text-gray-500 line-clamp-1" title="{{ $area->descripcion }}">{{ $area->descripcion }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 bg-primary-50 text-primary-800 rounded-lg text-xs font-medium">
                                    {{ $area->usuarios_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($area->activo)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Activa</span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('areas.edit', $area) }}" class="text-primary-700 hover:text-primary-900 transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-sitemap text-4xl mb-3 opacity-30"></i>
                                <p>No hay áreas registradas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
