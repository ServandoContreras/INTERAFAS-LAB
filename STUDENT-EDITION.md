# INTERAFAS-LAB — Student Edition

La distribución de estudiante está separada del entorno de desarrollo.

## Objetivo

El alumno recibe únicamente:

- `docker-compose.student.yml`
- `.env.example`
- lanzadores Windows y macOS/Linux
- guía de uso

El código fuente, migraciones y walkthroughs permanecen en el repositorio docente.

## Construcción de imágenes

El workflow `.github/workflows/student-images.yml` construye:

- `ghcr.io/servandocontreras/interafas-web`
- `ghcr.io/servandocontreras/interafas-news`
- `ghcr.io/servandocontreras/interafas-auditor`
- `ghcr.io/servandocontreras/interafas-db`
- `ghcr.io/servandocontreras/interafas-ot-sim`

Para la primera versión se recomienda utilizar:

`student-v1.0.0`

Antes de distribuir el paquete, las imágenes de GHCR deben ser accesibles para los estudiantes. Para una clase sin autenticación de Docker, configúralas como públicas en GitHub Packages.

## Publicación

Al crear y enviar un tag:

```bash
git tag student-v1.0.0
git push origin student-v1.0.0
```

se ejecutan:

1. construcción y publicación de imágenes;
2. creación del ZIP `INTERAFAS-LAB-ESTUDIANTE-student-v1.0.0.zip`;
3. publicación del ZIP como GitHub Release.

## Desarrollo

El `docker-compose.yml` de la raíz mantiene bind mounts para desarrollo. Los Dockerfiles ahora también copian la aplicación dentro de la imagen, por lo que:

- en desarrollo, el bind mount sustituye los archivos internos;
- en la edición estudiante, la imagen funciona sin entregar el código fuente como archivos del paquete.

## Nota de seguridad académica

Una imagen Docker puede inspeccionarse por un alumno avanzado. La edición estudiante reduce la exposición accidental de las soluciones, pero no constituye un mecanismo antitrampa. La evaluación debe utilizar también la bitácora, la secuencia de eventos y las evidencias generadas durante el ejercicio.
