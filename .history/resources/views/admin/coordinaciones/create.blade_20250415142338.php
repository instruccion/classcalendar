<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Registrar Coordinación</h2>
    </x-slot>

    @include('admin.coordinaciones.partials.form', ['coordinacion' => null, 'action' => route('admin.coordinaciones.store'), 'method' => 'POST'])
</x-app-layout>
