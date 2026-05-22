<x-guest-layout>
    <h2 class="text-xl font-bold text-gray-800 text-center mb-2">Recuperar Contraseña</h2>
    <p class="text-sm text-gray-500 text-center mb-6">Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
    @if(session('status'))
    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 text-sm">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none text-sm">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full bg-primary-900 hover:bg-primary-950 text-white font-semibold py-2.5 rounded-lg transition-colors">
            Enviar enlace de recuperación
        </button>
    </form>
    <p class="text-center text-sm text-gray-500 mt-4">
        <a href="{{ route('login') }}" class="text-primary-700 hover:underline">← Volver al inicio de sesión</a>
    </p>
</x-guest-layout>
