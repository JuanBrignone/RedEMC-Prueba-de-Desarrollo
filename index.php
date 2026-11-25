<?php

declare(strict_types=1);

require __DIR__ . '/src/PersonaValidator.php';

ini_set('display_errors', '0');

header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

/** Agrega la fecha del archivo a la URL para que el navegador no use una versión vieja. */
function recurso(string $ruta): string
{
    return htmlspecialchars($ruta . '?v=' . filemtime(__DIR__ . '/' . $ruta), ENT_QUOTES);
}

$reglas = htmlspecialchars(json_encode(PersonaValidator::REGLAS, JSON_THROW_ON_ERROR), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingreso y listado de datos</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="<?= recurso('assets/vendor/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= recurso('assets/css/estilos.css') ?>">
</head>
<body>
    <div class="container py-4 py-lg-5">
        <div class="row g-4">

            <div class="col-12 col-lg-4">
                <div class="panel">
                    <h1 class="panel-titulo">Ingreso de Datos</h1>

                    <form id="formulario" method="post" novalidate autocomplete="off" data-reglas="<?= $reglas ?>">
                        <div class="campo">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input class="form-control" type="text" id="nombre" name="nombre"
                                   required aria-describedby="error-nombre">
                            <div class="campo-error" id="error-nombre" role="alert"></div>
                        </div>

                        <!-- type="text": con type="email" cada navegador altera el valor a su manera. -->
                        <div class="campo">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" type="text" inputmode="email" autocapitalize="none"
                                   spellcheck="false" id="email" name="email"
                                   required aria-describedby="error-email">
                            <div class="campo-error" id="error-email" role="alert"></div>
                        </div>

                        <!-- Sin maxlength: recortaría en silencio un número pegado con dígitos de más. -->
                        <div class="campo">
                            <label class="form-label" for="telefono">Teléfono <span class="opcional">(opcional)</span></label>
                            <input class="form-control" type="tel" id="telefono" name="telefono"
                                   placeholder="097680102" aria-describedby="error-telefono">
                            <div class="campo-error" id="error-telefono" role="alert"></div>
                        </div>

                        <button class="btn btn-guardar w-100" type="submit" id="guardar">Guardar</button>
                        <div class="aviso" id="aviso" role="status"></div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="panel">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <h2 class="panel-titulo">Listado de Datos</h2>
                        <div class="total" id="total"></div>
                    </div>

                    <div class="fila fila-encabezado" aria-hidden="true">
                        <div>Nombre</div>
                        <div>Email</div>
                        <div>Teléfono</div>
                        <div>Fecha</div>
                    </div>
                    <div id="listado" role="list" aria-live="polite"></div>
                    <div class="estado" id="estado"></div>
                    <div class="paginado" id="paginado" role="navigation" aria-label="Páginas del listado"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="<?= recurso('assets/vendor/jquery.min.js') ?>"></script>
    <script src="<?= recurso('assets/js/app.js') ?>"></script>
</body>
</html>
