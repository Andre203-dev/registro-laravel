@extends('layouts.app')

@section('titulo', 'Registro de Estudiante')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow p-4">

            <h2 class="text-center mb-4">Registro de Estudiante</h2>

            {{-- Antes: action="procesar.php". Ahora: ruta con nombre estudiantes.store (POST /estudiantes) --}}
            <form action="{{ route('estudiantes.store') }}" method="POST" novalidate data-validar>
                @csrf

                @include('estudiantes._form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Registrar</button>
                    <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary flex-fill">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
