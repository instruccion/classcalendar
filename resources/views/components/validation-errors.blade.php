{{-- resources/views/components/validation-errors.blade.php --}}
@props(['errors']) {{-- Define que puede recibir una propiedad 'errors', aunque usaremos la variable $errors global --}}

@if ($errors->any()) {{-- Verifica si hay algún error de validación en la sesión --}}
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-600 bg-red-100 border border-red-400 rounded-md p-4']) }}>
        <div class="font-bold">{{ __('Whoops! Something went wrong.') }}</div> {{-- Título del error (puedes traducir o cambiar) --}}

        <ul class="mt-3 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error) {{-- Itera sobre todos los errores --}}
                <li>{{ $error }}</li> {{-- Muestra cada error --}}
            @endforeach
        </ul>
    </div>
@endif
