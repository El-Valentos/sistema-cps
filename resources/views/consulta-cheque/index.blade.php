<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Consulta de Cheques</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/iconcaja.jpeg') }}">
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 min-h-screen">

    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="py-4 px-6">
            <div class="max-w-4xl mx-auto flex items-center gap-3">
                <img src="{{ asset('assets/iconcaja.jpeg') }}" alt="CPS" class="w-10 h-10 rounded-full object-cover">
                <div>
                    <p class="font-bold text-white text-sm leading-tight">CPS — Caja Petrolera de Salud</p>
                    <p class="text-primary-300 text-xs">Sistema Integral de Pagos</p>
                </div>
            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-4xl">

                <!-- Flash Messages -->
                @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
                @endif

                <!-- Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8">

                    <!-- Title -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-100 mb-3">
                            <i class="fas fa-money-check text-green-700 text-xl"></i>
                        </div>
                        <h1 class="text-xl font-bold text-gray-800">Consulta de Cheques</h1>
                        <p class="text-gray-500 text-sm mt-1">Ingrese nombre, CI o NIT del beneficiario</p>
                    </div>

                    <!-- Search Form -->
                    <form method="POST" action="{{ route('consulta-cheque.buscar') }}" class="mb-6">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Campo de búsqueda -->
                            <div class="flex-1">
                                <input type="text" name="valor_busqueda" id="valor_busqueda"
                                    class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ingrese nombre, CI o NIT del beneficiario..."
                                    value="{{ old('valor_busqueda') }}"
                                    required autofocus>
                                @error('valor_busqueda')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Botón consultar -->
                            <button type="submit"
                                class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 sm:w-auto">
                                <i class="fas fa-search"></i> Consultar
                            </button>
                        </div>
                    </form>

                    <!-- Results -->
                    @isset($resultados)
                    <hr class="border-gray-200 mb-6">

                    <!-- Información de búsqueda -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Resultados de la Consulta</h2>
                            <p class="text-sm text-gray-500">
                                Búsqueda: <span class="font-semibold text-gray-700">"{{ $valorBusqueda }}"</span>
                            </p>
                        </div>
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                            {{ $resultados->count() }} {{ $resultados->count() === 1 ? 'cheque encontrado' : 'cheques encontrados' }}
                        </span>
                    </div>

                    <!-- Lista de cheques -->
                    <div class="space-y-3">
                        @foreach($resultados as $resultado)
                            @php
                                $cheque = $resultado['cheque'];
                                $estadoCliente = $resultado['estadoCliente'];
                                $orden = $cheque->ordenPago;
                                
                                $badge = match($estadoCliente['color']) {
                                    'blue' => 'bg-blue-100 text-blue-700',
                                    'green' => 'bg-green-100 text-green-700',
                                    'red' => 'bg-red-100 text-red-700',
                                    'yellow' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $icon = match($estadoCliente['key']) {
                                    'listo' => 'fa-check-circle',
                                    'rechazado' => 'fa-times-circle',
                                    default => 'fa-clock',
                                };
                            @endphp
                            
                            <div class="bg-gray-50 rounded-xl px-5 py-4 border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-money-check text-green-700 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">N° de Cheque</p>
                                            <p class="font-bold text-gray-800">{{ $cheque->numero_cheque ?? 'Pendiente' }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $badge }}">
                                        <i class="fas {{ $icon }}"></i>
                                        {{ $estadoCliente['label'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500 text-xs">Beneficiario</p>
                                        <p class="font-semibold text-gray-800 truncate">
                                            {{ $orden?->beneficiario_nombre ?? '-' }} {{ $orden?->beneficiario_apellidos ?? '' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-xs">CI/NIT</p>
                                        <p class="font-semibold text-gray-800">{{ $orden?->beneficiario_ci_nit ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-xs">Fecha de Emisión</p>
                                        <p class="font-semibold text-gray-800">{{ $cheque->fecha_emision?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('consulta-cheque.index') }}" class="text-green-700 hover:text-green-800 text-sm font-medium transition-colors">
                            <i class="fas fa-redo mr-1"></i> Realizar otra consulta
                        </a>
                    </div>
                    @endisset

                </div>

                <!-- Footer -->
                <p class="text-center text-primary-200 text-xs mt-6">
                    &copy; {{ date('Y') }} CPS — Caja Petrolera de Salud. Todos los derechos reservados.
                </p>
            </div>
        </main>
    </div>

    @vite('resources/js/app.js')
</body>
</html>
