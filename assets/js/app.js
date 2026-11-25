(function ($) {
    'use strict';

    var URL_API = 'api/personas.php';
    var ERROR_CONEXION = 'No se pudo conectar con el servidor. Intentá nuevamente.';

    var $formulario = $('#formulario');
    var $guardar = $('#guardar');
    var $aviso = $('#aviso');
    var $listado = $('#listado');
    var $estado = $('#estado');
    var $paginado = $('#paginado');
    var $total = $('#total');

    // Las reglas llegan desde PHP (PersonaValidator), así no se duplican.
    var reglas = $formulario.data('reglas');
    var campos = Object.keys(reglas);
    var formatoFecha = new Intl.DateTimeFormat('es-UY', {
        timeZone: 'America/Montevideo',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23'
    });
    var pedidoListado = null;

    /* ---------- Formulario ---------- */

    function leerCampo(campo) {
        var valor = $('#' + campo).val();

        if (campo === 'nombre') {
            // NFC une las tildes que algunos sistemas escriben como carácter aparte.
            return valor.normalize('NFC').replace(/\s+/g, ' ').trim();
        }

        return campo === 'email' ? valor.trim() : valor;
    }

    function leerFormulario() {
        var datos = {};

        campos.forEach(function (campo) {
            datos[campo] = leerCampo(campo);
        });

        return datos;
    }

    function errorDeCampo(campo, valor) {
        var regla = reglas[campo];

        if (valor === '') {
            return regla.obligatorio ? regla.mensajes.vacio : '';
        }
        if (regla.maximo !== null && valor.length > regla.maximo) {
            return regla.mensajes.maximo;
        }

        return new RegExp(regla.patron, 'u').test(valor) ? '' : regla.mensajes.formato;
    }

    function mostrarError(campo, mensaje) {
        $('#' + campo).toggleClass('is-invalid', mensaje !== '').attr('aria-invalid', mensaje !== '');
        $('#error-' + campo).text(mensaje);
    }

    function mostrarErrores(errores) {
        campos.forEach(function (campo) {
            mostrarError(campo, errores[campo] || '');
        });
        $formulario.find('.is-invalid').first().trigger('focus');
    }

    function mostrarAviso(mensaje, tipo) {
        $aviso.removeClass('aviso-exito aviso-error').addClass('aviso-' + tipo).text(mensaje);
    }

    function validar(datos) {
        var errores = {};

        campos.forEach(function (campo) {
            var mensaje = errorDeCampo(campo, datos[campo]);

            if (mensaje !== '') {
                errores[campo] = mensaje;
            }
        });

        return errores;
    }

    function guardar(datos) {
        $guardar.prop('disabled', true).text('Guardando…');

        $.ajax({
            url: URL_API,
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify(datos),
            dataType: 'json',
            timeout: 15000
        }).done(function (respuesta) {
            $formulario.trigger('reset');
            mostrarAviso('El registro se guardó correctamente.', 'exito');
            cargarPagina(1, respuesta.id);
        }).fail(function (xhr) {
            var respuesta = xhr.responseJSON || {};

            mostrarErrores(respuesta.errores || {});
            mostrarAviso(respuesta.mensaje || ERROR_CONEXION, 'error');
        }).always(function () {
            $guardar.prop('disabled', false).text('Guardar');
        });
    }

    $formulario.on('submit', function (evento) {
        evento.preventDefault();

        var datos = leerFormulario();
        var errores = validar(datos);

        if ($guardar.prop('disabled')) {
            return;
        }

        $aviso.text('');
        mostrarErrores(errores);

        if ($.isEmptyObject(errores)) {
            guardar(datos);
        }
    });

    $formulario.on('input', '.form-control', function () {
        mostrarError(this.name, '');
        $aviso.text('');
    });

    $formulario.on('blur', '.form-control', function () {
        // No se marca como error un campo que todavía no se completó.
        if (this.value !== '' && !$(this).hasClass('is-invalid')) {
            mostrarError(this.name, errorDeCampo(this.name, leerCampo(this.name)));
        }
    });

    /* ---------- Listado ---------- */

    function crearFila(persona, idNuevo) {
        // .text() escapa el contenido: evita inyección de HTML (XSS).
        return $('<div class="fila" role="listitem">')
            .toggleClass('fila-nueva', persona.id === idNuevo)
            .append($('<div class="fila-nombre">').text(persona.nombre))
            .append($('<div>').text(persona.email))
            .append($('<div>').toggleClass('fila-telefono-vacio', !persona.telefono).text(persona.telefono || '—'))
            .append($('<div>').text(formatoFecha.format(new Date(persona.fecha_insertado))));
    }

    function crearBoton(texto, pagina, deshabilitado) {
        return $('<button type="button">').text(texto).attr('data-pagina', pagina).prop('disabled', deshabilitado);
    }

    function mostrarPaginado(actual, paginas) {
        var anterior = 0;

        $paginado.empty();

        if (paginas === 1) {
            return;
        }

        $paginado.append(crearBoton('Anterior', actual - 1, actual === 1));

        for (var pagina = 1; pagina <= paginas; pagina += 1) {
            // Se muestran la primera, la última y las vecinas de la actual.
            if (pagina === 1 || pagina === paginas || Math.abs(pagina - actual) <= 1) {
                if (pagina - anterior > 1) {
                    $paginado.append($('<div class="puntos">').text('…'));
                }

                var $boton = crearBoton(pagina, pagina, false);

                if (pagina === actual) {
                    $boton.addClass('actual').attr('aria-current', 'page');
                }

                $paginado.append($boton);
                anterior = pagina;
            }
        }

        $paginado.append(crearBoton('Siguiente', actual + 1, actual === paginas));
    }

    function mostrarListado(respuesta, idNuevo) {
        $listado.empty().append(respuesta.personas.map(function (persona) {
            return crearFila(persona, idNuevo);
        }));
        $estado.text(respuesta.total === 0 ? 'Todavía no hay registros.' : '');
        $total.text(respuesta.total === 1 ? '1 registro' : respuesta.total + ' registros');
        mostrarPaginado(respuesta.pagina, respuesta.paginas);
    }

    function cargarPagina(pagina, idNuevo) {
        // Si se cambia rápido de página, solo interesa el último pedido.
        if (pedidoListado) {
            pedidoListado.abort();
        }

        $listado.addClass('cargando');

        var pedido = $.ajax({
            url: URL_API,
            data: { pagina: pagina },
            dataType: 'json',
            timeout: 15000
        });

        pedidoListado = pedido;

        pedido.done(function (respuesta) {
            mostrarListado(respuesta, idNuevo);
        }).fail(function (xhr, motivo) {
            if (motivo !== 'abort') {
                $estado.text('No se pudo cargar el listado. Recargá la página para volver a intentarlo.');
            }
        }).always(function () {
            if (pedidoListado === pedido) {
                pedidoListado = null;
                $listado.removeClass('cargando');
            }
        });
    }

    $paginado.on('click', 'button', function () {
        cargarPagina(Number($(this).attr('data-pagina')));
    });

    cargarPagina(1);
}(jQuery));
