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
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach ($colores as $color)
                @php $seleccionado = old('color', $coordinacion->color ?? '') === $color->color; @endphp
                <label class="relative cursor-pointer">
                    <input type="radio" name="color" value="{{ $color->color }}" class="sr-only" @checked($seleccionado)>
                    <span class="w-8 h-8 rounded-full inline-block border-2 {{ $seleccionado ? 'border-black ring-2 ring-offset-2' : 'border-gray-300' }}"
                          style="background-color: {{ $color->color }};"></span>
                    @if ($seleccionado)
                        <span class="absolute top-0 right-0 text-xs font-bold text-white">✔</span>
                    @endif
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
