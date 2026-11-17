<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Acceso del operador/docente (la versión PHP nativa no tenía login).
 */
class SesionController extends Controller
{
    public function create()
    {
        return view('sesion.login');
    }

    public function store(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Escribe el correo del operador.',
            'email.email' => 'Escribe un correo válido.',
            'password.required' => 'Escribe la contraseña.',
        ]);

        // Auth::attempt busca el usuario por email y compara la clave con su hash.
        if (! Auth::attempt($credenciales)) {
            return back()
                ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                ->onlyInput('email');
        }

        // Nuevo id de sesión tras iniciar sesión (evita la fijación de sesión).
        $request->session()->regenerate();

        return redirect()->intended(route('estudiantes.index'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();                          // quita al usuario de la sesión
        $request->session()->invalidate();       // borra los datos de la sesión
        $request->session()->regenerateToken();  // nuevo token CSRF

        return redirect()->route('login')->with('ok', 'Sesión cerrada.');
    }
}
