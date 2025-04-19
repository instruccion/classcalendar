<x-app-layout> {{-- CORREGIDO --}}
    {{-- Puedes añadir un slot de header si tu layout lo espera --}}
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Programaciones') }}
        </h2>
    </x-slot> --}}

    <div class="py-6"> {{-- Añadir padding o contenedor si es necesario --}}
         <h1 class="text-2xl font-bold mb-4">Programaciones</h1> {{-- Añadido mb-4 --}}

         {{-- Aquí iría el contenido principal de la página de programaciones --}}
         <div class="bg-white p-4 shadow rounded">
             <p>Contenido del listado o calendario de programaciones irá aquí.</p>
             <p>Por ahora, puedes usar el enlace del sidebar "Programar Curso" para ir al formulario.</p>
             <p class="mt-4"><a href="{{ route('admin.programaciones.create') }}" class="text-blue-600 hover:underline">Ir a Programar Curso →</a></p>
         </div>
    </div>

</x-app-layout> {{-- CORREGIDO --}}
