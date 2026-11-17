<?php

/*
|--------------------------------------------------------------------------
| Configuración del Registro académico
|--------------------------------------------------------------------------
|
| Aquí viven el catálogo cerrado de programas, la edad mínima y los datos
| del operador (docente). Los datos del operador se leen del archivo .env,
| que NUNCA se sube a Git.
|
*/

return [

    // Catálogo cerrado: el formulario solo ofrece estos programas y el
    // controlador rechaza cualquier otro valor con Rule::in().
    'programas' => [
        'Ingeniería de Sistemas',
        'Ingeniería Industrial',
        'Administración de Empresas',
        'Contaduría Pública',
        'Psicología',
    ],

    // Edad mínima permitida (15 se rechaza).
    'edad_minima' => 16,
    'edad_maxima' => 99,

    // Operador que crea el DocenteSeeder.
    'docente' => [
        'email' => env('DOCENTE_EMAIL'),
        'clave' => env('DOCENTE_CLAVE'),
    ],

];
