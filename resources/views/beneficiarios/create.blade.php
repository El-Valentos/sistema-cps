<x-app-layout>
    <x-slot name="header">Nuevo Beneficiario</x-slot>
    <div class="py-6 max-w-3xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6">Registrar Beneficiario</h2>
            <form method="POST" action="{{ route('beneficiarios.store') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select name="tipo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none @error('tipo') border-red-500 @enderror">
                            <option value="persona" {{ old('tipo')=='persona'?'selected':'' }}>Persona Natural</option>
                            <option value="empresa" {{ old('tipo')=='empresa'?'selected':'' }}>Empresa</option>
                        </select>
                        @error('tipo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CI / NIT</label>
                        <input type="text" name="ci_nit" value="{{ old('ci_nit') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Razón Social <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_razon_social" value="{{ old('nombre_razon_social') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none @error('nombre_razon_social') border-red-500 @enderror">
                        @error('nombre_razon_social')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                        <input type="text" name="apellidos" value="{{ old('apellidos') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-primary-900 hover:bg-primary-950 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>Guardar
                    </button>
                    <a href="{{ route('beneficiarios.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
