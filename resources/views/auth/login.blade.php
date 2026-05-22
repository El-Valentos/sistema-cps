<x-guest-layout>
    <h2 class="text-xl font-bold text-gray-800 text-center mb-6">Iniciar Sesión</h2>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('warning') }}
        </div>
        @endif

        @if(session('status'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-sm @error('email') border-red-500 @enderror">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input id="password" type="password" name="password" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none text-sm">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary-700">
                Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-primary-700 hover:underline">¿Olvidó su contraseña?</a>
        </div>

        <button type="submit" class="w-full bg-primary-900 hover:bg-primary-950 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors">
            <i class="fas fa-sign-in-alt mr-2"></i>Ingresar al Sistema
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">
        Sistema Integral de Pago CPS &copy; {{ date('Y') }}
    </p>
</x-guest-layout>
