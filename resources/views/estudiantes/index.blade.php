@extends('layouts.app')

@section('titulo', 'Lista de Estudiantes')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="card shadow p-4">

            <h2 class="text-center mb-4">Lista de Estudiantes</h2>

            <div class="mb-3">
                <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">+ Nuevo estudiante</a>
            </div>

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
                                <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay estudiantes registrados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
