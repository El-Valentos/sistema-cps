<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="py-6">
        @php
            $role = auth()->user()->roles->first()->name ?? 'Usuario';
        @endphp

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Bienvenido, {{ auth()->user()->name }}</h2>
            <p class="text-gray-500 text-sm mt-1">Rol: <span class="font-medium text-primary-800">{{ $role }}</span></p>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            @php
                $rol = $role;
                if ($rol === 'Super Admin') {
                    $statsOrdenes = \App\Models\OrdenPago::count();
                    $statsPendientes = \App\Models\OrdenPago::where('estado','pendiente_tesoreria')->count();
                    $statsCheques = \App\Models\Cheque::count();
                } elseif ($rol === 'Tesorería') {
                    $statsOrdenes = \App\Models\OrdenPago::where('estado','pendiente_tesoreria')->count();
                    $statsPendientes = \App\Models\OrdenPago::where('estado','rechazado_financiera')->count();
                    $statsCheques = \App\Models\OrdenPago::whereIn('estado',['pendiente_tesoreria','rechazado_financiera'])->count();
                } elseif ($rol === 'Contabilidad') {
                    $statsOrdenes = \App\Models\OrdenPago::where('estado','enviado_contabilidad')->count();
                    $statsPendientes = \App\Models\Cheque::count();
                    $statsCheques = \App\Models\Cheque::where('estado','emitido')->count();
                } elseif ($rol === 'Financiera') {
                    $statsOrdenes = \App\Models\OrdenPago::whereIn('estado',['enviado_financiera','enviado_financiera_cheque'])->count();
                    $statsPendientes = \App\Models\OrdenPago::where('estado','enviado_financiera')->count();
                    $statsCheques = \App\Models\OrdenPago::where('estado','enviado_financiera_cheque')->count();
                } elseif ($rol === 'Presupuesto') {
                    $statsOrdenes = \App\Models\OrdenPago::where('estado','enviado_presupuesto')->count();
                    $statsPendientes = 0;
                    $statsCheques = $statsOrdenes;
                } elseif ($rol === 'Administración') {
                    $statsOrdenes = \App\Models\OrdenPago::where('estado','enviado_administracion')->count();
                    $statsPendientes = 0;
                    $statsCheques = $statsOrdenes;
                } elseif ($rol === 'Caja') {
                    $statsOrdenes = \App\Models\OrdenPago::whereIn('estado',['en_caja','entregado'])->count();
                    $statsPendientes = \App\Models\OrdenPago::where('estado','en_caja')->count();
                    $statsCheques = 0;
                } elseif ($rol === 'Archivos') {
                    $statsOrdenes = \App\Models\OrdenPago::whereIn('estado',['enviado_archivos','archivado'])->count();
                    $statsPendientes = \App\Models\OrdenPago::where('estado','enviado_archivos')->count();
                    $statsCheques = 0;
                } else {
                    $statsOrdenes = 0;
                    $statsPendientes = 0;
                    $statsCheques = 0;
                }
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-primary-500">
                <p class="text-xs text-gray-500 uppercase font-medium">Órdenes</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($statsOrdenes) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
                <p class="text-xs text-gray-500 uppercase font-medium">Pendientes</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ number_format($statsPendientes) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
                <p class="text-xs text-gray-500 uppercase font-medium">Cheques</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($statsCheques) }}</p>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @can('crear_orden_pago')
            <a href="{{ route('ordenes-pago.create') }}" class="bg-primary-900 hover:bg-primary-950 text-white rounded-xl p-5 flex items-center gap-4 transition-colors group shadow">
                <div class="w-12 h-12 bg-primary-800 rounded-lg flex items-center justify-center group-hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus-circle text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Nueva Orden de Pago</p>
                    <p class="text-primary-300 text-xs mt-0.5">Crear solicitud</p>
                </div>
            </a>
            @endcan

            @can('ver_ordenes_pago')
            <a href="{{ route('ordenes-pago.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list-alt text-primary-700 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Ver Órdenes</p>
                    <p class="text-gray-400 text-xs mt-0.5">Listado completo</p>
                </div>
            </a>
            @endcan

            @can('generar_cheque')
            <a href="{{ route('cheques.create') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-check text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Generar Cheque</p>
                    <p class="text-gray-400 text-xs mt-0.5">Emitir pago</p>
                </div>
            </a>
            @endcan

            @can('ver_tracking')
            <a href="{{ route('tracking.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-route text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Tracking</p>
                    <p class="text-gray-400 text-xs mt-0.5">Seguimiento de órdenes</p>
                </div>
            </a>
            @endcan

            @can('ver_reportes')
            <a href="{{ route('reportes.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Reportes</p>
                    <p class="text-gray-400 text-xs mt-0.5">Estadísticas y exportación</p>
                </div>
            </a>
            @endcan

            @can('ver_beneficiarios')
            @unless(auth()->user()->hasRole('Financiera'))
            <a href="{{ route('beneficiarios.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Beneficiarios</p>
                    <p class="text-gray-400 text-xs mt-0.5">Gestión de beneficiarios</p>
                </div>
            </a>
            @endunless
            @endcan

            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Contabilidad'))
            <a href="{{ route('contabilidad.revision_cheques') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-search-dollar text-red-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Revisión Cheque</p>
                    <p class="text-gray-400 text-xs mt-0.5">Revisión de cheques</p>
                </div>
            </a>
            @endif

            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Archivos'))
            <a href="{{ route('archivos.index') }}" class="bg-white hover:bg-gray-50 text-gray-800 rounded-xl p-5 flex items-center gap-4 transition-colors shadow border border-gray-200">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-folder text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm">Archivos</p>
                    <p class="text-gray-400 text-xs mt-0.5">Gestión de archivos</p>
                </div>
            </a>
            @endif
        </div>
    </div>
</x-app-layout>
