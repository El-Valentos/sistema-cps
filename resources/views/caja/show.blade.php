<x-app-layout>
    <x-slot name="header">Detalle de Caja</x-slot>
    <div class="py-6 max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('caja.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Orden de Pago: {{ $ordenPago->numero_orden }}</h2>
            @if($ordenPago->estado === 'entregado')
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium ml-auto">Entregado</span>
            @elseif($ordenPago->estado === 'revalidado')
            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium ml-auto">Revalidado</span>
            @else
            <span class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium ml-auto">Pendiente de Entrega</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Información de la Orden</h3>
                <div class="space-y-3 text-sm">
                    <div><span class="text-gray-500">Beneficiario:</span> <span class="font-semibold block text-base">{{ $ordenPago->beneficiario_nombre ?? '-' }}</span></div>
                    <div><span class="text-gray-500">CI/NIT/N° Patronal Beneficiario:</span> <span class="font-medium block">{{ $ordenPago->beneficiario_ci_nit ?? '-' }}</span></div>
                    <div><span class="text-gray-500">Teléfono/Celular:</span> <span class="font-medium block">{{ $ordenPago->beneficiario_telefono ?? '-' }}</span></div>
                    <div><span class="text-gray-500">Concepto:</span> <span class="font-medium block">{{ $ordenPago->concepto }}</span></div>
                    <div><span class="text-gray-500">Neto a Pagar:</span> <span class="font-bold text-green-700 text-lg block">Bs. {{ number_format($ordenPago->neto_pagar, 2) }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Información del Cheque</h3>
                @if($ordenPago->cheque)
                <div class="space-y-3 text-sm">
                    <div><span class="text-gray-500">Número de Cheque:</span> <span class="font-bold text-lg block">{{ $ordenPago->cheque->numero_cheque }}</span></div>
                    <div><span class="text-gray-500">Fecha Emisión:</span> <span class="font-medium block">{{ \Carbon\Carbon::parse($ordenPago->cheque->fecha_emision)->format('d/m/Y') }}</span></div>
                </div>
                @else
                <p class="text-gray-500 italic">La información del cheque no está disponible.</p>
                @endif
            </div>
        </div>

        @if(in_array($ordenPago->estado, ['cheque_generado', 'en_caja']))
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border-2 border-primary-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-handshake text-primary-700"></i> Registrar Entrega de Cheque
            </h3>
            <form method="POST" action="{{ route('caja.entrega', $ordenPago) }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Entregado a (Nombre Completo) <span class="text-red-500">*</span></label>
                        <input type="text" name="recibido_por" value="{{ $ordenPago->beneficiario_nombre ?? '' }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula de Identidad (CI) <span class="text-red-500">*</span></label>
                        <input type="text" name="ci_recibido" value="{{ $ordenPago->beneficiario->numero_documento ?? '' }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Entrega <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_entrega" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Firma / Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Ej. Entregado con poder notarial..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-colors shadow-sm">
                        Confirmar y Registrar Entrega
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($ordenPago->estado === 'entregado')
        <div class="bg-green-50 rounded-xl p-5 mb-6 border border-green-200">
            <h3 class="text-green-800 font-bold mb-2"><i class="fas fa-check-circle mr-2"></i>Cheque Entregado Exitosamente</h3>
            <p class="text-green-700 text-sm mb-4">Este cheque ya fue entregado y la orden está cerrada temporalmente hasta su archivo definitivo.</p>
            
            <form action="{{ route('caja.enviarContabilidad', $ordenPago) }}" method="POST">
                @csrf
                <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-bold transition-all shadow-sm">
                    📤 Enviar a Contabilidad para Archivo
                </button>
            </form>
        </div>
        @endif

        @if($ordenPago->estado === 'revalidado')
        <div class="bg-gray-50 rounded-xl p-5 mb-6 border border-gray-200">
            <h3 class="text-gray-800 font-bold mb-2"><i class="fas fa-check-circle mr-2"></i>Cheque Revalidado</h3>
            <p class="text-gray-600 text-sm">Este cheque ha sido revalidado. No se puede editar ni entregar.</p>
        </div>
        @endif
    </div>
</x-app-layout>
