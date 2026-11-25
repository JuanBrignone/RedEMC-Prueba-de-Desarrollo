<?php

declare(strict_types=1);

/**
 * Reglas de validación del formulario.
 *
 * Es la única fuente de las reglas: index.php se las entrega a JavaScript, por
 * lo que las expresiones regulares usan sintaxis válida tanto en PHP como en JS.
 */
final class PersonaValidator
{
    public const REGLAS = [
        'nombre' => [
            'obligatorio' => true,
            'maximo'      => 100,
            'patron'      => '^[A-Za-zÀ-ÖØ-öø-ÿ]+(?: [A-Za-zÀ-ÖØ-öø-ÿ]+)*$',
            'mensajes'    => [
                'vacio'   => 'El nombre es obligatorio.',
                'formato' => 'El nombre solo puede contener letras y espacios.',
                'maximo'  => 'El nombre no puede superar los 100 caracteres.',
            ],
        ],
        'email' => [
            'obligatorio' => true,
            'maximo'      => 254,
            'patron'      => '^(?=[^@]{1,64}@)[A-Za-z0-9_+%\'-]+(?:\.[A-Za-z0-9_+%\'-]+)*'
                . '@(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}$',
            'mensajes'    => [
                'vacio'   => 'El email es obligatorio.',
                'formato' => 'El email no tiene un formato válido (ejemplo: nombre@dominio.com).',
                'maximo'  => 'El email no puede superar los 254 caracteres.',
            ],
        ],
        'telefono' => [
            'obligatorio' => false,
            'maximo'      => null,
            'patron'      => '^09[0-9]{7}$',
            'mensajes'    => [
                'formato' => 'El teléfono debe tener 9 dígitos y comenzar con 09, sin espacios, guiones ni +598 (ejemplo: 097680102).',
            ],
        ],
    ];

    /**
     * Limpia los datos recibidos. El teléfono no se modifica: si trae espacios
     * debe rechazarse, porque el formato exigido es estricto.
     *
     * @return array{nombre: string, email: string, telefono: string}
     */
    public static function normalizar(array $entrada): array
    {
        $texto = static fn (string $campo): string => is_string($entrada[$campo] ?? null) ? $entrada[$campo] : '';

        return [
            'nombre'   => self::recortar((string) preg_replace('/\s+/u', ' ', $texto('nombre'))),
            'email'    => strtolower(self::recortar($texto('email'))),
            'telefono' => $texto('telefono'),
        ];
    }

    /** @return array<string, string> Mensaje de error por campo; vacío si todo es válido. */
    public static function validar(array $datos): array
    {
        $errores = [];

        foreach (self::REGLAS as $campo => $regla) {
            $error = self::errorDeCampo($datos[$campo], $regla);

            if ($error !== null) {
                $errores[$campo] = $error;
            }
        }

        return $errores;
    }

    private static function errorDeCampo(string $valor, array $regla): ?string
    {
        if ($valor === '') {
            return $regla['obligatorio'] ? $regla['mensajes']['vacio'] : null;
        }

        if ($regla['maximo'] !== null && mb_strlen($valor, 'UTF-8') > $regla['maximo']) {
            return $regla['mensajes']['maximo'];
        }

        // u: UTF-8. D: "$" no acepta un salto de línea al final.
        return preg_match(';' . $regla['patron'] . ';uD', $valor) === 1 ? null : $regla['mensajes']['formato'];
    }

    private static function recortar(string $valor): string
    {
        return (string) preg_replace('/^\s+|\s+$/u', '', $valor);
    }
}
