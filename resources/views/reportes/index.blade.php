<x-app-layout>
    <x-slot name="header">Reportes</x-slot>
    <div class="py-6 max-w-3xl">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Generador de Reportes</h2>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('reportes.generar') }}" class="space-y-5">
                @csrf
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded text-sm">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Reporte <span class="text-red-500">*</span></label>
                        @php
                            $userRole = auth()->user()->roles->first()->name ?? '';
                        @endphp
                        <select name="tipo_reporte" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">-- Seleccionar --</option>
                            @if($userRole === 'Tesorería')
                            <option value="ordenes">Órdenes de Pago</option>
                            @elseif($userRole === 'Contabilidad')
                            <option value="cheques">Cheques</option>
                            @else
                            @if($userRole !== 'Caja')
                            <option value="ordenes">Órdenes de Pago</option>
                            @endif
                            <option value="cheques">Cheques</option>
                            <option value="beneficiarios">Beneficiarios</option>
                            <option value="resoluciones">Resoluciones</option>
                            <option value="devoluciones">Devoluciones por Pagar</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
                        <select name="formato" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV (Excel)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_desde" value="{{ date('Y-m-01') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_hasta" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Área (opcional)</label>
                        @php
                            $areaRole = auth()->user()->roles->first()->name ?? '';
                            $areaMap = ['Tesorería' => 'tesoreria', 'Contabilidad' => 'contabilidad'];
                        @endphp
                        <select name="area" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            @if(in_array($areaRole, ['Tesorería', 'Contabilidad']))
                            <option value="{{ $areaMap[$areaRole] }}">{{ $areaRole }}</option>
                            @else
                            <option value="">Todas las Áreas</option>
                            <option value="tesoreria">Tesorería</option>
                            <option value="financiera">Financiera</option>
                            <option value="contabilidad">Contabilidad</option>
                            <option value="presupuesto">Presupuesto</option>
                            <option value="administracion">Administración</option>
                            <option value="caja">Caja</option>
                            <option value="archivos">Archivos</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Beneficiario (opcional)</label>
                        <select name="beneficiario_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">Todos</option>
                            @foreach($beneficiarios as $b)
                            <option value="{{ $b->id }}">{{ $b->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-download mr-2"></i>Generar Reporte
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
