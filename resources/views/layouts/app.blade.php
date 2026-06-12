<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('assets/iconcaja.jpeg') }}">
    <title>{{ config('app.name') }} - @yield('title', 'Sistema')</title>
    @vite('resources/css/app.css')
    <style>
        [x-cloak] { display: none !important; }
        .sidebar { width: 16rem; height: 100vh; }
        @media (max-width: 768px) { .sidebar { display: none; } }
        
        /* Estilo del scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="sidebar bg-primary-800 text-white flex flex-col fixed top-0 left-0 h-screen z-30">
        <!-- Logo -->
        <div class="px-6 py-5 border-b border-primary-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/iconcaja.jpeg') }}" alt="Logo" class="w-10 h-10 rounded-full object-cover">
                <div>
                    <p class="font-bold text-white text-sm leading-tight">CPS</p>
                    <p class="text-primary-300 text-xs">{{ config('app.ciudad') }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto sidebar-nav">
            @auth
            @php $role = auth()->user()->roles->first()->name ?? ''; @endphp

            @can('ver_dashboard')
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-tachometer-alt w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            @endcan

            @can('ver_ordenes_pago')
            @unless(auth()->user()->hasRole('Financiera'))
            <a href="{{ route('ordenes-pago.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('ordenes-pago.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-file-invoice-dollar w-5 text-center"></i>
                <span>Órdenes de Pago</span>
                @php $pendientes = \App\Models\OrdenPago::where('estado','pendiente_tesoreria')->count(); @endphp
                @if($pendientes > 0)
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $pendientes }}</span>
                @endif
            </a>
            @endunless
            @endcan

            @can('ver_cheques')
            @unless(auth()->user()->hasRole('Caja') || auth()->user()->hasRole('Tesorería') || auth()->user()->hasRole('Financiera') || auth()->user()->hasRole('Presupuesto'))
            <a href="{{ route('cheques.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('cheques.index') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-money-check w-5 text-center"></i>
                <span>Cheques</span>
            </a>
            <a href="{{ route('cheques.buscar') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('cheques.buscar') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-search w-5 text-center"></i>
                <span>Buscar Cheque</span>
            </a>
            @endunless
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Contabilidad'))
            <a href="/contabilidad/revision-cheques" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all text-primary-200 hover:bg-primary-900 hover:text-white">
                <i class="fas fa-search-dollar w-5 text-center"></i>
                <span>Revisión Cheque</span>
            </a>
            @endif
            @endcan
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Archivos'))
            <a href="/archivos" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all text-primary-200 hover:bg-primary-900 hover:text-white">
                <i class="fas fa-folder w-5 text-center"></i>
                <span>Archivos</span>
            </a>
            @endif

            @if(auth()->user()->hasRole('Caja') || auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('caja.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('caja.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-cash-register w-5 text-center"></i>
                <span>Caja (Entregas)</span>
            </a>
            @endif

            @can('ver_tracking')
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('tracking.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-route w-5 text-center"></i>
                <span>Tracking</span>
            </a>
            @endcan

            @can('ver_beneficiarios')
            <a href="{{ route('beneficiarios.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('beneficiarios.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-users w-5 text-center"></i>
                <span>Beneficiarios</span>
            </a>
            @endcan

            @if(auth()->user()->hasRole('Presupuesto') || auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('presupuesto.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('presupuesto.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-calculator w-5 text-center"></i>
                <span>Presupuesto</span>
            </a>
            @endif

            @if(auth()->user()->hasRole('Financiera') || auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('financiera.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('financiera.index') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-file-invoice-dollar w-5 text-center"></i>
                <span>Órdenes de Pago</span>
            </a>
            <a href="{{ route('financiera.cheques') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('financiera.cheques') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-money-check w-5 text-center"></i>
                <span>Revisión Financiera</span>
            </a>
            @endif

            @if(auth()->user()->hasRole('Administración') || auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('administracion.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('administracion.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-building w-5 text-center"></i>
                <span>Administración</span>
            </a>
            @endif

            @can('ver_reportes')
            <a href="{{ route('reportes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('reportes.index') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-chart-bar w-5 text-center"></i>
                <span>Generador de Reportes</span>
            </a>
            <a href="{{ route('reportes.consolidado') }}" class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('reportes.consolidado*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-layer-group w-4 text-center"></i>
                <span>Reportes por Área</span>
            </a>
            @endcan

            @if(auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-user-cog w-5 text-center"></i>
                <span>Gestión de Usuarios</span>
            </a>
            <a href="{{ route('areas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-all {{ request()->routeIs('areas.*') ? 'bg-primary-800 text-white' : 'text-primary-200 hover:bg-primary-900 hover:text-white' }}">
                <i class="fas fa-sitemap w-5 text-center"></i>
                <span>Gestión de Áreas</span>
            </a>
            @endif
            @endauth
        </nav>

        <!-- User info + logout -->
        @auth
        <div class="px-4 py-4 border-t border-primary-800 flex-shrink-0">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-primary-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div class="overflow-hidden">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-primary-300 text-xs">{{ auth()->user()->roles->first()->name ?? 'Usuario' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left flex items-center gap-2 text-primary-300 hover:text-white text-xs px-2 py-1.5 rounded hover:bg-primary-900 transition-colors">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-1 ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm sticky top-0 z-20">
            <div class="flex items-center justify-between px-6 py-3">
                <div>
                    @isset($header)
                        <h1 class="text-lg font-semibold text-gray-800">{{ $header }}</h1>
                    @endisset
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span><i class="fas fa-calendar-alt mr-1"></i>{{ now()->format('d/m/Y') }}</span>
                    @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 hover:text-primary-800 transition-colors">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ session('warning') }}
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="px-6 pb-8">
            {{ $slot }}
        </main>
    </div>
</div>

@vite('resources/js/app.js')
@if(session('download_report'))
<script>document.addEventListener('DOMContentLoaded',function(){var a=document.createElement('a');a.href='{{ route("reportes.descargar", session("download_report")) }}';a.download='';document.body.appendChild(a);a.click();a.remove();})</script>
@endif
@stack('scripts')
</body>
</html>
