# Preguntas de la defensa

Respuestas cortas para explicar con tus palabras, con el archivo y el fragmento real del código.

---

### 1. ¿Cómo sabe Laravel qué acción atender?

Por la combinación de **método HTTP + URL**, que se busca en `routes/web.php`:

```php
// routes/web.php:26
Route::resource('estudiantes', EstudianteController::class)
    ->except(['show'])
    ->parameters(['estudiantes' => 'estudiante']);
```

Esa línea crea 6 rutas: `GET /estudiantes` → `index`, `GET /estudiantes/create` → `create`, `POST /estudiantes` → `store`,
`GET /estudiantes/{id}/edit` → `edit`, `PUT /estudiantes/{id}` → `update` y `DELETE /estudiantes/{id}` → `destroy`.
Puedes mostrarlas con `php artisan route:list --except-vendor`.
**Antes**, cada acción era un archivo distinto (`procesar.php`, `actualizar.php`…).

### 2. ¿Dónde se rechaza la edad 15?

En el **servidor**, dentro de las reglas del controlador:

```php
// app/Http/Controllers/EstudianteController.php:118-122
'edad' => [
    'required', 'integer',
    'min:'.config('registro.edad_minima'),   // config/registro.php:27 → 16
    'max:'.config('registro.edad_maxima'),
],
```

`integer` rechaza `17.5` o `abc`, y `min:16` rechaza 15. Si falla, `validate()` (línea 46) devuelve al formulario y el
`Estudiante::create` de la línea 49 **no se ejecuta**. El navegador también avisa (`public/js/validacion.js:14-18`),
pero eso es solo una ayuda: el servidor es el que manda.

### 3. ¿Qué permite `$fillable`?

Define **qué columnas se pueden llenar en bloque** con `create($datos)` o `update($datos)`:

```php
// app/Models/Estudiante.php:18-25
protected $fillable = ['nombre', 'apellido', 'identidad', 'correo', 'edad', 'programa'];
```

Si alguien agrega a la petición un campo que no está en la lista (por ejemplo `id`), Eloquent lo ignora.
Esto se probó en `tests/Feature/EstudianteTest.php` (`test_id_enviado_en_el_formulario_se_ignora`).

### 4. ¿Cómo se muestran los signos especiales?

Con `{{ }}`, que **escapa** el texto igual que `htmlspecialchars`:

```blade
{{-- resources/views/estudiantes/index.blade.php:51 --}}
<td>{{ $estudiante->nombre }}</td>
```

Un nombre `<b>Ana</b> & Co` se guarda tal cual, pero se envía como `&lt;b&gt;Ana&lt;/b&gt; &amp; Co`, y el navegador
lo muestra como texto, no en negrita. **Antes**, `semana3/index.php` hacía `echo $fila['nombre']` sin escapar.
(`{!! !!}` no escaparía; no se usa en el proyecto).

### 5. ¿Por qué editar no duplica?

Porque `update` modifica **la fila que ya existe** y la identifica por el id de la URL:

```php
// app/Http/Controllers/EstudianteController.php:71, 77
public function update(Request $request, Estudiante $estudiante)   // route model binding: busca por {estudiante}
$estudiante->update($datos);                                       // UPDATE estudiantes SET … WHERE id = N
```

Además, la regla `unique` ignora la propia fila para que conservar el mismo correo no dé un error falso:

```php
// EstudianteController.php:115
Rule::unique('estudiantes', 'correo')->ignore($estudiante?->id),
```

Si el id no existe, Laravel responde 404 y no toca nada.

### 6. ¿Qué hace `@csrf`?

Agrega un campo oculto `_token` con un valor secreto ligado a la sesión:

```blade
{{-- resources/views/estudiantes/create.blade.php:14 --}}
@csrf
```

Laravel rechaza (error 419) cualquier POST, PUT o DELETE que no traiga ese token. Así, otra página web no puede enviar
formularios a tu app aprovechando tu sesión (ataque CSRF).

### 7. ¿Por qué existe `@method`?

