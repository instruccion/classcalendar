<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Editar Coordinación</h2>
    </x-slot>

    @include('admin.coordinaciones.partials.form', [
    'coordinacion' => $coordinacion,
    'action' => route('coordinaciones.update', ['coordinacion' => $coordinacion->id]),
    'method' => 'PUT'
])

</x-app-layout>
