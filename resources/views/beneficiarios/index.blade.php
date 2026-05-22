<x-app-layout>
    <x-slot name="header">Beneficiarios</x-slot>

    <div class="py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Listado de Beneficiarios</h2>
            @can('crear_beneficiario')
            <a href="{{ route('beneficiarios.create') }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Nuevo Beneficiario
            </a>
            @endcan
        </div>

        <!-- Filtros -->
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nombre, CI/NIT/N° Patronal..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none flex-1 min-w-48">
            <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">Todos los tipos</option>
                <option value="persona" {{ request('tipo')=='persona' ? 'selected' : '' }}>Persona Natural</option>
                <option value="empresa" {{ request('tipo')=='empresa' ? 'selected' : '' }}>Empresa</option>
            </select>
            <button type="submit" class="bg-primary-800 hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-search mr-1"></i>Buscar
            </button>
            @if(request()->hasAny(['search','tipo']))
            <a href="{{ route('beneficiarios.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                Limpiar
            </a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nombre / Razón Social</th>
                        <th class="px-4 py-3 text-left">CI/NIT/N° Patronal</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Teléfono</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($beneficiarios as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $b->nombre_razon_social }} {{ $b->apellidos }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $b->ci_nit ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $b->tipo === 'empresa' ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($b->tipo) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $b->telefono ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $b->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $b->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('beneficiarios.show', $b) }}" class="text-primary-700 hover:text-primary-900 text-xs px-2 py-1 rounded bg-primary-50 hover:bg-primary-100 transition-colors" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('editar_beneficiario')
                                <a href="{{ route('beneficiarios.edit', $b) }}" class="text-yellow-600 hover:text-yellow-800 text-xs px-2 py-1 rounded bg-yellow-50 hover:bg-yellow-100 transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('eliminar_beneficiario')
                                <form method="POST" action="{{ route('beneficiarios.destroy', $b) }}" onsubmit="return confirm('¿Eliminar este beneficiario?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 transition-colors" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-users text-4xl mb-3 block opacity-30"></i>
                            No se encontraron beneficiarios
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $beneficiarios->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
