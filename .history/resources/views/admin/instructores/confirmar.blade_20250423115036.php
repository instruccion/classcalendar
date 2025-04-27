<x-guest-layout>
    <div class="max-w-xl mx-auto py-10">
        <h2 class="text-2xl font-bold mb-6 text-center">Confirmación de Curso</h2>

        <div class="mb-6">
            <p><strong>Curso:</strong> {{ $programacion->curso->nombre }}</p>
            <p><strong>Grupo:</strong> {{ $programacion->grupo->nombre }}</p>
            <p><strong>Fechas:</strong> {{ $programacion->fecha_inicio->format('d/m/Y') }} - {{ $programacion->fecha_fin->format('d/m/Y') }}</p>
            <p><strong>Horario:</strong> {{ $programacion->hora_inicio }} a {{ $programacion->hora_fin }}</p>
        </div>

        <form method="POST" action="{{ route('instructor.confirmar.enviar', $programacion->token_confirmacion) }}">
            @csrf
            <input type="hidden" name="accion" id="accion" value="">

            <div id="motivo-container" class="hidden mb-4">
                <label for="motivo_rechazo" class="block font-semibold mb-1">Motivo del Rechazo</label>
                <textarea name="motivo_rechazo" id="motivo_rechazo" rows="3"
                          class="w-full border px-4 py-2 rounded"></textarea>
            </div>

            <div class="flex justify-between">
                <button type="button" onclick="confirmar()" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">✅ Confirmar</button>
                <button type="button" onclick="rechazar()" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">❌ Rechazar</button>
            </div>
        </form>
    </div>

    <script>
        function confirmar() {
            document.getElementById('accion').value = 'confirmar';
            document.querySelector('form').submit();
        }

        function rechazar() {
            document.getElementById('motivo-container').classList.remove('hidden');
            document.getElementById('accion').value = 'rechazar';
        }
    </script>
</x-guest-layout>
