# RedEMC - Prueba de desarrollo

Formulario de ingreso de datos y listado paginado, sin recargar la página.

**Proyecto publicado:** _PENDIENTE: link de Railway_

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
   RAILPACK_PHP_EXTENSIONS=pdo_mysql
   ```

   La última instala la extensión de PHP que se conecta con MySQL.
4. Crear la tabla: en el servicio MySQL, **Settings > Networking**, habilitar el acceso público, conectarse con un cliente MySQL usando los datos de `MYSQL_PUBLIC_URL` y ejecutar `sql/schema.sql`. Después, deshabilitar el acceso público.
5. Desplegar los cambios con **Deploy**.
6. En el servicio de la aplicación, **Settings > Networking > Generate Domain** para obtener el link público.
7. Desde ese link, ingresar un dato y comprobar que aparece primero en el listado y que sigue al recargar.
