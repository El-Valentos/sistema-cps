<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app-5a2e9a38.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-400 mb-3">
                <span class="text-green-900 font-bold text-2xl">C</span>
            </div>
            <h1 class="text-white text-2xl font-bold">CPS</h1>
            <p class="text-primary-300 text-sm">Caja Petrolera de Salud</p>
            <p class="text-primary-200 text-xs mt-1">Sistema Integral de Pagos</p>
        </div>
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
