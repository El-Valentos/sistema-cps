<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Usuarios del Sistema</h2>
            <a href="{{ route('users.create') }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Nuevo Usuario
            </a>
        </div>

        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex gap-3">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, email o teléfono..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none flex-1">
            <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-search mr-1"></i>Buscar
            </button>
            @if(request('buscar'))
            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-times mr-1"></i>Limpiar
            </a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Usuario</th>
                            <th class="px-6 py-4">Rol / Permisos</th>
                            <th class="px-6 py-4">Área</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-900 font-bold text-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                    {{ $user->roles->first()->name ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->area->nombre ?? 'Sin área' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->activo)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Activo</span>
                                @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded text-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->activo)
                                    <form action="{{ route('users.toggleActivo', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-800 px-2 py-1 rounded text-sm" title="Desactivar" onclick="return confirm('¿Desactivar usuario?')">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('users.toggleActivo', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded text-sm" title="Activar" onclick="return confirm('¿Activar usuario?')">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if(!$user->hasRole('Super Admin'))
                                    <form action="{{ route('users.asignarSuperAdmin', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-2 py-1 rounded text-sm" title="Hacer Super Admin" onclick="return confirm('¿Asignar rol Super Admin?')">
                                            <i class="fas fa-crown"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('users.quitarSuperAdmin', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm" title="Quitar Super Admin" onclick="return confirm('¿Quitar rol Super Admin?')">
                                            <i class="fas fa-crown"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-users text-4xl mb-3 opacity-30"></i>
                                <p>No hay usuarios registrados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
