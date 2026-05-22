<x-app-layout>
    <x-slot name="header">Generar Cheque</x-slot>
    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-money-check mr-2 text-primary-800"></i>Generar Nuevo Cheque
                </h2>
                
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('cheques.store') }}" class="space-y-5">
                    @csrf
                    
                    @if($ordenPreseleccionada)
                        <input type="hidden" name="orden_pago_id" value="{{ $ordenPreseleccionada->id }}">
                        <div class="bg-white border-2 border-gray-800 rounded-lg p-6 mb-6">
                            <div class="text-center border-b-2 border-gray-300 pb-4 mb-6">
                                <div class="text-xs text-gray-500 uppercase tracking-widest">Caja Petrolera de Salud</div>
                                <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Orden de Pago</h1>
                                <div class="text-sm text-gray-600 mt-1">{{ $ordenPreseleccionada->ciudad }} | No. {{ $ordenPreseleccionada->numero_orden }} | Gestión {{ $ordenPreseleccionada->gestion }}</div>
                                <div class="text-xs font-medium text-gray-700 mt-2 uppercase">Jefatura Departamental Adm. Financiera</div>
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="font-bold text-gray-800">Liquidador:</span>
                                    <span class="text-gray-700">{{ $ordenPreseleccionada->liquidador->name ?? '-' }}</span>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p class="font-bold text-gray-800 mb-3">Sírvase efecturar la cancelación de:</p>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-medium">Bs. {{ number_format($ordenPreseleccionada->monto_total, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-red-600">
                                            <span>Retención 7% (IUE):</span>
                                            <span>Bs. {{ number_format($ordenPreseleccionada->retencion_7, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-red-600">
                                            <span>Retención 3.5% (IT):</span>
                                            <span>Bs. {{ number_format($ordenPreseleccionada->retencion_35, 2) }}</span>
                                        </div>
                                        @if($ordenPreseleccionada->devolucion_retencion > 0)
                                        <div class="flex justify-between text-green-600">
                                            <span>Devolución Retención:</span>
                                            <span>Bs. {{ number_format($ordenPreseleccionada->devolucion_retencion, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between border-t border-gray-300 pt-2 font-bold text-lg text-green-700">
                                            <span>MONTO A PAGAR:</span>
                                            <span>Bs. {{ number_format($ordenPreseleccionada->neto_pagar, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-2 text-sm">
                                    <div>
                                        <span class="font-bold text-gray-800">A la orden de:</span>
                                        <span class="text-gray-700">{{ $ordenPreseleccionada->a_la_orden_de ?? $ordenPreseleccionada->beneficiario_nombre }}</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-800">Empresa:</span>
                                        <span class="text-gray-700">{{ $ordenPreseleccionada->beneficiario_nombre }}</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-800">Por concepto de:</span>
                                        <span class="text-gray-700">{{ $ordenPreseleccionada->concepto }}</span>
                                    </div>
                                </div>

                                @if($ordenPreseleccionada->categoria_gasto_id)
                                <div class="mt-4 text-sm">
                                    <span class="font-bold text-gray-800">Categoría de Gasto:</span>
                                    <span class="text-gray-700">
                                        @php
                                        $categorias = [
                                            'incapacidad_temporal' => 'Devolución por incapacidad temporal',
                                            'medicamentos' => 'Medicamentos',
                                            'pasajes' => 'Pasajes',
                                            'servicios' => 'Servicios'
                                        ];
                                        echo $categorias[$ordenPreseleccionada->categoria_gasto_id] ?? $ordenPreseleccionada->categoria_gasto_id;
                                        @endphp
                                    </span>
                                </div>
                                @endif

                                <div class="mt-4 text-sm">
                                    <span class="font-bold text-gray-800">Con respaldo de:</span>
                                    <span class="text-gray-700">
                                        @if($ordenPreseleccionada->documentosAdjuntos->count() > 0)
                                            <a href="#" onclick="event.preventDefault(); document.getElementById('modal-documentos').classList.remove('hidden');" class="text-primary-700 hover:text-primary-900 underline font-medium">DOCUMENTACIÓN ADJUNTA ({{ $ordenPreseleccionada->documentosAdjuntos->count() }} archivo(s))</a>
                                        @elseif($ordenPreseleccionada->tiene_respaldo)
                                            DOCUMENTACIÓN ADJUNTA
                                        @else
                                            SIN ADJUNTAR
                                        @endif
                                    </span>
                                    <span class="text-gray-600"> | Fojas {{ $ordenPreseleccionada->numero_fojas ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        @if($ordenesPendientes->isEmpty())
                            <div class="text-center py-12 text-gray-400">
                                <i class="fas fa-inbox text-5xl mb-4 block opacity-30"></i>
                                <p class="text-lg mb-4">No hay órdenes de pago pendientes de cheque</p>
                                <a href="{{ route('cheques.index') }}" class="inline-block bg-primary-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-950">
                                    Volver a Cheques
                                </a>
                            </div>
                        @else
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Orden de Pago <span class="text-red-500">*</span>
                                </label>
                                <select name="orden_pago_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Seleccionar Orden --</option>
                                    @foreach($ordenesPendientes as $op)
                                    <option value="{{ $op->id }}" {{ old('orden_pago_id')==$op->id?'selected':'' }}>
                                        {{ $op->numero_orden }} — {{ $op->beneficiario_nombre }} — Bs. {{ number_format($op->neto_pagar,2) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endif

                    @if($ordenPreseleccionada || !$ordenesPendientes->isEmpty())
                        <div class="border-t border-gray-200 pt-5">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase">Datos del Cheque</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Banco <span class="text-red-500">*</span></label>
                                    <input type="text" name="banco" value="{{ old('banco', 'Banco Nacional de Bolivia') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Cuenta</label>
                                    <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-save mr-2"></i>Generar Cheque
                            </button>
                            
                            @if($ordenPreseleccionada)
                            <button type="button" onclick="abrirModalRechazo()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>Rechazar Orden
                            </button>
                            @endif

                            <a href="{{ route('cheques.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                Cancelar
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    @if($ordenPreseleccionada)
    <!-- Modal Rechazo -->
    <div id="modal-rechazo" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center">Motivo de Rechazo</h3>
                <form action="{{ route('contabilidad.rechazar', $ordenPreseleccionada) }}" method="POST" class="mt-4">
                    @csrf
                    <textarea name="motivo_rechazo" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" required placeholder="Explique el motivo del rechazo..."></textarea>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="cerrarModalRechazo()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirmar Rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Documentos Adjuntos -->
    @if($ordenPreseleccionada && $ordenPreseleccionada->documentosAdjuntos->count() > 0)
    <div id="modal-documentos" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Documentos Adjuntos</h3>
                <button onclick="cerrarModalDocumentos()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-3">
                @foreach($ordenPreseleccionada->documentosAdjuntos as $doc)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-{{ $doc->tipo_archivo === 'application/pdf' ? 'pdf text-red-600' : 'image text-primary-700' }} text-xl"></i>
                        <div>
                            <p class="font-medium text-gray-800">{{ $doc->nombre_archivo }}</p>
                            <p class="text-xs text-gray-500">{{ $doc->tipo_archivo }}</p>
                        </div>
                    </div>
                    <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank" class="bg-primary-700 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-eye mr-1"></i>Ver
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <script>
        function cerrarModalDocumentos() {
            document.getElementById('modal-documentos').classList.add('hidden');
        }

        function abrirModalRechazo() {
            document.getElementById('modal-rechazo').classList.remove('hidden');
        }

        function cerrarModalRechazo() {
            document.getElementById('modal-rechazo').classList.add('hidden');
        }
    </script>
</x-app-layout>