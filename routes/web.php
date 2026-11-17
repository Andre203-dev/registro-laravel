<?php

use App\Http\Controllers\EstudianteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/estudiantes');

// Paso 1 de la eliminación: GET solo MUESTRA la confirmación (no borra nada).
Route::get('estudiantes/{estudiante}/eliminar', [EstudianteController::class, 'confirmarEliminar'])
    ->name('estudiantes.confirmar');

// Rutas de recurso: index, create, store, edit, update, destroy (paso 2: DELETE borra).
Route::resource('estudiantes', EstudianteController::class)
    ->except(['show'])
    ->parameters(['estudiantes' => 'estudiante']);
