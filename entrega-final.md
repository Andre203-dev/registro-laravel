# Entrega final: Registro académico (Encuentro 16)

| Dato | Valor |
|---|---|
| Asignatura | Programación Web II |
| Repositorio | https://github.com/Andre203-dev/registro-laravel (privado; acceso dado al docente) |
| Commit presentado | `<<COMPLETAR: git rev-parse --short HEAD después del último push>>` |
| Modalidad | <<COMPLETAR: individual / equipo>> |
| Fecha | 17/11/2026 |

## Integrantes y aportes

| Integrante | Aporte | Dónde se ve |
|---|---|---|
| Johan Andrés Fernández Peña | Versión PHP nativa (`semana3`: formulario, CRUD con mysqli) y migración a Laravel | Historial: `git log --author="Johan"` (todos los commits del repositorio) |
| <<COMPLETAR si es en equipo>> | <<COMPLETAR>> | <<COMPLETAR>> |

> La migración a Laravel se hizo con ayuda de un asistente de IA (Claude Code), y los commits quedaron con el autor indicado arriba.
> <<COMPLETAR: si tu docente pide declarar el uso de IA, indícalo aquí con tus palabras>>

## Estado real

**Funciona (verificado con pruebas, ver `registro-pruebas.md`):**

- Login y logout del operador; todo el CRUD protegido con `auth`.
- Alta con validación en el servidor: obligatorios, correo válido y único, edad entera ≥ 16, programa del catálogo.
- Listado con búsqueda por parte del nombre o del apellido y mensaje cuando no hay resultados.
- Edición que conserva el id y no da un conflicto falso con el propio correo.
- Eliminación en dos pasos (confirmación GET, borrado DELETE de una sola fila).
- Datos persistentes en MySQL; signos especiales mostrados como texto.
- 19 pruebas automáticas (92 comprobaciones) correctas; arranque probado desde una copia limpia siguiendo el README.

**Pendiente o limitaciones conocidas:**

- El listado no tiene paginación (con muchos registros se cargan todos).
- Hay un solo rol (operador); no hay recuperación de contraseña.
- Laravel 13 exige PHP 8.3, y el XAMPP del equipo trae PHP 8.0. Se usa un PHP 8.3 portátil en `C:\php83` para Laravel (XAMPP sigue sirviendo MySQL).
- Los 4 registros de la base antigua (`estudiantes.alumnos`) **no se migraron**: la base de Laravel es nueva y solo tiene datos ficticios.

## Cierre reflexivo

### Una dificultad resuelta

- **Síntoma:** `composer create-project laravel/laravel` falló: no se podía instalar ninguna versión de Laravel.
- **Causa:** el XAMPP del equipo tiene **PHP 8.0.30**. Laravel 10 o superior pide PHP 8.1+, y Composer bloquea todas las versiones de Laravel 9
  porque tienen avisos de seguridad publicados.
- **Cambio:** en lugar de instalar una versión vulnerable, se descargó el PHP 8.3 oficial (ZIP de windows.php.net, con el hash verificado) en `C:\php83`,
  se activaron `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` y `zip`, y se creó el proyecto con Laravel 13 usando ese PHP. XAMPP no se modificó.
- **Prueba:** `php artisan --version` → Laravel 13.43; migraciones aplicadas sobre MySQL de XAMPP; 19 pruebas correctas.
- *(Otra incidencia menor: una alta con `curl` rechazó «Psicología» porque la terminal no enviaba UTF-8; se explica en `registro-pruebas.md`)*.
- <<COMPLETAR: si prefieres contar una dificultad que viviste tú en semanas anteriores, cámbiala aquí>>

### Una decisión técnica justificada

**Eliminación en dos pasos (GET muestra, DELETE borra) en lugar del `confirm()` de JavaScript de `listar.php`.**
Un GET nunca debe cambiar datos: un enlace, el historial o un rastreador podrían repetirlo. La pantalla de confirmación
muestra exactamente qué fila se va a borrar y permite cancelar sin tocar la base. El borrado real es un formulario DELETE
con token CSRF, que solo se envía desde la propia app. Además funciona aunque el navegador tenga JavaScript desactivado.
(`routes/web.php:22`, `resources/views/estudiantes/confirmar.blade.php`, `EstudianteController.php:86-101`).

### Una mejora futura concreta

**Paginación del listado.** Habría que cambiar en `EstudianteController@index` el `->get()` por `->paginate(10)->withQueryString()`
(para conservar `?buscar=`) y agregar `{{ $estudiantes->links() }}` en `resources/views/estudiantes/index.blade.php`. El conteo de resultados pasaría a `$estudiantes->total()`.
<<COMPLETAR: o elige otra mejora que te interese más (roles, exportar a CSV, actualizar XAMPP)>>

### Qué piezas permiten registrar, recuperar y proteger la información

- **Registrar:** formulario Blade (`create.blade.php` + `_form.blade.php`) → `EstudianteController@store` → `validate()` → `Estudiante::create` (`$fillable`) → tabla `estudiantes` (migración con `correo` UNIQUE).
- **Recuperar:** `EstudianteController@index` (Eloquent + búsqueda `LIKE` con parámetros) y `@edit` (route model binding) → vistas con `{{ }}`.
- **Proteger:**
  - middleware `auth` (`routes/web.php:16`) y `SesionController` (hash de la clave, `regenerate`, `invalidate`);
  - `@csrf` en cada formulario;
  - validación en el servidor y `$fillable`;
  - Eloquent con parámetros (evita la inyección SQL que tenía `procesar.php`);
  - escape con `{{ }}`;
  - `.env` fuera de Git.

<<COMPLETAR: una o dos frases propias sobre qué aprendiste en la evolución de HTML a Laravel>>
