<x-app-layout>
    <x-slot name="header">Editar Área</x-slot>

    <div class="py-6 max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Editar Área: {{ $area->nombre }}</h2>

            <form method="POST" action="{{ route('areas.update', $area) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Área <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $area->nombre) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código (Único) <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo" value="{{ old('codigo', $area->codigo) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Orden de Flujo <span class="text-red-500">*</span></label>
                        <input type="number" name="orden_flujo" value="{{ old('orden_flujo', $area->orden_flujo) }}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">{{ old('descripcion', $area->descripcion) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="activo" value="1" {{ old('activo', $area->activo) ? 'checked' : '' }} class="w-4 h-4 text-primary-700 rounded border-gray-300 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700">Área Activa</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 pl-6">Si se desactiva, los usuarios de esta área no podrán interactuar en el flujo.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('areas.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Cancelar</a>
                    <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Actualizar Área
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
