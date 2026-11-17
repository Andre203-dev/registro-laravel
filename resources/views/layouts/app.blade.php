<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Registro de Estudiantes') · {{ config('app.name') }}</title>

    {{-- Mismo Bootstrap 5.3.3 de la versión PHP nativa --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Mismo estilo de index.php / listar.php; la imagen ahora vive en public/img --}}
    <style>
        body{
            background-image: url('{{ asset('img/fondo-einstein.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .card{
            background: rgba(255,255,255,0.92);
            border-radius: 15px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Registro académico</a>
        @auth
            <div class="d-flex align-items-center gap-3">
                <span class="navbar-text text-light small">Operador: {{ auth()->user()->email }}</span>
                {{-- Salir es POST con @csrf: un simple enlace no puede cerrar la sesión de nadie --}}
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Salir</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<div class="container mt-5 mb-5">

    @if (session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @yield('contenido')

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/validacion.js') }}"></script>
@stack('scripts')
</body>
</html>