Porque **los formularios HTML solo envían GET o POST**. `@method('PUT')` o `@method('DELETE')` agrega un campo oculto
`_method`, y Laravel trata la petición como PUT o DELETE para que coincida con la ruta de recurso:

```blade
{{-- resources/views/estudiantes/edit.blade.php:19-20 --}}
@csrf
@method('PUT')
{{-- resources/views/estudiantes/confirmar.blade.php:25-26 --}}
@csrf
@method('DELETE')
```

### 8. ¿Cómo se conserva y se termina la sesión?

- **Se inicia** en `app/Http/Controllers/SesionController.php:30` con `Auth::attempt($credenciales)`, que compara la clave con el hash.
  Luego, en la línea `:37`, `$request->session()->regenerate()` cambia el id de sesión.
- **Se conserva** con una cookie que guarda el id de sesión. Los datos van en la tabla `sessions` de MySQL (`SESSION_DRIVER=database`).
  Por eso la sesión sobrevivió al reiniciar `php artisan serve` en la prueba 11.
- **Se protege** con el middleware `auth` (`routes/web.php:16`): sin sesión, todo el CRUD redirige a `/login`.
- **Se termina** en `SesionController.php:44-46`:

```php
Auth::logout();                          // quita al usuario
$request->session()->invalidate();       // borra los datos de la sesión
$request->session()->regenerateToken();  // nuevo token CSRF
```

### 9. ¿Qué se comparte en Git y qué queda local?

| Se comparte (en GitHub) | Queda local (en `.gitignore`) |
|---|---|
| Código: `app/`, `routes/`, `resources/`, `database/migrations`, `seeders`, `tests/` | `.env` (APP_KEY, claves de MySQL, `DOCENTE_CLAVE`) |
| `.env.example` con **valores ficticios** | `vendor/` (se reconstruye con `composer install`) |
| `composer.json` y `composer.lock` (versiones exactas) | `node_modules/` |
| README y documentos de entrega | Los datos de la base MySQL |

Lo que no se sube se reconstruye con los pasos del README.

---

## Cambios pequeños y reversibles que pueden pedirte en vivo

### Cambio A: texto de ayuda en el correo

- **Archivo:** `resources/views/estudiantes/_form.blade.php`, después de la línea 37 (el `@error('correo')`).
- **Agregar:** `<div class="form-text">Usa un correo institucional o personal válido.</div>`
- **Resultado esperado:** el texto gris aparece bajo el campo Correo en «Nuevo» y «Editar».
- **Comprobar:** recarga `/estudiantes/create`. **Revertir:** borra la línea (o `git checkout -- resources/views/estudiantes/_form.blade.php`).

### Cambio B: mensaje de validación del correo repetido

- **Archivo:** `app/Http/Controllers/EstudianteController.php:138`.
- **Cambiar:** `'correo.unique' => 'Ese correo ya está registrado.'` → `'correo.unique' => 'Ya existe un estudiante con ese correo.'`
- **Resultado esperado:** al registrar un correo repetido aparece el nuevo texto bajo el campo.
- **Comprobar:** intenta registrar un correo existente. Ojo: la prueba `test_correo_ya_registrado_se_rechaza_sin_duplicar` comprueba el texto exacto,
  así que `php artisan test` fallará hasta que también actualices el texto en `tests/Feature/EstudianteTest.php`. Es una buena demostración de que las pruebas detectan cambios.
- **Revertir:** `git checkout -- app/Http/Controllers/EstudianteController.php`.

### Cambio C: subir la edad mínima a 17

- **Archivo:** `config/registro.php:27` → `'edad_minima' => 17,`
- **Resultado esperado:** 16 ahora se rechaza («La edad mínima es 17 años.»); el `min` del input y el texto de ayuda cambian solos, porque leen la misma configuración.
- **Comprobar:** si usaste `php artisan config:cache`, ejecuta antes `php artisan config:clear`. Luego intenta registrar edad 16.
- **Revertir:** vuelve a `16`.
