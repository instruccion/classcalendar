<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Auditorías
        </h2>
    </x-slot>

    <div class="py-4 max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800">Registros de Auditoría</h1>

        <!-- Tabla de Auditorías -->
        <div class="overflow-x-auto bg-white shadow rounded-lg mt-6">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Acción</th>
                        <th class="px-4 py-2">Descripción</th>
                        <th class="px-4 py-2">Usuario</th>
                        <th class="px-4 py-2">IP</th>
                        <th class="px-4 py-2">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditorias as $auditoria)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $auditoria->accion }}</td>
                            <td class="px-4 py-2">{{ $auditoria->descripcion }}</td>
                            <td class="px-4 py-2">{{ $auditoria->user->name }}</td>
                            <td class="px-4 py-2">{{ $auditoria->ip }}</td>
                            <td class="px-4 py-2">{{ $auditoria->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No se encontraron auditorías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
