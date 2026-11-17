# Guion de defensa: Registro académico

**Caso único ficticio:** Ana Demo · identidad `0801200099999` · `ana.demo@example.com` · 20 años · Ingeniería de Sistemas.
Antes de empezar, comprueba que ese correo **no** esté registrado.

**Tener abiertos:** la app (http://127.0.0.1:8000, sin sesión iniciada), el editor con el proyecto y el repositorio en GitHub.

---

## 1. Objetivo: el formulario y sus datos

- **Pantalla:** inicia sesión (momento 2) y abre «+ Nuevo estudiante».
- **Explicar:** «El registro guarda estudiantes. Mi formulario original tenía nombre, apellido, identidad y correo.
  En la migración agregué edad y programa. El programa sale de un catálogo cerrado, el correo es único
  y la edad es un entero desde 16».
- **Archivos:** `resources/views/estudiantes/_form.blade.php` (los 6 campos), `config/registro.php:18` (catálogo) y `:27` (edad mínima),
  `database/migrations/2026_11_17_151952_create_estudiantes_table.php:21` (correo `unique`).
- **Evolución:** comparar con `semana3/index.php`, que solo tenía `required` de HTML.

## 2. Acceso: el operador

- **Pantalla:** abre `/estudiantes` sin sesión → te manda a `/login`. Entra con el correo del operador.
- **Explicar:** «El operador no es un estudiante: está en la tabla `users`. Lo crea el seeder con datos del `.env`,
  y la clave se guarda como hash».
- **Archivos:** `routes/web.php:16` (`middleware('auth')`), `app/Http/Controllers/SesionController.php:30` (`Auth::attempt`) y `:37` (`regenerate`),
  `database/seeders/DocenteSeeder.php:28` (`firstOrCreate`) y `:32` (`Hash::make`).

## 3. Alta: ruta, validación, inserción y redirección

- **Pantalla:** llena el formulario con los datos de Ana y pulsa «Registrar». Aparece «Estudiante registrado correctamente.» y Ana en el listado.
- **Explicar:** «El formulario hace POST a `/estudiantes`. La ruta de recurso lo manda a `store`, que valida, inserta con Eloquent y redirige».
- **Archivos:** `resources/views/estudiantes/create.blade.php:13-14` (`action` + `@csrf`), `routes/web.php:26` (`Route::resource`),
  `app/Http/Controllers/EstudianteController.php:46` (`validate`), `:49` (`Estudiante::create`), `:51-52` (redirección con mensaje).

## 4. Consulta: buscar una parte del nombre

- **Pantalla:** escribe `An` en el buscador → aparece Ana. Luego escribe `Zzyzx` → «No se encontraron estudiantes…».
- **Explicar:** «La búsqueda es GET, así que la URL queda como `?buscar=An`. El controlador usa `LIKE` con parámetro enlazado; el texto no se mezcla con el SQL».
- **Archivos:** `resources/views/estudiantes/index.blade.php:16` (formulario GET), `EstudianteController.php:20-23` (`where … like`),
  `index.blade.php:66-67` (mensaje sin resultados).

## 5. Edición: se conserva el id

- **Pantalla:** «Editar» en Ana. Fíjate en el id del título (`Editar Estudiante #N`). Cambia el nombre a «Ana María» y guarda. El listado muestra el **mismo id**.
- **Explicar:** «El id va en la URL. Laravel busca la fila con route model binding y `update` hace `UPDATE … WHERE id = N`, así que no se crea otra fila.
  El correo no da conflicto porque la regla `unique` ignora la propia fila».
- **Archivos:** `resources/views/estudiantes/edit.blade.php:18-20` (`route('estudiantes.update', $estudiante)` + `@method('PUT')`),
  `EstudianteController.php:71` (`update(Request $request, Estudiante $estudiante)`), `:77` (`$estudiante->update`), `:115` (`->ignore(...)`).

## 6. Comprobación: un rechazo controlado

- **Pantalla:** «+ Nuevo estudiante» con correo `ana.demo@example.com` (repetido) y edad `15`.
  - Primero se ve la validación JavaScript («La edad mínima es 16 años»).
  - Para demostrar el servidor: pon edad 16 y deja el correo repetido → el servidor responde «Ese correo ya está registrado.» y conserva lo escrito (`old()`).
- **Explicar:** «El JavaScript solo ayuda al usuario; quien decide es el servidor. Si alguien se salta el navegador, `validate` rechaza igual
  (lo demuestran las pruebas con un programa inventado)».
- **Archivos:** `public/js/validacion.js:14-18`, `EstudianteController.php:115` (unique), `:119-120` (`integer`, `min`), `:124` (`Rule::in`),
  `_form.blade.php:12-13` (`old()` y `@error`). Pruebas: `tests/Feature/EstudianteTest.php`.

## 7. Eliminación: cancelar primero y luego confirmar

- **Pantalla:** «Eliminar» en Ana → se ve la pantalla de confirmación → **Cancelar** → Ana sigue en el listado.
  Repite «Eliminar» → **Sí, eliminar** → «Estudiante eliminado correctamente.» y Ana desaparece.
- **Explicar:** «El botón del listado solo es un enlace GET a la confirmación, que no borra nada. El borrado es un formulario DELETE con token CSRF y afecta solo a ese id».
- **Archivos:** `index.blade.php:60` (enlace), `routes/web.php:22-23` (ruta GET de confirmación),
  `resources/views/estudiantes/confirmar.blade.php:24-26` (formulario DELETE) y `:30` (Cancelar), `EstudianteController.php:86` y `:97`.

## 8. Cierre: logout y volver a pedir el listado

- **Pantalla:** pulsa «Salir» → «Sesión cerrada.». Escribe `/estudiantes` en la barra → vuelve a pedir login.
- **Explicar:** «`logout` saca al usuario, `invalidate` borra la sesión y `regenerateToken` cambia el token CSRF. Sin sesión, el middleware `auth` bloquea todo el CRUD».
- **Archivos:** `resources/views/layouts/app.blade.php:35-36` (formulario POST de salida), `SesionController.php:44-46`, `routes/web.php:16`.

---

## Recorrido de una petición (alta de Ana)

```
Blade (create.blade.php: <form method="POST" action="/estudiantes"> + @csrf)
   │  POST /estudiantes
   ▼
Middleware web: sesión + verificación CSRF
   ▼
Middleware auth  ◄── aquí se exige sesión: sin login → redirect /login
   ▼
Ruta (routes/web.php: Route::resource → estudiantes.store)
   ▼
Controlador (EstudianteController@store)
   ▼
Validación ($request->validate: required, email, unique, integer|min:16, Rule::in)
   │   └── falla → redirect back con errores y old()  (no se toca la base)
   ▼
Eloquent (Estudiante::create($datos), solo los campos de $fillable)
   ▼
MySQL (INSERT INTO estudiantes … con parámetros)
   ▼
Redirección (redirect()->route('estudiantes.index')->with('ok', …))
   ▼
Listado (GET /estudiantes → index → index.blade.php muestra los datos con {{ }})
```

**Dónde actúa `auth`:** antes de llegar a la ruta y al controlador (`routes/web.php:16`). Envuelve todas las rutas del CRUD y el logout.
Las rutas de login usan `guest` (`routes/web.php:8`).
