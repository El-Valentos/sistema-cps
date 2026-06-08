<x-app-layout>
    <x-slot name="header">Orden de Pago</x-slot>
    <div class="py-6 max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('ordenes-pago.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Orden de Pago {{ $ordenPago->numero_orden }}</h2>
            @php $c = ['pendiente_tesoreria'=>'yellow','enviado_financiera'=>'orange','rechazado_financiera'=>'red','enviado_contabilidad'=>'blue','cheque_generado'=>'indigo','en_caja'=>'purple','entregado'=>'green','cerrado'=>'gray','anulado'=>'red'][$ordenPago->estado] ?? 'gray'; @endphp
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $c }}-100 text-{{ $c }}-700">{{ $ordenPago->estado_label }}</span>
        </div>

        <!-- Orden de Pago - Formato Oficial -->
        <div class="bg-white border-2 border-gray-800 rounded-lg p-6 mb-6">
            <!-- Datos Institucionales -->
            <div class="text-center border-b-2 border-gray-300 pb-4 mb-6">
                <div class="text-xs text-gray-500 uppercase tracking-widest">Caja Petrolera de Salud</div>
                <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Orden de Pago</h1>
                <div class="text-sm text-gray-600 mt-1">
                    {{ $ordenPago->ciudad }} | No. {{ $ordenPago->numero_orden }} | Gestión {{ $ordenPago->gestion }}
                </div>

                <div class="text-xs font-medium text-gray-700 mt-2 uppercase">Jefatura Departamental Adm. Financiera</div>
            </div>

            <!-- Datos del Beneficiario y Liquidador -->
            <div class="mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="font-bold text-gray-800">Liquidador:</span>
                        <span class="text-gray-700">{{ $ordenPago->liquidador_texto ?? $ordenPago->liquidador->name ?? '-' }}</span>
                    </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="font-bold text-gray-800 mb-3">Sírvase efecturar la cancelación de:</p>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-medium">Bs. {{ number_format($ordenPago->monto_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-red-600">
                            <span>Retención 7% (IUE):</span>
                            <span>Bs. {{ number_format($ordenPago->retencion_7, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-red-600">
                            <span>Retención 3.5% (IT):</span>
                            <span>Bs. {{ number_format($ordenPago->retencion_35, 2) }}</span>
                        </div>
                        @if($ordenPago->devolucion_retencion > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Devolución Retención:</span>
                            <span>Bs. {{ number_format($ordenPago->devolucion_retencion, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-300 pt-2 font-bold text-lg text-green-700">
                            <span>MONTO A PAGAR:</span>
                            <span>Bs. {{ number_format($ordenPago->neto_pagar, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div>
                        <span class="font-bold text-gray-800">A la orden de:</span>
                        <span class="text-gray-700">{{ $ordenPago->a_la_orden_de ?? ($ordenPago->beneficiario_nombre . ' ' . $ordenPago->beneficiario_apellidos) }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800">Beneficiario:</span>
                        <span class="text-gray-700">{{ $ordenPago->beneficiario_nombre }} {{ $ordenPago->beneficiario_apellidos }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800">Por concepto de:</span>
                        <span class="text-gray-700">{{ $ordenPago->concepto }}</span>
                    </div>
                </div>

                <!-- Categoría de Gasto -->
                @if($ordenPago->categoriaGasto)
                <div class="mt-4 text-sm">
                    <span class="font-bold text-gray-800">Categoría de Gasto:</span>
                    <span class="text-gray-700">{{ $ordenPago->categoriaGasto->nombre }}</span>
                </div>
                @endif

                <!-- Con respaldo de -->
                <div class="mt-4 text-sm">
                    <span class="font-bold text-gray-800">Con respaldo de:</span>
                    <span class="text-gray-700">
                        @if($ordenPago->documentosAdjuntos->count() > 0)
                            <a href="#" onclick="event.preventDefault(); document.getElementById('modal-documentos').classList.remove('hidden');" class="text-primary-700 hover:text-primary-900 underline font-medium">DOCUMENTACIÓN ADJUNTA ({{ $ordenPago->documentosAdjuntos->count() }} archivo(s))</a>
                        @elseif($ordenPago->tiene_respaldo)
                            DOCUMENTACIÓN ADJUNTA
                        @else
                            SIN ADJUNTAR
                        @endif
                    </span>
                    <span class="text-gray-600"> | Fojas {{ $ordenPago->numero_fojas ?? 0 }}</span>
                </div>
            </div>

            
        </div>

        <!-- Documentos Adjuntos -->
        @if($ordenPago->documentosAdjuntos->count() > 0)
        <div id="seccion-documentos" class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-3">Documentos Adjuntos</h3>
            <div class="space-y-2">
                @foreach($ordenPago->documentosAdjuntos as $doc)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-{{ $doc->tipo_archivo === 'application/pdf' ? 'pdf text-red-600' : 'image text-primary-700' }}"></i>
                        <span>{{ $doc->nombre_archivo }}</span>
                    </div>
                    <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank" class="text-primary-700 hover:text-primary-900 text-xs">Ver</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Cheque info -->
        @if($ordenPago->cheque)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-indigo-800 mb-2"><i class="fas fa-money-check mr-2"></i>Cheque Emitido</h3>
            <div class="text-sm grid grid-cols-2 gap-3 text-indigo-700">
                <div>N° Cheque: <strong>{{ $ordenPago->cheque->numero_cheque }}</strong></div>
                <div>Monto: <strong>Bs. {{ number_format($ordenPago->cheque->monto,2) }}</strong></div>
            </div>
        </div>
        @endif

        <!-- Tracking -->
        @if($ordenPago->trackingHistorial->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Historial de Seguimiento</h3>
            <div class="space-y-3">
                @foreach($ordenPago->trackingHistorial->sortByDesc('fecha_hora') as $t)
                <div class="flex gap-3 text-sm">
                    <div class="w-2 h-2 rounded-full bg-primary-500 mt-1.5 flex-shrink-0"></div>
                    <div>
                        <span class="font-medium text-gray-800">{{ $t->accion }}</span>
                        @if($t->comentario)<span class="text-gray-500"> — {{ $t->comentario }}</span>@endif
                        <p class="text-xs text-gray-400">{{ $t->usuario->name ?? '-' }} · {{ \Carbon\Carbon::parse($t->fecha_hora)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Acciones -->
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('ordenes-pago.pdf', $ordenPago) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Ver PDF
            </a>
            @can('aprobar_orden_pago')
            @if($ordenPago->estado === 'pendiente_tesoreria')
            <form method="POST" action="{{ route('ordenes-pago.aprobar', $ordenPago) }}" onsubmit="return confirm('¿Aprobar y enviar a Financiera?')">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-check mr-2"></i>Aprobar y Enviar a Financiera
                </button>
            </form>
            @endif
            @if($ordenPago->estado === 'rechazado_financiera')
            <form method="POST" action="{{ route('ordenes-pago.reenviar-financiera', $ordenPago) }}" onsubmit="return confirm('¿Reenviar a Financiera?')">
                @csrf
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-redo mr-2"></i>Reenviar a Financiera
                </button>
            </form>
            @endif
            @endcan
            @can('editar_orden_pago')
            @if(in_array($ordenPago->estado, ['pendiente_tesoreria', 'rechazado_financiera']))
            <a href="{{ route('ordenes-pago.edit', $ordenPago) }}" class="bg-primary-700 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>Editar Orden
            </a>
            @endif
            @endcan

            @if($ordenPago->estado === 'enviado_financiera' && auth()->user()->hasAnyRole(['Super Admin', 'Financiera']))
            <form method="POST" action="{{ route('financiera.aprobar', $ordenPago) }}" onsubmit="return confirm('¿Aprobar y enviar a Contabilidad?')">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-check-double mr-2"></i>Aprobar y Enviar a Contabilidad
                </button>
            </form>
            <button type="button" onclick="rechazarOrden({{ $ordenPago->id }})" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-times mr-2"></i>Rechazar Orden
            </button>
            @endif

            <a href="{{ route('ordenes-pago.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Volver</a>
        </div>
    </div>

    <!-- Modal Rechazo -->
    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Motivo de Rechazo</h3>
                <form id="form-rechazo" method="POST" class="mt-2 text-left">
                    @csrf
                    <input type="hidden" name="orden_id" id="rechazo_orden_id">
                    <textarea name="motivo_rechazo" rows="4" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" required placeholder="Explique el motivo del rechazo..."></textarea>
                    <div class="items-center px-4 py-3 mt-4 flex justify-end gap-2">
                        <button type="button" onclick="cerrarModalRechazo()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Rechazar Orden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rechazarOrden(id) {
            document.getElementById('form-rechazo').action = `/financiera/${id}/rechazar`;
            document.getElementById('modal-rechazo').classList.remove('hidden');
        }

        function cerrarModalRechazo() {
            document.getElementById('modal-rechazo').classList.add('hidden');
            document.getElementById('form-rechazo').reset();
        }
    </script>
</x-app-layout>
