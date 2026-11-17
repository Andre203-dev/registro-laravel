<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    /**
     * Listado (antes: listar.php e index.php).
     */
    public function index()
    {
        $estudiantes = Estudiante::orderBy('id')->get();

        return view('estudiantes.index', compact('estudiantes'));
    }

    /**
     * Formulario de alta (antes: la parte superior de index.php).
     */
    public function create()
    {
        return view('estudiantes.create', ['programas' => config('registro.programas')]);
    }

    /**
     * Guardar un estudiante nuevo (antes: procesar.php).
     */
    public function store(Request $request)
    {
        // Si algo no cumple, Laravel vuelve al formulario con los errores
        // y lo escrito (old()); el INSERT de abajo no llega a ejecutarse.
        $datos = $request->validate($this->reglas(), $this->mensajes());

        // INSERT con parámetros: Eloquent evita la inyección SQL que tenía procesar.php
        Estudiante::create($datos);

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante registrado correctamente.');
    }

    /**
     * Formulario de edición (antes: editar.php).
     * Route model binding: Laravel busca el estudiante por el {estudiante} de la URL
     * y responde 404 si no existe.
     */
    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', [
            'estudiante' => $estudiante,
            'programas' => config('registro.programas'),
        ]);
    }

    /**
     * Guardar cambios (antes: actualizar.php).
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        // Se pasa el estudiante para que la regla unique ignore su propio correo.
        $datos = $request->validate($this->reglas($estudiante), $this->mensajes());

        // UPDATE ... WHERE id = $estudiante->id : misma fila, mismo id, no se duplica.
        $estudiante->update($datos);

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante actualizado correctamente.');
    }

    /**
     * Borrar (antes: eliminar.php).
     */
    public function destroy(Estudiante $estudiante)
    {
        $estudiante->delete();

        return redirect()->route('estudiantes.index')
            ->with('ok', 'Estudiante eliminado correctamente.');
    }

    /**
     * Reglas de validación en el servidor.
     */
    private function reglas(?Estudiante $estudiante = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'identidad' => ['required', 'string', 'max:20'],
            'correo' => [
                'required', 'email', 'max:100',
                // Al editar, ignora la fila actual para no dar un "ya registrado" falso.
                Rule::unique('estudiantes', 'correo')->ignore($estudiante?->id),
            ],
            // integer rechaza "16.5" o "abc"; min rechaza 15.
            'edad' => [
                'required', 'integer',
                'min:'.config('registro.edad_minima'),
                'max:'.config('registro.edad_maxima'),
            ],
            // Solo se aceptan programas del catálogo cerrado (config/registro.php).
            'programa' => ['required', Rule::in(config('registro.programas'))],
        ];
    }

    /**
     * Mensajes en español para los errores.
     */
    private function mensajes(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no puede superar :max.',
            'correo.email' => 'Escribe un correo válido.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.min' => 'La edad mínima es :min años.',
            'edad.max' => 'La edad máxima es :max años.',
            'programa.in' => 'Selecciona un programa del catálogo.',
        ];
    }
}
