<x-app-layout>
    <x-slot name="header">Nueva Orden de Pago</x-slot>
    <div class="py-6 max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Crear Orden de Pago</h2>
            <form method="POST" action="{{ route('ordenes-pago.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded text-sm">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
                @endif

                <!-- Beneficiario -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Beneficiario</h3>

                    <input type="hidden" name="beneficiario_id" id="beneficiario_id" value="{{ old('beneficiario_id') }}">

                    <div id="buscar_modo">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar beneficiario <span class="text-red-500">*</span></label>
                        <input type="text" id="buscar_input" autocomplete="off" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Buscar por nombre, apellidos o CI/NIT...">
                        <div id="resultados" class="hidden mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto absolute z-10 w-full"></div>
                    </div>

                    <div id="seleccionado_modo" class="hidden mt-3">
                        <div class="flex items-start justify-between bg-primary-50 border border-primary-200 rounded-lg p-3">
                            <div>
                                <p id="beneficiario_nombre_display" class="text-sm font-medium text-gray-800"></p>
                                <p id="beneficiario_ci_display" class="text-xs text-gray-500 mt-0.5"></p>
                            </div>
                            <button type="button" id="cambiar_beneficiario" class="text-xs text-primary-700 hover:text-primary-900 font-medium">Cambiar</button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">A la orden de</label>
                        <input type="text" name="a_la_orden_de" id="a_la_orden_de" value="{{ old('a_la_orden_de') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Dejar vacío para usar nombre del beneficiario">
                    </div>
                </div>

                <!-- Concepto y categoría -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Descripción del Pago</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Concepto <span class="text-red-500">*</span></label>
                            <textarea name="concepto" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">{{ old('concepto') }}</textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Concepto de Pago</label>
                                <select name="concepto_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Pago" {{ old('concepto_pago')=='Pago'?'selected':'' }}>Pago</option>
                                    <option value="Devolución" {{ old('concepto_pago')=='Devolución'?'selected':'' }}>Devolución</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría de Gasto</label>
                                <select name="categoria_gasto_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_gasto_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hidden">
                                <input type="hidden" name="fecha_orden" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Liquidador</label>
                                <input type="text" name="liquidador_texto" value="{{ auth()->user()->name }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">N° de Fojas</label>
                                <input type="number" name="numero_fojas" value="{{ old('numero_fojas', 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
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
                            <input type="number" name="monto_total" id="monto_total" value="{{ old('monto_total') }}" required step="0.01" min="0" oninput="calcularNeto()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="aplica_retencion_7" id="ret7" value="1" {{ old('aplica_retencion_7')?'checked':'' }} onchange="calcularNeto()" class="rounded">
                                Retención 7% (IUE)
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="aplica_retencion_35" id="ret35" value="1" {{ old('aplica_retencion_35')?'checked':'' }} onchange="calcularNeto()" class="rounded">
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
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Documentos de Respaldo</h3>
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
                    <textarea name="observaciones" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">{{ old('observaciones') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Crear Orden de Pago
                    </button>
                    <a href="{{ route('ordenes-pago.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancelar</a>
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

    // Buscador de beneficiarios
    const buscarInput = document.getElementById('buscar_input');
    const resultados = document.getElementById('resultados');
    const beneficiarioIdInput = document.getElementById('beneficiario_id');
    const buscarModo = document.getElementById('buscar_modo');
    const seleccionadoModo = document.getElementById('seleccionado_modo');
    const nombreDisplay = document.getElementById('beneficiario_nombre_display');
    const ciDisplay = document.getElementById('beneficiario_ci_display');
    let timeoutId = null;
    let selectedData = null;

    buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const q = this.value.trim();
        if (q.length < 2) {
            resultados.classList.add('hidden');
            resultados.innerHTML = '';
            return;
        }
        timeoutId = setTimeout(() => buscarBeneficiarios(q), 300);
    });

    async function buscarBeneficiarios(q) {
        try {
            const response = await fetch(`/api/v1/beneficiarios/buscar?q=${encodeURIComponent(q)}`);
            const data = await response.json();
            resultados.innerHTML = '';
            if (data.length === 0) {
                resultados.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>';
                resultados.classList.remove('hidden');
                return;
            }
            data.forEach(b => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2.5 hover:bg-primary-50 cursor-pointer border-b border-gray-100 last:border-0 text-sm';
                div.innerHTML = `<span class="font-medium text-gray-800">${escapeHtml(b.nombre_razon_social)}${b.apellidos ? ' ' + escapeHtml(b.apellidos) : ''}</span> <span class="text-gray-400 text-xs ml-2">${escapeHtml(b.ci_nit || '')}</span>`;
                div.addEventListener('click', () => seleccionarBeneficiario(b));
                resultados.appendChild(div);
            });
            resultados.classList.remove('hidden');
        } catch (e) {
            console.error('Error buscando beneficiarios:', e);
        }
    }

    function seleccionarBeneficiario(b) {
        beneficiarioIdInput.value = b.id;
        selectedData = b;
        nombreDisplay.textContent = b.nombre_razon_social + (b.apellidos ? ' ' + b.apellidos : '');
        ciDisplay.textContent = b.ci_nit ? 'CI/NIT: ' + b.ci_nit : '';
        buscarModo.classList.add('hidden');
        seleccionadoModo.classList.remove('hidden');
        resultados.classList.add('hidden');
        resultados.innerHTML = '';
        buscarInput.value = '';
    }

    document.getElementById('cambiar_beneficiario').addEventListener('click', function() {
        beneficiarioIdInput.value = '';
        selectedData = null;
        seleccionadoModo.classList.add('hidden');
        buscarModo.classList.remove('hidden');
        buscarInput.focus();
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#buscar_modo')) {
            resultados.classList.add('hidden');
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    </script>
    @endpush
</x-app-layout>
