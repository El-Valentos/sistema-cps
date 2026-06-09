<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Reportes por Área</span>
            <a href="{{ route('reportes.consolidado.pdf') }}" class="bg-primary-900 hover:bg-primary-950 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF Consolidado
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex overflow-x-auto" x-data="{ tab: 'tesoreria' }">
                    <button @click="tab = 'tesoreria'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'tesoreria', 'text-gray-500 hover:text-gray-700': tab !== 'tesoreria' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-file-invoice mr-1.5"></i>Tesorería
                    </button>
                    <button @click="tab = 'financiera'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'financiera', 'text-gray-500 hover:text-gray-700': tab !== 'financiera' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-check-double mr-1.5"></i>Financiera
                    </button>
                    <button @click="tab = 'contabilidad'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'contabilidad', 'text-gray-500 hover:text-gray-700': tab !== 'contabilidad' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-calculator mr-1.5"></i>Contabilidad
                    </button>
                    <button @click="tab = 'presupuesto'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'presupuesto', 'text-gray-500 hover:text-gray-700': tab !== 'presupuesto' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-chart-pie mr-1.5"></i>Presupuesto
                    </button>
                    <button @click="tab = 'administracion'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'administracion', 'text-gray-500 hover:text-gray-700': tab !== 'administracion' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-building mr-1.5"></i>Administración
                    </button>
                    <button @click="tab = 'caja'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'caja', 'text-gray-500 hover:text-gray-700': tab !== 'caja' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-cash-register mr-1.5"></i>Caja
                    </button>
                    <button @click="tab = 'archivos'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'archivos', 'text-gray-500 hover:text-gray-700': tab !== 'archivos' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-folder mr-1.5"></i>Archivos
                    </button>
                    <button @click="tab = 'global'" :class="{ 'border-b-2 border-primary-900 text-primary-900 font-semibold': tab === 'global', 'text-gray-500 hover:text-gray-700': tab !== 'global' }" class="px-5 py-3 text-sm whitespace-nowrap transition-colors">
                        <i class="fas fa-globe mr-1.5"></i>Global
                    </button>
                </nav>
            </div>

            <div class="p-6" x-data="{ tab: 'tesoreria' }">
                {{-- TESORERÍA --}}
                <div x-show="tab === 'tesoreria'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-file-invoice text-primary-900 mr-2"></i>Tesorería</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-xs text-blue-600 uppercase font-semibold">Pendientes</p>
                            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $reportes['tesoreria']['resumen']['pendientes'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="text-xs text-red-600 uppercase font-semibold">Rechazados</p>
                            <p class="text-2xl font-bold text-red-800 mt-1">{{ $reportes['tesoreria']['resumen']['rechazados'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">En Flujo</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['tesoreria']['resumen']['en_flujo'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto del Mes</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['tesoreria']['resumen']['monto_mes'], 2) }}</p>
                        </div>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes Pendientes / Rechazadas</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                    <th class="text-left px-4 py-3">N° Orden</th>
                                    <th class="text-left px-4 py-3">Fecha</th>
                                    <th class="text-left px-4 py-3">Beneficiario</th>
                                    <th class="text-left px-4 py-3">Concepto</th>
                                    <th class="text-right px-4 py-3">Monto</th>
                                    <th class="text-center px-4 py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportes['tesoreria']['ordenes_pendientes'] as $orden)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $orden->numero_orden }}</td>
                                    <td class="px-4 py-3">{{ $orden->fecha_orden?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $orden->beneficiario_nombre }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate">{{ $orden->concepto }}</td>
                                    <td class="px-4 py-3 text-right">Bs {{ number_format($orden->monto_total, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $orden->badge_color }}">
                                            {{ $orden->estado_label }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No hay órdenes pendientes o rechazadas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- FINANCIERA --}}
                <div x-show="tab === 'financiera'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-check-double text-primary-900 mr-2"></i>Financiera</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Órdenes Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['financiera']['resumen']['ordenes_pendientes'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Órdenes Aprobadas</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['financiera']['resumen']['ordenes_aprobadas'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="text-xs text-red-600 uppercase font-semibold">Órdenes Rechazadas</p>
                            <p class="text-2xl font-bold text-red-800 mt-1">{{ $reportes['financiera']['resumen']['ordenes_rechazadas'] }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="text-xs text-purple-600 uppercase font-semibold">Cheques Pendientes</p>
                            <p class="text-2xl font-bold text-purple-800 mt-1">{{ $reportes['financiera']['resumen']['cheques_pendientes'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto Pend. Órdenes</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['financiera']['resumen']['monto_ordenes_pendientes'], 2) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes Pendientes de Aprobación</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['financiera']['ordenes_pendientes'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->monto_total, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400">Sin órdenes pendientes</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Cheques Pendientes de Aprobación</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Cheque</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['financiera']['cheques_pendientes'] as $cheque)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $cheque->numero_cheque }}</td>
                                            <td class="px-3 py-2">{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($cheque->monto, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400">Sin cheques pendientes</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTABILIDAD --}}
                <div x-show="tab === 'contabilidad'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-calculator text-primary-900 mr-2"></i>Contabilidad</h3>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Órdenes Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['contabilidad']['resumen']['ordenes_pendientes'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Cheques Emitidos (Mes)</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['contabilidad']['resumen']['cheques_emitidos'] }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="text-xs text-purple-600 uppercase font-semibold">Enviados a Presupuesto</p>
                            <p class="text-2xl font-bold text-purple-800 mt-1">{{ $reportes['contabilidad']['resumen']['cheques_enviados_presupuesto'] }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-xs text-blue-600 uppercase font-semibold">Enviados a Admin.</p>
                            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $reportes['contabilidad']['resumen']['cheques_enviados_admin'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="text-xs text-red-600 uppercase font-semibold">Anulados (Mes)</p>
                            <p class="text-2xl font-bold text-red-800 mt-1">{{ $reportes['contabilidad']['resumen']['cheques_anulados_mes'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto Emitido (Mes)</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['contabilidad']['resumen']['monto_cheques_mes'], 2) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes Pendientes de Aprobación</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['contabilidad']['ordenes_pendientes'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->monto_total, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400">Sin órdenes pendientes</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Últimos Cheques Emitidos</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Cheque</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                            <th class="text-center px-3 py-2">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['contabilidad']['ultimos_cheques'] as $cheque)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $cheque->numero_cheque }}</td>
                                            <td class="px-3 py-2">{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($cheque->monto, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ ucfirst($cheque->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin cheques emitidos</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if(($reportes['contabilidad']['resumen']['entregados_por_revisar'] ?? 0) > 0)
                    <div class="mt-4 bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                        <span class="text-sm text-orange-700 font-medium">
                            Hay <strong>{{ $reportes['contabilidad']['resumen']['entregados_por_revisar'] }}</strong> entregado(s) pendiente(s) de revisión.
                            <a href="/contabilidad/revision-cheques" class="underline hover:text-orange-800">Ir a Revisión</a>
                        </span>
                    </div>
                    @endif
                </div>

                {{-- PRESUPUESTO --}}
                <div x-show="tab === 'presupuesto'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-pie text-primary-900 mr-2"></i>Presupuesto</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Cheques Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['presupuesto']['resumen']['pendientes'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Aprobados (Mes)</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['presupuesto']['resumen']['aprobados'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="text-xs text-red-600 uppercase font-semibold">Rechazados (Mes)</p>
                            <p class="text-2xl font-bold text-red-800 mt-1">{{ $reportes['presupuesto']['resumen']['rechazados'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto Pendiente</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['presupuesto']['resumen']['monto_pendiente'], 2) }}</p>
                        </div>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Cheques Pendientes de Aprobación</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                    <th class="text-left px-4 py-3">N° Cheque</th>
                                    <th class="text-left px-4 py-3">Beneficiario</th>
                                    <th class="text-left px-4 py-3">Banco</th>
                                    <th class="text-right px-4 py-3">Monto</th>
                                    <th class="text-left px-4 py-3">Fecha Emisión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportes['presupuesto']['cheques_pendientes'] as $cheque)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $cheque->numero_cheque }}</td>
                                    <td class="px-4 py-3">{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $cheque->banco ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">Bs {{ number_format($cheque->monto, 2) }}</td>
                                    <td class="px-4 py-3">{{ $cheque->fecha_emision?->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Sin cheques pendientes de aprobación</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ADMINISTRACIÓN --}}
                <div x-show="tab === 'administracion'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-building text-primary-900 mr-2"></i>Administración</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Cheques Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['administracion']['resumen']['pendientes'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Enviados a Caja (Mes)</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['administracion']['resumen']['enviados_caja'] }}</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <p class="text-xs text-red-600 uppercase font-semibold">Rechazados (Mes)</p>
                            <p class="text-2xl font-bold text-red-800 mt-1">{{ $reportes['administracion']['resumen']['rechazados'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto Pendiente</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['administracion']['resumen']['monto_pendiente'], 2) }}</p>
                        </div>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Cheques Pendientes de Aprobación</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                    <th class="text-left px-4 py-3">N° Cheque</th>
                                    <th class="text-left px-4 py-3">Beneficiario</th>
                                    <th class="text-left px-4 py-3">Banco</th>
                                    <th class="text-right px-4 py-3">Monto</th>
                                    <th class="text-left px-4 py-3">Fecha Emisión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportes['administracion']['cheques_pendientes'] as $cheque)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $cheque->numero_cheque }}</td>
                                    <td class="px-4 py-3">{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $cheque->banco ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">Bs {{ number_format($cheque->monto, 2) }}</td>
                                    <td class="px-4 py-3">{{ $cheque->fecha_emision?->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Sin cheques pendientes de aprobación</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CAJA --}}
                <div x-show="tab === 'caja'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-cash-register text-primary-900 mr-2"></i>Caja</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Por Entregar</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['caja']['resumen']['para_entregar'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Entregados Hoy</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['caja']['resumen']['entregados_hoy'] }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-xs text-blue-600 uppercase font-semibold">Entregados (Mes)</p>
                            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $reportes['caja']['resumen']['entregados_mes'] }}</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <p class="text-xs text-purple-600 uppercase font-semibold">Cobrados (Mes)</p>
                            <p class="text-2xl font-bold text-purple-800 mt-1">{{ $reportes['caja']['resumen']['cobrados_mes'] }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <p class="text-xs text-indigo-600 uppercase font-semibold">Monto Entregado (Mes)</p>
                            <p class="text-2xl font-bold text-indigo-800 mt-1">Bs {{ number_format($reportes['caja']['resumen']['monto_entregado_mes'], 2) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes por Entregar</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-left px-3 py-2">Cheque</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['caja']['para_entregar'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2">{{ $orden->cheque?->numero_cheque ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->neto_pagar, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin órdenes por entregar</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Últimas Entregas</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Neto Pagar</th>
                                            <th class="text-center px-3 py-2">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['caja']['ultimas_entregas'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->neto_pagar, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $orden->badge_color }}">
                                                    {{ $orden->estado_label }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin entregas recientes</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if(($reportes['caja']['resumen']['revalidados_mes'] ?? 0) > 0)
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-3">
                        <i class="fas fa-sync-alt text-blue-600"></i>
                        <span class="text-sm text-blue-700 font-medium">
                            Se revalidaron <strong>{{ $reportes['caja']['resumen']['revalidados_mes'] }}</strong> cheque(s) este mes.
                        </span>
                    </div>
                    @endif
                </div>

                {{-- ARCHIVOS --}}
                <div x-show="tab === 'archivos'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-folder text-primary-900 mr-2"></i>Archivos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <p class="text-xs text-yellow-600 uppercase font-semibold">Por Archivar</p>
                            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $reportes['archivos']['resumen']['por_archivar'] }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-xs text-green-600 uppercase font-semibold">Archivados (Mes)</p>
                            <p class="text-2xl font-bold text-green-800 mt-1">{{ $reportes['archivos']['resumen']['archivados_mes'] }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <p class="text-xs text-blue-600 uppercase font-semibold">Total Archivados</p>
                            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $reportes['archivos']['resumen']['archivados_total'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes por Archivar</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['archivos']['por_archivar'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->monto_total, 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400">Sin órdenes por archivar</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Últimos Archivados</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                            <th class="text-left px-3 py-2">N° Orden</th>
                                            <th class="text-left px-3 py-2">Beneficiario</th>
                                            <th class="text-right px-3 py-2">Monto</th>
                                            <th class="text-left px-3 py-2">Fecha Cierre</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse($reportes['archivos']['archivados'] as $orden)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 font-medium">{{ $orden->numero_orden }}</td>
                                            <td class="px-3 py-2">{{ $orden->beneficiario_nombre }}</td>
                                            <td class="px-3 py-2 text-right">Bs {{ number_format($orden->monto_total, 2) }}</td>
                                            <td class="px-3 py-2">{{ $orden->fecha_cierre?->format('d/m/Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Sin órdenes archivadas</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GLOBAL --}}
                <div x-show="tab === 'global'" x-cloak>
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-globe text-primary-900 mr-2"></i>Reporte Global del Sistema</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-xs text-primary-600 uppercase font-semibold">Total Órdenes</p>
                            <p class="text-2xl font-bold text-primary-800 mt-1">{{ $reportes['global']['resumen']['total_ordenes'] }}</p>
                            <p class="text-xs text-primary-500 mt-1">{{ $reportes['global']['resumen']['ordenes_mes'] }} este mes</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-xs text-primary-600 uppercase font-semibold">Total Cheques</p>
                            <p class="text-2xl font-bold text-primary-800 mt-1">{{ $reportes['global']['resumen']['total_cheques'] }}</p>
                            <p class="text-xs text-primary-500 mt-1">{{ $reportes['global']['resumen']['cheques_mes'] }} este mes</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-xs text-primary-600 uppercase font-semibold">Monto Total Órdenes</p>
                            <p class="text-2xl font-bold text-primary-800 mt-1">Bs {{ number_format($reportes['global']['resumen']['monto_total_ordenes'], 2) }}</p>
                            <p class="text-xs text-primary-500 mt-1">Neto: Bs {{ number_format($reportes['global']['resumen']['monto_total_neto'], 2) }}</p>
                        </div>
                        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                            <p class="text-xs text-primary-600 uppercase font-semibold">Beneficiarios / Usuarios</p>
                            <p class="text-2xl font-bold text-primary-800 mt-1">{{ $reportes['global']['resumen']['total_beneficiarios'] }} / {{ $reportes['global']['resumen']['usuarios_activos'] }}</p>
                            <p class="text-xs text-primary-500 mt-1">Beneficiarios / Usuarios activos</p>
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Órdenes por Estado</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                                    <th class="text-left px-4 py-3">Estado</th>
                                    <th class="text-right px-4 py-3">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reportes['global']['ordenes_por_estado'] as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ ucfirst(str_replace('_', ' ', $item->estado)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">{{ $item->total }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="px-4 py-8 text-center text-gray-400">Sin datos</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @php
                        $link = route('reportes.index');
                    @endphp
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-download mr-1"></i>
                            Descarga el reporte completo en PDF usando el botón en la parte superior,
                            o genera reportes específicos en el
                            <a href="{{ $link }}" class="text-primary-900 font-semibold underline hover:text-primary-950">Generador de Reportes</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center text-xs text-gray-400">
            Reporte generado el {{ $reportes['generado_en']->format('d/m/Y H:i') }}
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reportTabs', () => ({
                tab: 'tesoreria'
            }))
        })
    </script>
    @endpush
</x-app-layout>