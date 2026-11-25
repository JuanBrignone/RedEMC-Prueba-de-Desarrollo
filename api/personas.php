<?php

declare(strict_types=1);
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/PersonaValidator.php';
require __DIR__ . '/../src/PersonaRepository.php';

ini_set('display_errors', '0');

const TAMANIO_MAXIMO = 10240;

function responder(int $estado, array $datos): never
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function listar(): never
{
    $pagina = filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    responder(200, (new PersonaRepository(Database::conectar()))->listar($pagina ?: 1));
}

function guardar(): never
{
    if (!str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
        responder(415, ['mensaje' => 'El contenido debe enviarse como JSON.']);
    }

    $cuerpo  = (string) file_get_contents('php://input', false, null, 0, TAMANIO_MAXIMO + 1);
    $entrada = strlen($cuerpo) <= TAMANIO_MAXIMO ? json_decode($cuerpo, true) : null;

    if (!is_array($entrada)) {
        responder(400, ['mensaje' => 'Los datos enviados no son válidos.']);
    }

    $datos   = PersonaValidator::normalizar($entrada);
    $errores = PersonaValidator::validar($datos);

    if ($errores !== []) {
        responder(422, ['mensaje' => 'Revisá los campos marcados.', 'errores' => $errores]);
    }

    $repositorio = new PersonaRepository(Database::conectar());
    $errores     = $repositorio->duplicados($datos);
    $id          = $errores === [] ? $repositorio->insertar($datos) : null;

    if ($id === null) {
        $errores = $errores ?: $repositorio->duplicados($datos);
        responder(422, ['mensaje' => 'Revisá los campos marcados.', 'errores' => $errores]);
    }

    responder(201, ['id' => $id]);
}

try {
    match ($_SERVER['REQUEST_METHOD']) {
        'GET'   => listar(),
        'POST'  => guardar(),
        default => responder(405, ['mensaje' => 'Método no permitido.']),
    };
} catch (Throwable $e) {
    // El detalle queda en el log del servidor; nunca se expone al usuario.
    error_log((string) $e);
    responder(500, ['mensaje' => 'Ocurrió un error en el servidor. Intentá nuevamente.']);
}
