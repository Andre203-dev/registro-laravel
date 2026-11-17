<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    // Laravel deduciría "estudiantes" por sí solo; se deja explícito para la defensa.
    protected $table = 'estudiantes';

    // Solo estos campos se pueden asignar en bloque con create()/update().
    // Un campo extra enviado en la petición (p. ej. "id") se ignora.
    protected $fillable = [
        'nombre',
        'apellido',
        'identidad',
        'correo',
        'edad',
        'programa',
    ];

    protected function casts(): array
    {
        return [
            'edad' => 'integer',
        ];
    }
}
