@extends('layouts.app')

@section('titulo', 'Lista de Estudiantes')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow p-4">

            <h2 class="text-center mb-4">Lista de Estudiantes</h2>

            <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
                <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">+ Nuevo estudiante</a>

                {{-- Búsqueda por GET: la URL queda como /estudiantes?buscar=texto --}}
                <form action="{{ route('estudiantes.index') }}" method="GET" class="d-flex gap-2" role="search">
                    <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Buscar por nombre o apellido" aria-label="Buscar">
                    <button type="submit" class="btn btn-outline-primary">Buscar</button>
                    @if ($buscar !== '')
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            @if ($buscar !== '')
                <p class="text-muted">
                    {{ $estudiantes->count() }} resultado(s) para «{{ $buscar }}».
                </p>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Identidad</th>
                            <th>Correo</th>
                            <th>Edad</th>
                            <th>Programa</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    {{-- {{ }} escapa el texto (como htmlspecialchars): <b> se ve como texto, no como negrita --}}
                    @forelse ($estudiantes as $estudiante)
                        <tr>
                            <td>{{ $estudiante->id }}</td>
                            <td>{{ $estudiante->nombre }}</td>
                            <td>{{ $estudiante->apellido }}</td>
                            <td>{{ $estudiante->identidad }}</td>
                            <td>{{ $estudiante->correo }}</td>
                            <td>{{ $estudiante->edad }}</td>
                            <td>{{ $estudiante->programa }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning btn-sm">Editar</a>
                                {{-- Solo lleva a la confirmación; todavía no borra --}}
                                <a href="{{ route('estudiantes.confirmar', $estudiante) }}" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                @if ($buscar !== '')
                                    No se encontraron estudiantes cuyo nombre o apellido contenga «{{ $buscar }}».
                                @else
                                    No hay estudiantes registrados.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
