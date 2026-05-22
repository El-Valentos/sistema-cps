<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Consulta de Cheques</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/iconcaja.jpeg') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/app-5a2e9a38.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="w-full max-w-2xl">

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
                        <p class="text-gray-500 text-sm mt-1">Ingrese el número de cheque para conocer su estado</p>
                    </div>

                    <!-- Search Form -->
                    <form method="POST" action="{{ route('consulta-cheque.buscar') }}" class="mb-6">
                        @csrf
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <input type="text" name="numero_cheque" id="numero_cheque"
                                    class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-green-500 focus:ring-green-500"
                                    placeholder="Ej: CH-2026-00001"
                                    value="{{ old('numero_cheque', $cheque->numero_cheque ?? '') }}"
                                    required autofocus>
                                @error('numero_cheque')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <i class="fas fa-search"></i> Consultar
                            </button>
                        </div>
                    </form>

                    <!-- Results -->
                    @isset($cheque)
                    <hr class="border-gray-200 mb-6">

                    <div class="space-y-4">

                        <!-- Status Badge -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800">Resultado de la Consulta</h2>
                            @php
                                $badge = match($estadoCliente['color']) {
                                    'green' => 'bg-green-100 text-green-700',
                                    'red' => 'bg-red-100 text-red-700',
                                    'yellow' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                                $icon = match($estadoCliente['key']) {
                                    'aprobado' => 'fa-check-circle',
                                    'rechazado' => 'fa-times-circle',
                                    default => 'fa-clock',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-semibold {{ $badge }}">
                                <i class="fas {{ $icon }}"></i>
                                {{ $estadoCliente['label'] }}
                            </span>
                        </div>

                        <!-- Cheque Info -->
                        <div class="bg-gray-50 rounded-xl p-5 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">N° de Cheque</p>
                                <p class="font-semibold text-gray-800">{{ $cheque->numero_cheque }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Beneficiario</p>
                                <p class="font-semibold text-gray-800">{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Monto</p>
                                <p class="font-semibold text-green-700 text-lg">Bs. {{ number_format($cheque->monto, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Fecha de Emisión</p>
                                <p class="font-semibold text-gray-800">{{ $cheque->fecha_emision?->format('d/m/Y') ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Banco</p>
                                <p class="font-semibold text-gray-800">{{ $cheque->banco ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">N° de Cuenta</p>
                                <p class="font-semibold text-gray-800">{{ $cheque->numero_cuenta ?? '-' }}</p>
                            </div>
                        </div>

                        @if($cheque->ordenPago?->concepto)
                        <div class="bg-gray-50 rounded-xl p-4 text-sm">
                            <p class="text-gray-500 mb-1">Concepto</p>
                            <p class="text-gray-800">{{ $cheque->ordenPago->concepto }}</p>
                        </div>
                        @endif

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

</body>
</html>
