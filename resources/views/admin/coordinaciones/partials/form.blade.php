<form action="{{ $action }}" method="POST" class="bg-white shadow rounded p-6 space-y-6 max-w-xl mx-auto">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block font-semibold mb-1">Nombre</label>
        <input type="text" name="nombre" required class="w-full border px-4 py-2 rounded"
               value="{{ old('nombre', $coordinacion->nombre ?? '') }}">
    </div>

    <div>
        <label class="block font-semibold mb-1">Descripción (opcional)</label>
        <textarea name="descripcion" rows="2" class="w-full border px-4 py-2 rounded">{{ old('descripcion', $coordinacion->descripcion ?? '') }}</textarea>
    </div>

    <div>
        <label class="block font-semibold mb-1">Color</label>
        <style>
            .color-box:has(input:checked) {
                outline: 3px solid #2563eb; /* azul-600 */
                outline-offset: 2px;
            }
        </style>

        <div class="flex flex-wrap gap-3">
            @foreach ($colores as $color)
                <label class="color-box relative rounded-full cursor-pointer">
                    <input type="radio" name="color" value="{{ $color->color }}" class="sr-only"
                        @checked(old('color', $coordinacion->color ?? '') == $color->color)>
                    <span class="w-9 h-9 rounded-full border border-gray-400 block"
                        style="background-color: {{ $color->color }}"></span>
                </label>
            @endforeach
        </div>

    </div>


    <div class="text-center">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            {{ $method === 'POST' ? 'Registrar Coordinación' : 'Actualizar Coordinación' }}
        </button>
    </div>
</form>
