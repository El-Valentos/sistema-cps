@php
    $user = auth()->user();
    $userRole = $user->roles->first()?->name ?? 'Sin Rol';
    $isSuperAdmin = $user->hasRole('Super Admin');
    $isAdministracion = $user->hasRole('Administración');
    
    $menuItems = [
        ['name' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'route' => 'dashboard', 'roles' => []],
    ];

    if ($isSuperAdmin || $user->hasRole('Tesorería')) {
        $menuItems[] = ['name' => 'Órdenes de Pago', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'route' => 'ordenes-pago.index', 'roles' => []];
    }

    if ($isSuperAdmin || $user->hasRole('Financiera')) {
        $menuItems[] = ['name' => 'Financiera', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'financiera.index', 'roles' => []];
    }

    if ($isSuperAdmin || $user->hasRole('Contabilidad')) {
        $menuItems[] = ['name' => 'Contabilidad', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'contabilidad.index', 'roles' => []];
    }

    $menuItems[] = ['name' => 'Revisión Cheque', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'url' => '/contabilidad/revision-cheques', 'roles' => []];
    $menuItems[] = ['name' => 'Archivos', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', 'url' => '/archivos', 'roles' => []];

    if ($isSuperAdmin || $user->hasRole('Presupuesto')) {
        $menuItems[] = ['name' => 'Presupuesto', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'presupuesto.index', 'roles' => []];
    }

    if ($isSuperAdmin || $user->hasRole('Administración')) {
        $menuItems[] = ['name' => 'Administración', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'administracion.index', 'roles' => []];
    }

    if ($isSuperAdmin || $user->hasRole('Caja')) {
        $menuItems[] = ['name' => 'Caja', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'route' => 'caja.index', 'roles' => []];
    }

    $menuItems[] = ['name' => 'Cheques', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'route' => 'cheques.index', 'roles' => []];
    $menuItems[] = ['name' => 'Tracking', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'tracking.index', 'roles' => []];
    $menuItems[] = ['name' => 'Reportes', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'route' => 'reportes.index', 'roles' => []];

    if ($isSuperAdmin || $user->hasRole('Tesorería')) {
        $menuItems[] = ['name' => 'Beneficiarios', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'route' => 'beneficiarios.index', 'roles' => []];
    }
    
    if ($isSuperAdmin) {
        $menuItems[] = ['name' => 'Usuarios', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'route' => 'users.index', 'roles' => []];
        $menuItems[] = ['name' => 'Áreas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-1 5h1m-1 5h1m2-5v-5a1 1 0 00-1-1h-4a1 1 0 00-1 1v5m-4 0h8', 'route' => 'areas.index', 'roles' => []];
    }
@endphp

<div class="flex items-center justify-center mt-8">
    <div class="flex items-center">
        <img src="{{ asset('images/cps-logo.png') }}" alt="CPS Logo" class="w-12 h-12">
        <div class="ml-2">
            <span class="text-lg font-bold text-white">CPS</span>
            <span class="block text-xs text-gray-300">{{ config('app.ciudad') }}</span>
        </div>
    </div>
</div>

<nav class="mt-10">
    @foreach($menuItems as $item)
        <a href="{{ isset($item['url']) ? $item['url'] : route($item['route']) }}" 
           target="{{ isset($item['url']) ? '_blank' : '' }}"
           class="flex items-center px-6 py-3 mt-2 text-gray-100 transition-colors duration-200 hover:bg-primary-700 {{ isset($item['url']) ? '' : (request()->routeIs($item['route'] . '*') ? 'bg-primary-950 border-l-4 border-yellow-400' : '') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
            </svg>
            <span class="mx-3">{{ $item['name'] }}</span>
            @if($item['name'] === 'Órdenes de Pago' && ($sidebarBadges['pending_tesoreria'] ?? 0) > 0)
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">{{ $sidebarBadges['pending_tesoreria'] }}</span>
            @endif
            @if($item['name'] === 'Financiera' && ($sidebarBadges['pending_financiera'] ?? 0) > 0)
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">{{ $sidebarBadges['pending_financiera'] }}</span>
            @endif
            @if($item['name'] === 'Cheques Entregados' && ($sidebarBadges['pending_contabilidad_audit'] ?? 0) > 0)
                <span class="ml-auto bg-blue-500 text-white text-xs rounded-full px-2 py-1">{{ $sidebarBadges['pending_contabilidad_audit'] }}</span>
            @endif
            @if($item['name'] === 'Archivo Definitivo' && ($sidebarBadges['pending_archivos'] ?? 0) > 0)
                <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full px-2 py-1">{{ $sidebarBadges['pending_archivos'] }}</span>
            @endif
            @if($item['name'] === 'Revisión Cheque')
                <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">!</span>
            @endif
        </a>
    @endforeach
</nav>

<div class="absolute bottom-0 w-64 mb-6">
    <div class="px-6 py-4 border-t border-primary-800">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-primary-700 flex items-center justify-center">
                    <span class="text-white text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-300">{{ $userRole }}</p>
            </div>
        </div>
    </div>
</div>