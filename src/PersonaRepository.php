<?php

declare(strict_types=1);

/** Acceso a la tabla personas. Todas las consultas con datos externos son preparadas. */
final class PersonaRepository
{
    public const POR_PAGINA = 8;

    private const ERROR_DUPLICADO = 1062;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{personas: list<array>, pagina: int, paginas: int, total: int} */
    public function listar(int $pagina): array
    {
        $total   = (int) $this->pdo->query('SELECT COUNT(*) FROM personas')->fetchColumn();
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $pagina  = min($pagina, $paginas);

        $consulta = $this->pdo->prepare(
            'SELECT id, nombre, email, telefono, fecha_insertado
               FROM personas
              ORDER BY id DESC
              LIMIT :limite OFFSET :desde'
        );
        $consulta->bindValue(':limite', self::POR_PAGINA, PDO::PARAM_INT);
        $consulta->bindValue(':desde', ($pagina - 1) * self::POR_PAGINA, PDO::PARAM_INT);
        $consulta->execute();

        return [
            'personas' => array_map(self::formatear(...), $consulta->fetchAll()),
            'pagina'   => $pagina,
            'paginas'  => $paginas,
            'total'    => $total,
        ];
    }

    /** @return array<string, string> Mensaje por cada campo que ya está registrado. */
    public function duplicados(array $datos): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT email, telefono FROM personas WHERE email = :email OR telefono = :telefono'
        );
        $consulta->execute([
            'email'    => $datos['email'],
            'telefono' => $datos['telefono'] === '' ? null : $datos['telefono'],
        ]);

        $errores = [];

        foreach ($consulta->fetchAll() as $fila) {
            if (strcasecmp($fila['email'], $datos['email']) === 0) {
                $errores['email'] = 'Este email ya está registrado.';
            }
            if ($fila['telefono'] !== null && $fila['telefono'] === $datos['telefono']) {
                $errores['telefono'] = 'Este teléfono ya está registrado.';
            }
        }

        return $errores;
    }

    /** @return int|null Id insertado, o null si un índice único rechazó el registro. */
    public function insertar(array $datos): ?int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO personas (nombre, email, telefono) VALUES (:nombre, :email, :telefono)'
        );

        try {
            $consulta->execute([
                'nombre'   => $datos['nombre'],
                'email'    => $datos['email'],
                'telefono' => $datos['telefono'] === '' ? null : $datos['telefono'],
            ]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === self::ERROR_DUPLICADO) {
                return null;
            }
            throw $e;
        }

        return (int) $this->pdo->lastInsertId();
    }

    private static function formatear(array $fila): array
    {
        $fila['id'] = (int) $fila['id'];
        // Formato ISO 8601 en UTC, interpretado igual por todos los navegadores.
        $fila['fecha_insertado'] = str_replace(' ', 'T', $fila['fecha_insertado']) . 'Z';

        return $fila;
    }
}
