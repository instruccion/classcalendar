<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 flex items-center justify-center h-screen">

    <div class="text-center space-y-6">
        <h1 class="text-4xl font-bold text-gray-800">¡Bienvenido a Cursos Laser!</h1>
        <p class="text-lg text-gray-600">Accede para gestionar tus cursos y usuarios.</p>

        @auth
            <a href="{{ url('/dashboard') }}"
               class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                Ir al Dashboard
            </a>
        @else
            <div class="space-x-4">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}"
                       class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                        Iniciar sesión
                    </a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                        Registrarse
                    </a>
                @endif
            </div>
        @endauth
    </div>

</body>
</html>
