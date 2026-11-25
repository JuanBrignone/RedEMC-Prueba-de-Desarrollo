# Proceso con IA

## Herramientas y usos

Usé **Claude Code** (modelo Claude Opus 5.5) desde VS Code. Con esa herramienta se generó:

- el código PHP, jQuery, HTML y CSS, y la estructura de la tabla SQL;
- la consulta de la documentación de Railway y de la paleta de redemc.net;
- pruebas de las expresiones regulares y de la interfaz en Chrome, con un servidor simulado;
- los borradores del README y de este documento.

## Prompts clave

1. «Tengo que realizar esta prueba para ingresar a un trabajo como desarrollador. Quiero que me ayudes a realizar lo que pide», con el PDF de la prueba adjunto.
2. «Me esta pidiendo explicitamente que se use Raialway».
3. «Tenes que usar lo que dice el stack y seguir al pie de la letra lo que dice el pdf, no agregar cosas como el dockerfile ni nada extra, solo lo que dice el archivo».

## Algo que rechacé

La IA propuso desplegar con un Dockerfile y usar imágenes de Docker para probar en local. La interrumpí y le pedí que se limitara al stack de la prueba, porque el enunciado no lo pedía y sumaba herramientas ajenas a lo solicitado. El Dockerfile no llegó a crearse, se borraron las carpetas preparadas para eso y quedó un proyecto PHP que Railway detecta directamente.

## Algo que aprendí

_PENDIENTE_
