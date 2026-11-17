<?php

use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

// --- Acceso (solo para quien NO ha iniciado sesión) ---
Route::middleware('guest')->group(function () {
    Route::get('login', [SesionController::class, 'create'])->name('login');
    Route::post('login', [SesionController::class, 'store'])
        ->middleware('throttle:5,1')   // máximo 5 intentos por minuto
        ->name('login.store');
});

// --- Todo el registro exige sesión iniciada (middleware auth) ---
Route::middleware('auth')->group(function () {
    Route::redirect('/', '/estudiantes');

    Route::post('logout', [SesionController::class, 'destroy'])->name('logout');

    // Paso 1 de la eliminación: GET solo MUESTRA la confirmación (no borra nada).
    Route::get('estudiantes/{estudiante}/eliminar', [EstudianteController::class, 'confirmarEliminar'])
        ->name('estudiantes.confirmar');

    // Rutas de recurso: index, create, store, edit, update, destroy (paso 2: DELETE borra).
    Route::resource('estudiantes', EstudianteController::class)
        ->except(['show'])
        ->parameters(['estudiantes' => 'estudiante']);
});
