# Proceso con IA

## Herramientas y usos

Usé **Claude Code** (modelo Claude Opus 5.5) desde VS Code. Con esa herramienta se generó:

- el código PHP, jQuery, HTML y CSS, y la estructura de la tabla SQL;
- la consulta de la documentación de Railway y de la paleta de redemc.net;
- pruebas de las expresiones regulares y de la interfaz en Chrome, con un servidor simulado;
- los borradores del README y de este documento.

## Prompts clave

1. «Tengo que realizar esta prueba para ingresar a un trabajo como desarrollador. Quiero que me ayudes a realizar lo que pide», con el PDF de la prueba adjunto.
2. «La parte de crear la tabla lo quiero hacer en railway, sin instalar cosas nuevas».
3. «Tenes que usar lo que dice el stack que te envie, no agregar cosas como el dockerfile ni nada extra, solo lo que dice el archivo».

## Algo que rechacé

La IA propuso desplegar con un Dockerfile y usar imágenes de Docker para probar en local. La interrumpí y le pedí que se limitara al stack de la prueba, porque el enunciado no lo pedía y sumaba herramientas ajenas a lo solicitado. El Dockerfile no llegó a crearse, se borraron las carpetas preparadas para eso y quedó un proyecto PHP que Railway detecta directamente.

## Algo que aprendí

Realizando esta prueba aprendí a utilizar Railway, una tecnología que no había utilizado nunca. Al desplegar, la aplicación fallaba con el error could not find driver. Revisando los logs con ayuda de la IA, vimos que faltaba la extensión pdo_mysql, que conecta PHP con MySQL, y que la variable que la IA me había indicado para instalarla no funcionaba en este proyecto. Lo resolví con un Custom Build Command.

También es la primera vez que utilizo PHP, algo totalmente nuevo para mí, aunque sé que es muy utilizado.