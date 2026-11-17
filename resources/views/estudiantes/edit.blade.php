@extends('layouts.app')

@section('titulo', 'Editar Estudiante')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow p-4">

            <h2 class="text-center mb-4">Editar Estudiante #{{ $estudiante->id }}</h2>

            {{--
                Antes: editar.php enviaba un <input hidden name="id"> a actualizar.php.
                Ahora el id va en la URL (/estudiantes/{id}) y Laravel busca la fila
                con route model binding. Los formularios HTML solo envían GET o POST,
                por eso @method('PUT') agrega un campo oculto _method=PUT.
            --}}
            <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST" novalidate data-validar>
                @csrf
                @method('PUT')

                @include('estudiantes._form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill">Guardar cambios</button>
                    <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary flex-fill">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
