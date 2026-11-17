# Registro académico (Laravel)

Proyecto final de **Programación Web II**. Es la migración a Laravel del formulario PHP nativo + MySQL
(carpeta `semana3`). Un **operador/docente** inicia sesión y gestiona el registro de **estudiantes**
(alta, listado con búsqueda, edición y eliminación con confirmación).

| Dato | Regla |
|---|---|
| Nombre, apellido | Obligatorios, máximo 100 caracteres |
| Número de identidad | Obligatorio, máximo 20 caracteres |
| Correo | Obligatorio, formato de correo, **único** |
| Edad | Entero entre **16** y 99 (15 se rechaza) |
| Programa | Solo valores del catálogo de `config/registro.php` |

## Requisitos

- **PHP 8.3 o superior** con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip` y `curl`.
- **Composer 2**.
- **MySQL o MariaDB** (por ejemplo, el de XAMPP).
- Git.

> **Si usas XAMPP con PHP 8.0 o 8.1:** Laravel 13 no funciona con esa versión. Instala un PHP 8.3 aparte
> (por ejemplo, el ZIP "VS16 x64 Non Thread Safe" de https://windows.php.net/download/, descomprimido en `C:\php83`,
> con `php.ini-development` copiado como `php.ini` y esas extensiones activadas). Luego usa ese PHP en todos los comandos:
> `C:\php83\php.exe C:\composer\composer.phar install` y `C:\php83\php.exe artisan ...`.
> Comprueba la versión con `php -v`.

## Configuración local

Copia `.env.example` a `.env` (lo hace el arranque de abajo) y ajusta estas variables:

| Variable | Qué poner |
|---|---|
| `DB_HOST`, `DB_PORT` | Normalmente `127.0.0.1` y `3306` |
| `DB_DATABASE` | La base que vas a crear, p. ej. `registro_laravel` |
| `DB_USERNAME`, `DB_PASSWORD` | Tu usuario de MySQL (en XAMPP suele ser `root` sin clave) |
| `DOCENTE_EMAIL`, `DOCENTE_CLAVE` | Correo y clave del operador (ver abajo) |

`.env` **nunca se sube a Git**: tiene la `APP_KEY` y tus claves locales.

## Base de datos

Crea una base vacía (desde phpMyAdmin o la consola):

```sql
CREATE DATABASE registro_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Solo si vas a correr las pruebas automáticas:
CREATE DATABASE registro_laravel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Con XAMPP: `C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE registro_laravel"`.

## Preparación del operador

El operador (docente) no es un estudiante: es el usuario que inicia sesión. Antes del seeder,
escribe en **tu `.env` local** un correo y una clave propios:

```dotenv
DOCENTE_EMAIL=docente@ejemplo.test
DOCENTE_CLAVE=una-clave-solo-tuya
```

`php artisan db:seed --class=DocenteSeeder` crea ese usuario con la clave **hasheada**.
Usa `firstOrCreate`: **si el correo ya existe, no reemplaza su clave**. Para cambiarla, usa otro correo
o borra ese usuario de la tabla `users` y vuelve a ejecutar el seeder.

## Arranque

```powershell
git clone <URL-del-repositorio> registro-defensa
cd registro-defensa
composer install
Copy-Item .env.example .env      # en Linux/Mac: cp .env.example .env
# configurar .env: DB_*, DOCENTE_EMAIL y DOCENTE_CLAVE
php artisan key:generate
php artisan config:clear
php artisan migrate
php artisan db:seed --class=DocenteSeeder
php artisan serve
```

Abre http://127.0.0.1:8000 e inicia sesión con `DOCENTE_EMAIL` / `DOCENTE_CLAVE`.

## Pruebas

Los casos de prueba y sus resultados están en [`registro-pruebas.md`](registro-pruebas.md).
Las pruebas automáticas usan la base `registro_laravel_test` (ver `phpunit.xml`), nunca la principal:

```powershell
php artisan test
```

## Documentos de la entrega

- [`registro-pruebas.md`](registro-pruebas.md): casos de prueba.
- [`guion-defensa.md`](guion-defensa.md): recorrido para la presentación.
- [`preguntas-defensa.md`](preguntas-defensa.md): preguntas esperadas y cambios en vivo.
- [`entrega-final.md`](entrega-final.md): estado, aportes y cierre reflexivo.
