<?php

declare(strict_types=1);

/** Crea la conexión PDO a partir de las variables de entorno. */
final class Database
{
    public static function conectar(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            self::variable('MYSQLHOST'),
            self::variable('MYSQLPORT', '3306'),
            self::variable('MYSQLDATABASE')
        );

        $pdo = new PDO($dsn, self::variable('MYSQLUSER'), self::variable('MYSQLPASSWORD', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]);

        // Las fechas se guardan en UTC; el navegador las muestra en hora de Uruguay.
        $pdo->exec("SET time_zone = '+00:00'");

        return $pdo;
    }

    private static function variable(string $nombre, ?string $porDefecto = null): string
    {
        $valor = getenv($nombre);

        if ($valor !== false && $valor !== '') {
            return $valor;
        }

        if ($porDefecto === null) {
            throw new RuntimeException("Falta la variable de entorno $nombre");
        }

        return $porDefecto;
    }
}
