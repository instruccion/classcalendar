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
                @php
                    $seleccionado = old('color', $coordinacion->color ?? '') === $color->color;
                @endphp
                <label class="relative cursor-pointer group">
                    <input type="radio" name="color" value="{{ $color->color }}" class="sr-only" @checked($seleccionado)>
                    <span class="w-10 h-10 rounded-full inline-block border-2 transition-all
                                {{ $seleccionado ? 'border-black ring-2 ring-offset-2 ring-black' : 'border-gray-300 group-hover:ring-2 group-hover:ring-blue-400' }}"
                        style="background-color: {{ $color->color }};"></span>
                    @if ($seleccionado)
                        <span class="absolute -top-1 -right-1 bg-black text-white text-xs font-bold rounded-full px-1">✔</span>
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
