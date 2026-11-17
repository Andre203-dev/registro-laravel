@extends('layouts.app')

@section('titulo', 'Acceso del operador')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow p-4">

            <h2 class="text-center mb-4">Acceso del operador</h2>

            <form action="{{ route('login.store') }}" method="POST" novalidate data-validar>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" id="email" name="email" required autofocus autocomplete="username"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>

        </div>
    </div>
</div>
@endsection
