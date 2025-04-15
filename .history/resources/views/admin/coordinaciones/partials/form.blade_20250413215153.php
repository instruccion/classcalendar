@if ($errors->any())
    <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" class="bg-white shadow rounded p-4 space-y-4">
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
        <div class="flex flex-wrap gap-2">
            @foreach ($colores as $color)
                <label class="relative">
                    <input type="radio" name="color" value="{{ $color->color }}" class="sr-only"
                           @checked(old('color', $coordinacion->color ?? '') == $color->color)>
                    <span class="w-8 h-8 rounded-full inline-block border-2 border-gray-300"
                          style="background-color: {{ $color->color }};"></span>
                    @if (old('color', $coordinacion->color ?? '') == $color->color)
                        <span class="absolute top-0 right-0 text-white text-xs font-bold">✔</span>
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
