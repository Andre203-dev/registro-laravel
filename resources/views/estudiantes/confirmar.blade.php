@extends('layouts.app')

@section('titulo', 'Confirmar eliminación')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow p-4 border-danger">

            <h2 class="text-center mb-4 text-danger">¿Eliminar este estudiante?</h2>

            <dl class="row mb-4">
                <dt class="col-sm-4">ID</dt>        <dd class="col-sm-8">{{ $estudiante->id }}</dd>
                <dt class="col-sm-4">Nombre</dt>    <dd class="col-sm-8">{{ $estudiante->nombre }} {{ $estudiante->apellido }}</dd>
                <dt class="col-sm-4">Identidad</dt> <dd class="col-sm-8">{{ $estudiante->identidad }}</dd>
                <dt class="col-sm-4">Correo</dt>    <dd class="col-sm-8">{{ $estudiante->correo }}</dd>
                <dt class="col-sm-4">Programa</dt>  <dd class="col-sm-8">{{ $estudiante->programa }}</dd>
            </dl>

            <p class="text-muted">Esta acción no se puede deshacer.</p>

            <div class="d-flex gap-2">
                {{-- Paso 2: el borrado real solo ocurre con este formulario DELETE --}}
                <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="flex-fill">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">Sí, eliminar</button>
                </form>
                {{-- Cancelar es un enlace normal: vuelve al listado sin tocar la base --}}
                <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary flex-fill">Cancelar</a>
            </div>

        </div>
    </div>
</div>
@endsection
