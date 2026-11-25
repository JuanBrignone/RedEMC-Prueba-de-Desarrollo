-- Estructura de la tabla. Se ejecuta una única vez sobre la base MySQL.
-- Los índices UNIQUE garantizan la unicidad aun con dos envíos simultáneos.
-- La collation _ci hace que el email no distinga mayúsculas de minúsculas.

CREATE TABLE IF NOT EXISTS personas (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100) NOT NULL,
    email           VARCHAR(254) NOT NULL,
    telefono        CHAR(9)      NULL DEFAULT NULL,
    fecha_insertado DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_personas_email (email),
    UNIQUE KEY uq_personas_telefono (telefono)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
