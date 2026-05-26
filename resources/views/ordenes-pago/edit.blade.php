<x-app-layout>
    <x-slot name="header">Editar Orden de Pago</x-slot>
    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Editar Orden de Pago {{ $ordenPago->numero_orden }}</h2>
            <form method="POST" action="{{ route('ordenes-pago.update', $ordenPago) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded text-sm">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
                @endif

                <!-- Beneficiario -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Datos del Beneficiario</h3>
                    <input type="hidden" name="beneficiario_id" id="beneficiario_id" value="{{ old('beneficiario_id', $ordenPago->beneficiario_id) }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre o Razón Social</label>
                            <input type="text" name="nombre_razon_social" id="nombre_razon_social" value="{{ old('nombre_razon_social', $ordenPago->beneficiario_nombre) }}" {{ $ordenPago->estado !== 'pendiente_tesoreria' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none {{ $ordenPago->estado === 'pendiente_tesoreria' ? '' : 'bg-gray-100' }}" placeholder="Nombre completo o razón social">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $ordenPago->beneficiario_apellidos) }}" {{ $ordenPago->estado !== 'pendiente_tesoreria' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none {{ $ordenPago->estado === 'pendiente_tesoreria' ? '' : 'bg-gray-100' }}" placeholder="Apellidos (si es persona)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CI/NIT/N° Patronal</label>
                            <input type="text" name="ci_nit" id="ci_nit" value="{{ old('ci_nit', $ordenPago->beneficiario_ci_nit) }}" {{ $ordenPago->estado !== 'pendiente_tesoreria' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none {{ $ordenPago->estado === 'pendiente_tesoreria' ? '' : 'bg-gray-100' }}" placeholder="Cédula de identidad, NIT o N° Patronal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $ordenPago->beneficiario_telefono) }}" {{ $ordenPago->estado !== 'pendiente_tesoreria' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none {{ $ordenPago->estado === 'pendiente_tesoreria' ? '' : 'bg-gray-100' }}" placeholder="Teléfono de contacto">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $ordenPago->beneficiario_direccion) }}" {{ $ordenPago->estado !== 'pendiente_tesoreria' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none {{ $ordenPago->estado === 'pendiente_tesoreria' ? '' : 'bg-gray-100' }}" placeholder="Dirección fiscal">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">A la orden de</label>
                            <input type="text" name="a_la_orden_de" id="a_la_orden_de" value="{{ old('a_la_orden_de', $ordenPago->a_la_orden_de) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Dejar vacío para usar nombre del beneficiario">
                        </div>
                    </div>
                </div>

                <!-- Concepto y categoría -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Descripción del Pago</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Concepto <span class="text-red-500">*</span></label>
                            <textarea name="concepto" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">{{ old('concepto', $ordenPago->concepto) }}</textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concepto de Pago</label>
                                <select name="concepto_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Pago" {{ old('concepto_pago', $ordenPago->concepto_pago)=='Pago'?'selected':'' }}>Pago</option>
                                    <option value="Devolución" {{ old('concepto_pago', $ordenPago->concepto_pago)=='Devolución'?'selected':'' }}>Devolución</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría de Gasto</label>
                                <select name="categoria_gasto_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_gasto_id', $ordenPago->categoria_gasto_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Orden <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_orden" value="{{ old('fecha_orden', $ordenPago->fecha_orden?->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Liquidador</label>
                                <input type="text" name="liquidador_texto" value="{{ old('liquidador_texto', $ordenPago->liquidador_texto ?? $ordenPago->liquidador->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° de Fojas</label>
                                <input type="number" name="numero_fojas" value="{{ old('numero_fojas', $ordenPago->numero_fojas) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Montos -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Liquidación de Montos</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto Total (Bs.) <span class="text-red-500">*</span></label>
                            <input type="number" name="monto_total" id="monto_total" value="{{ old('monto_total', $ordenPago->monto_total) }}" required step="0.01" min="0" oninput="calcularNeto()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="aplica_retencion_7" id="ret7" value="1" {{ old('aplica_retencion_7', $ordenPago->retencion_7 > 0) ? 'checked' : '' }} onchange="calcularNeto()" class="rounded">
                                Retención 7% (IUE)
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="aplica_retencion_35" id="ret35" value="1" {{ old('aplica_retencion_35', $ordenPago->retencion_35 > 0) ? 'checked' : '' }} onchange="calcularNeto()" class="rounded">
                                Retención 3.5% (IT)
                            </label>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3" id="retencion_7_box" style="display:none">
                                <p class="text-xs text-red-700 font-medium">Descuento 7% (IUE)</p>
                                <p class="text-lg font-bold text-red-800" id="ret7_display">Bs. 0.00</p>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3" id="retencion_35_box" style="display:none">
                                <p class="text-xs text-orange-700 font-medium">Descuento 3.5% (IT)</p>
                                <p class="text-lg font-bold text-orange-800" id="ret35_display">Bs. 0.00</p>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <p class="text-xs text-green-700 font-medium">Neto a Pagar</p>
                                <p class="text-lg font-bold text-green-800" id="neto_display">Bs. 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentos -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Documentos de Respaldo Adicionales</h3>
                    <div class="flex items-center gap-3">
                        <input type="file" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        <button type="button" onclick="document.getElementById('fileInput').click()" class="bg-primary-50 hover:bg-primary-100 text-primary-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-plus mr-1"></i>Agregar archivos
                        </button>
                        <span id="file-count" class="text-xs text-gray-500"></span>
                    </div>
                    <div id="file-list" class="mt-3 space-y-2"></div>
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG. Máximo 10MB por archivo. Máximo 15 archivos.</p>
                    <p id="file-error" class="text-xs text-red-500 mt-1 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">{{ old('observaciones', $ordenPago->observaciones) }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                    <a href="{{ route('ordenes-pago.show', $ordenPago) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
    <script>
    function calcularNeto() {
        const monto = parseFloat(document.getElementById('monto_total').value) || 0;
        const r7    = document.getElementById('ret7').checked ? monto * 0.07 : 0;
        const r35   = document.getElementById('ret35').checked ? monto * 0.035 : 0;
        const neto  = monto - r7 - r35;

        document.getElementById('neto_display').textContent = 'Bs. ' + neto.toFixed(2);

        const ret7Box = document.getElementById('retencion_7_box');
        const ret7Display = document.getElementById('ret7_display');
        if (document.getElementById('ret7').checked) {
            ret7Box.style.display = 'block';
            ret7Display.textContent = '- Bs. ' + r7.toFixed(2);
        } else {
            ret7Box.style.display = 'none';
        }

        const ret35Box = document.getElementById('retencion_35_box');
        const ret35Display = document.getElementById('ret35_display');
        if (document.getElementById('ret35').checked) {
            ret35Box.style.display = 'block';
            ret35Display.textContent = '- Bs. ' + r35.toFixed(2);
        } else {
            ret35Box.style.display = 'none';
        }
    }
    calcularNeto();

    const archivosSeleccionados = [];

    document.getElementById('fileInput').addEventListener('change', function() {
        const nuevos = Array.from(this.files);
        const error = document.getElementById('file-error');

        if (archivosSeleccionados.length + nuevos.length > 15) {
            error.textContent = 'Solo se permiten hasta 15 archivos en total.';
            error.classList.remove('hidden');
            this.value = '';
            return;
        }

        error.classList.add('hidden');

        for (const file of nuevos) {
            archivosSeleccionados.push(file);
        }

        actualizarLista();
        this.value = '';
    });

    function actualizarLista() {
        const list = document.getElementById('file-list');
        list.innerHTML = '';
        archivosSeleccionados.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between bg-gray-50 px-3 py-2 rounded-lg';
            div.innerHTML = '<span class="text-sm text-gray-700 truncate">' + file.name + '</span>' +
                '<button type="button" onclick="removerArchivo(' + index + ')" class="text-red-500 hover:text-red-700 text-sm ml-2"><i class="fas fa-times"></i></button>';
            list.appendChild(div);
        });
        document.getElementById('file-count').textContent = archivosSeleccionados.length > 0 ? archivosSeleccionados.length + ' archivo(s) seleccionado(s)' : '';
    }

    function removerArchivo(index) {
        archivosSeleccionados.splice(index, 1);
        actualizarLista();
    }

    document.querySelector('form').addEventListener('submit', function() {
        const input = document.getElementById('fileInput');
        const dt = new DataTransfer();
        archivosSeleccionados.forEach(function(file) { dt.items.add(file); });
        input.files = dt.files;
    });

    // Autocompletar beneficiario por CI/NIT
    const ciNitInput = document.getElementById('ci_nit');
    let buscando = false;

    ciNitInput.addEventListener('blur', async function() {
        const ciNit = this.value.trim();
        if (!ciNit || buscando) return;
        if (document.getElementById('beneficiario_id').value) return;

        buscando = true;
        try {
            const response = await fetch(`/api/v1/beneficiarios/buscar?ci_nit=${encodeURIComponent(ciNit)}`);
            const data = await response.json();
            if (data.found && data.beneficiario) {
                document.getElementById('beneficiario_id').value = data.beneficiario.id;
                document.getElementById('nombre_razon_social').value = data.beneficiario.nombre_razon_social || '';
                document.getElementById('apellidos').value = data.beneficiario.apellidos || '';
                document.getElementById('telefono').value = data.beneficiario.telefono || '';
                document.getElementById('direccion').value = data.beneficiario.direccion || '';
            }
        } catch (e) {
            console.error('Error buscando beneficiario:', e);
        }
        buscando = false;
    });
    </script>
    @endpush
</x-app-layout>
