<x-app-layout>
    <x-slot name="header">Ordenar Bloque</x-slot>
    <h1>Ordenar Cursos para Grupo: {{ $grupo->nombre }}</h1>
    <p>Cursos Seleccionados (IDs):</p>
    <pre>{{ print_r(request('cursos_id'), true) }}</pre>
    <p>Objetos Curso:</p>
    <ul>
        @foreach($cursosOrdenados as $curso)
            <li>{{ $curso->id }} - {{ $curso->nombre }}</li>
        @endforeach
    </ul>
    {{-- Aquí iría la interfaz para reordenar (ej. drag and drop) y el form final --}}
</x-app-layout>
