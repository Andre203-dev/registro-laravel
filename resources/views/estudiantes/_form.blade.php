{{--
    Campos compartidos por "crear" y "editar".
    old() devuelve lo que el usuario escribió si la validación falla;
    si no hay valor anterior, usa el dato guardado ($estudiante) o vacío.
--}}
@php($e = $estudiante ?? null)

<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre" maxlength="100" required
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $e?->nombre) }}">
    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="apellido" class="form-label">Apellido</label>
    <input type="text" id="apellido" name="apellido" maxlength="100" required
           class="form-control @error('apellido') is-invalid @enderror"
           value="{{ old('apellido', $e?->apellido) }}">
    @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="identidad" class="form-label">Número de Identidad</label>
    <input type="text" id="identidad" name="identidad" maxlength="20" required
           class="form-control @error('identidad') is-invalid @enderror"
           value="{{ old('identidad', $e?->identidad) }}">
    @error('identidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="correo" class="form-label">Correo</label>
    <input type="email" id="correo" name="correo" maxlength="100" required
           class="form-control @error('correo') is-invalid @enderror"
           value="{{ old('correo', $e?->correo) }}">
    @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="edad" class="form-label">Edad</label>
    <input type="number" id="edad" name="edad" required step="1"
           min="{{ config('registro.edad_minima') }}" max="{{ config('registro.edad_maxima') }}"
           class="form-control @error('edad') is-invalid @enderror"
           value="{{ old('edad', $e?->edad) }}">
    <div class="form-text">Número entero desde {{ config('registro.edad_minima') }} años.</div>
    @error('edad') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="programa" class="form-label">Programa</label>
    <select id="programa" name="programa" required
            class="form-select @error('programa') is-invalid @enderror">
        <option value="">Selecciona un programa…</option>
        @foreach ($programas as $programa)
            <option value="{{ $programa }}" @selected(old('programa', $e?->programa) === $programa)>
                {{ $programa }}
            </option>
        @endforeach
    </select>
    @error('programa') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
