# Registro de pruebas: Registro académico (Laravel)

**Fecha de ejecución:** 17/11/2026 · **Entorno:** PHP 8.3.36, Laravel 13.43, MariaDB 10.4 (XAMPP)

**Cómo se probó:**

- **Pruebas Feature** (`php artisan test`) sobre la base **`registro_laravel_test`**, que se vacía en cada prueba con `RefreshDatabase`. Archivos: `tests/Feature/EstudianteTest.php` y `tests/Feature/SesionTest.php`. Resultado: **19 pruebas, 92 comprobaciones, todas correctas**.
- **Peticiones reales** con `curl` contra `php artisan serve` y la base `registro_laravel` (casos 10, 11 y la eliminación).
- Laravel responde a una validación fallida con una **redirección 302 y errores en la sesión**, no con un código 4xx. Por eso cada caso comprueba **el efecto en la base** (`assertDatabaseCount`, `assertDatabaseHas` y `assertDatabaseMissing`) y no solo el código HTTP.

| # | Caso | Entrada | Resultado esperado | Resultado obtenido | Estado | Observación |
|---|---|---|---|---|---|---|
| 1 | Alta válida con correo nuevo | Ana / Prueba / 0801200012345 / ana.prueba@example.com / 20 / Ingeniería de Sistemas | Una fila y regreso al listado | Redirección a `/estudiantes`, sin errores; la tabla tiene exactamente 1 fila con ese correo | Correcto | `test_alta_valida_crea_una_fila_y_regresa_al_listado` |
| 2 | Correo ya registrado | Mismo correo que una fila existente | Error de validación; no hay duplicado | Vuelve al formulario con «Ese correo ya está registrado.»; la tabla sigue con 1 fila | Correcto | Regla `Rule::unique` + índice `UNIQUE` en la migración |
| 3 | Edad 15 o valor no entero | `15`, `17.5`, `diecisiete`, vacío | Rechazo; no se guarda | Las 4 entradas dan error en `edad`; la tabla queda con 0 filas | Correcto | Reglas `integer` y `min:16` |
| 4 | Programa inventado en una petición controlada | POST directo con `programa=Astrología Avanzada` (sin pasar por el `<select>`) | Rechazo por la regla del catálogo | Error «Selecciona un programa del catálogo.»; 0 filas | Correcto | `Rule::in(config('registro.programas'))` |
| 5 | Editar conservando el propio correo | PUT a la fila de Ana con el mismo correo y el nombre «Ana María» | Guarda sin conflicto falso | Sin errores; el mismo `id` tiene el nuevo nombre; sigue habiendo 1 fila | Correcto | `->ignore($estudiante?->id)` |
| 6 | Id de una fila inexistente | GET edit, PUT y DELETE a `id + 999` | No modifica otra fila ni crea registro | Las 3 peticiones devuelven 404; Ana sigue igual; no se crea el «intruso» | Correcto | Route model binding. Extra (6b): un campo `id` falso en el formulario se ignora gracias a `$fillable` |
| 7 | Búsqueda sin coincidencias | `/estudiantes?buscar=Zzyzx` | Resultado vacío comprensible | 200 con «No se encontraron estudiantes cuyo nombre o apellido contenga «Zzyzx».» | Correcto | Extra (7b): `buscar=An` encuentra a Ana y no a Bruno |
| 8 | Cancelar confirmación de eliminación | GET `/estudiantes/{id}/eliminar` y después «Cancelar» (enlace al listado) | La fila sigue guardada | La pantalla de confirmación se muestra; la fila sigue en la base. Con petición real: la tabla mantiene 1 fila después del GET | Correcto | El GET nunca borra |
| 9 | Confirmar borrado de una fila ficticia | DELETE de una fila habiendo dos | Desaparece solo esa fila | Redirección al listado; esa fila ya no existe y la otra sigue. Con petición real: de 1 a 0 filas | Correcto | `$estudiante->delete()` borra solo por id |
| 10 | Ruta protegida sin sesión o tras salir | GET listado/crear/editar/eliminar, POST y DELETE sin sesión; luego login → logout → listado | Se exige acceso | Todas redirigen a `/login`; no se crea ni borra nada. Después de `logout`, el listado vuelve a pedir acceso | Correcto | Middleware `auth`. También se probó con `curl` real |
| 11 | Volver a abrir la aplicación | Alta real de «Persistencia Prueba», detener `php artisan serve`, arrancarlo de nuevo, iniciar sesión y abrir el listado | Los registros persisten en MySQL | La fila aparece después del reinicio | Correcto | Petición real sobre `registro_laravel`. La fila de prueba se borró después con el flujo de dos pasos |
| 12 | Nombre con signos especiales | Nombre `<b>Ana</b> & Co` | Blade lo muestra como texto | En la base se guarda tal cual; el HTML del listado contiene `&lt;b&gt;Ana&lt;/b&gt; &amp; Co` y no contiene `<b>Ana</b>` | Correcto | `{{ }}` escapa la salida |

## Pruebas adicionales de acceso

| # | Caso | Resultado obtenido | Estado |
|---|---|---|---|
| A1 | Login con la clave correcta | Redirección a `/estudiantes`, usuario autenticado | Correcto |
| A2 | Login con la clave incorrecta | Vuelve al login con «Correo o contraseña incorrectos.» y sin sesión | Correcto |
| A3 | `DocenteSeeder` ejecutado dos veces con claves distintas | Un solo operador; la clave guardada es un hash y sigue siendo la primera | Correcto |
| A4 | Copia limpia (clon nuevo + README, base `registro_defensa`) | `composer install`, migraciones, seeder, login, alta y las 19 pruebas funcionan | Correcto |

## Incidencia durante las pruebas

Al dar de alta con `curl` desde Git Bash, el programa «Psicología» fue **rechazado por el catálogo**.
La causa era que la terminal de Windows no enviaba la «í» en UTF-8. No era un fallo de la app: un navegador
siempre envía UTF-8. Al enviar el valor codificado (`Psicolog%C3%ADa`), la alta funcionó y la base guardó
los bytes `C3 AD` correctos. No hubo que cambiar el código.

## Cómo repetir las pruebas

```powershell
# requiere la base registro_laravel_test creada (ver README)
php artisan test
php vendor/bin/phpunit --testdox   # lista legible de casos
```
