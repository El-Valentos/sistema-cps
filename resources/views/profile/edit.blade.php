<x-app-layout>
    <x-slot name="header">Mi Perfil</x-slot>
    <div class="py-6 max-w-2xl space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Información Personal</h3>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @if(session('status') === 'profile-updated')
                <p class="text-green-600 text-sm">✅ Perfil actualizado correctamente.</p>
                @endif
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    Guardar cambios
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Cambiar Contraseña</h3>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                    <input type="password" name="current_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    @error('current_password','updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    @error('password','updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                @if(session('status') === 'password-updated')
                <p class="text-green-600 text-sm">✅ Contraseña actualizada.</p>
                @endif
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    Actualizar Contraseña
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
