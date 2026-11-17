<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Crea el operador/docente leyendo DOCENTE_EMAIL y DOCENTE_CLAVE del .env.
 *
 *   php artisan db:seed --class=DocenteSeeder
 *
 * firstOrCreate: si el correo YA existe, no hace nada (no reemplaza su clave).
 */
class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('registro.docente.email');
        $clave = config('registro.docente.clave');

        if (blank($email) || blank($clave)) {
            throw new RuntimeException('Define DOCENTE_EMAIL y DOCENTE_CLAVE en tu archivo .env.');
        }

        $docente = User::firstOrCreate(
            ['email' => $email],                 // busca por correo
            [                                    // solo se usa si hay que crearlo
                'name' => 'Operador docente',
                'password' => Hash::make($clave), // se guarda el hash, nunca la clave
            ],
        );

        $this->command?->info($docente->wasRecentlyCreated
            ? "Operador creado: {$email}"
            : "El operador {$email} ya existía: no se cambió su contraseña.");
    }
}
