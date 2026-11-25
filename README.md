# RedEMC - Prueba de desarrollo

Formulario de ingreso de datos y listado paginado, sin recargar la página.

**Proyecto publicado:** https://redemc-prueba-de-desarrollo-production.up.railway.app

**Stack:** HTML, CSS, Bootstrap 5, JavaScript/jQuery, PHP y MySQL.

## Estructura

| Archivo | Función |
| --- | --- |
| `index.php` | Página con el formulario y el listado |
| `api/personas.php` | Recibe los pedidos Ajax: listar (GET) y guardar (POST) |
| `src/Database.php` | Conexión PDO con variables de entorno |
| `src/PersonaValidator.php` | Reglas de validación, usadas por PHP y por JavaScript |
| `src/PersonaRepository.php` | Consultas a la tabla `personas` |
| `assets/` | Estilos, JavaScript, Bootstrap y jQuery |
| `sql/schema.sql` | Estructura de la tabla |

## Decisiones principales

- **Validación:** se hace en el navegador y se repite en el servidor. Las expresiones regulares están definidas una sola vez, en PHP.
- **Nombre:** acepta letras latinas (con tildes, ñ, ü, ç) y espacios. Los espacios repetidos se unifican.
- **Datos únicos:** además de la consulta previa, los índices `UNIQUE` evitan duplicados si llegan dos envíos a la vez. El email se guarda en minúsculas.
- **Seguridad:** consultas preparadas (inyección SQL), salida escapada (XSS), Content Security Policy, rechazo de envíos que no sean JSON (CSRF), credenciales solo en variables de entorno y errores internos solo en el log.
- **Listado:** paginado por Ajax de 8 registros, del más nuevo al más viejo.
- **Fecha:** la asigna MySQL al insertar, en UTC, y se muestra en hora de Uruguay.

## Deploy en Railway

1. En Railway, crear un proyecto nuevo con **Deploy from GitHub repo** y elegir este repositorio. Railway detecta PHP por el `index.php` de la raíz.
2. En el mismo proyecto: **Create > Database > MySQL**.
3. En el servicio de la aplicación, pestaña **Variables**, agregar (si la base no se llama `MySQL`, cambiar ese nombre):

   ```
   MYSQLHOST=${{MySQL.MYSQLHOST}}
   MYSQLPORT=${{MySQL.MYSQLPORT}}
   MYSQLUSER=${{MySQL.MYSQLUSER}}
   MYSQLPASSWORD=${{MySQL.MYSQLPASSWORD}}
   MYSQLDATABASE=${{MySQL.MYSQLDATABASE}}
   ```

   En **Settings > Build > Custom Build Command**, poner `install-php-extensions pdo_mysql`, que instala la extensión de PHP que se conecta con MySQL. (La variable `RAILPACK_PHP_EXTENSIONS` no sirve acá: Railpack la ignora si no hay `composer.json`.)
4. Crear la tabla: en el servicio de la aplicación, **Settings > Deploy > Pre-Deploy Command**, poner este comando, que ejecuta `sql/schema.sql` sobre la base:

   ```
   php -r 'require "src/Database.php"; Database::conectar()->exec(file_get_contents("sql/schema.sql"));'
   ```

5. Aplicar los cambios con **Deploy**. Con la tabla ya creada, el comando se puede quitar.
6. En el servicio de la aplicación, **Settings > Networking > Generate Domain** para obtener el link público.
7. Desde ese link, ingresar un dato y comprobar que aparece primero en el listado y que sigue al recargar.
