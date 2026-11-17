<?php

use App\Http\Controllers\EstudianteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/estudiantes');

// Rutas de recurso: index, create, store, edit, update, destroy.
Route::resource('estudiantes', EstudianteController::class)
    ->except(['show'])
    ->parameters(['estudiantes' => 'estudiante']);
