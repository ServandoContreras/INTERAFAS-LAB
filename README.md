# INTERAFAS-LAB

**INTERAFAS-LAB** es un laboratorio académico controlado de ciberseguridad diseñado para estudiar cómo vulnerabilidades aparentemente independientes pueden encadenarse desde una aplicación web hasta un entorno operacional simulado.

El escenario utiliza una organización ficticia de infraestructura crítica y combina portal ciudadano, APIs, lógica de negocio, telemetría, un portal de noticias, un sistema de auditoría y un simulador OT.

> Uso exclusivo en entornos académicos y autorizados. No utilices las técnicas del laboratorio contra sistemas reales sin autorización expresa.

---

## Estado del proyecto

- **Versión estable del laboratorio:** `checkpoint/vuln-20-stable`
- **Student Edition:** `student-v1.0.0`
- **Retos disponibles:** 20
- **Ejecución:** Docker / Docker Compose
- **Modalidad:** local e independiente por estudiante
- **Entorno OT:** completamente simulado

Release de la edición estudiante:

https://github.com/ServandoContreras/INTERAFAS-LAB/releases/tag/student-v1.0.0

> El repositorio es privado. La Release sólo es visible para usuarios con acceso autorizado. Para distribución a grupos se recomienda descargar previamente el paquete OFFLINE y compartirlo mediante el medio institucional correspondiente.

---

## Objetivo académico

El laboratorio busca que el estudiante comprenda, mediante una experiencia práctica, la progresión de un incidente de ciberseguridad:

```text
Reconocimiento
    ↓
Exposición de información
    ↓
Autenticación y autorización
    ↓
Inyección y procesamiento inseguro
    ↓
APIs y lógica de negocio
    ↓
Descubrimiento de arquitectura
    ↓
Cruce IT → OT
    ↓
Telemetría y mantenimiento
    ↓
Dependencias operacionales
    ↓
Impacto físico simulado
    ↓
Recuperación
```

La evaluación no se limita a encontrar banderas. El entorno registra actividad y eventos para permitir analizar la secuencia seguida durante el ejercicio.

---

## Arquitectura

| Servicio | URL local | Función |
|---|---|---|
| Portal INTERAFAS | http://localhost:8080 | Portal público y ciudadano |
| Pulso Metropolitano | http://localhost:8091 | Narrativa mediática y evolución del incidente |
| Portal del Auditor | http://localhost:8092 | Registro del alumno, progreso, banderas, pistas y bitácora |
| MariaDB | Interno | Persistencia del escenario y telemetría |
| OT Simulator | Interno | Proceso operacional completamente simulado |

La comunicación entre servicios se realiza dentro de una red Docker local.

---

## Familias de retos

Los 20 retos cubren, entre otros, los siguientes temas:

- exposición accidental de archivos y respaldos;
- información sensible entregada al navegador;
- IDOR / BOLA;
- errores verbosos;
- control de acceso por rol;
- SQL Injection;
- Stored XSS;
- Path Traversal;
- gestión insegura de sesiones;
- APIs no documentadas;
- abuso de lógica de negocio;
- validación incorrecta de relaciones entre objetos;
- exposición de arquitectura interna;
- confianza indebida en cabeceras de proxy;
- acceso a telemetría operacional;
- validación deficiente de firmware;
- exceso de privilegios en contexto OT;
- encadenamiento IT → OT;
- impacto operacional simulado y recuperación.

Las banderas y rutas de solución se mantienen fuera de este README.

---

# Edición estudiante

La **Student Edition** permite que cada alumno ejecute su propia instancia sin recibir directamente el árbol fuente del laboratorio.

Existen dos paquetes.

### OFFLINE — recomendado para clase presencial

Incluye las cinco imágenes Docker necesarias.

Ventajas:

- no requiere acceso a GHCR durante la práctica;
- no depende de la red del aula;
- no requiere credenciales de GitHub;
- todos los estudiantes trabajan con exactamente la misma versión.

Archivo:

```text
INTERAFAS-LAB-ESTUDIANTE-OFFLINE-student-v1.0.0.zip
```

### LITE

Contiene únicamente el runtime y descarga las imágenes desde GitHub Container Registry.

Archivo:

```text
INTERAFAS-LAB-ESTUDIANTE-LITE-student-v1.0.0.zip
```

La edición LITE requiere que los paquetes de contenedor correspondientes sean accesibles para el alumno.

---

## Requisitos del alumno

### Docker Desktop

Descarga oficial:

https://www.docker.com/products/docker-desktop/

### Windows

Guía oficial de instalación:

https://docs.docker.com/desktop/setup/install/windows-install/

Si Docker solicita WSL 2:

https://learn.microsoft.com/windows/wsl/install

### macOS

https://docs.docker.com/desktop/setup/install/mac-install/

### Linux

Docker Engine:

https://docs.docker.com/engine/install/

Docker Compose:

https://docs.docker.com/compose/install/

### Recursos recomendados

- Windows 10/11, macOS o Linux.
- Docker Desktop o Docker Engine + Compose.
- Al menos 4 GB de RAM disponibles para Docker.
- Puertos locales `8080`, `8091` y `8092` disponibles.

---

# Inicio rápido — Student Edition OFFLINE

## Windows

Descomprime el paquete y ejecuta, en este orden:

```text
1. CARGAR-IMAGENES.bat
2. VERIFICAR-INSTALACION.bat
3. INICIAR-LABORATORIO.bat
```

Después abre primero:

http://localhost:8092

y registra tus datos y matrícula en el Portal del Auditor.

Para detener el entorno sin borrar el progreso:

```text
DETENER-LABORATORIO.bat
```

Para eliminar el progreso y regresar al estado inicial:

```text
RESTABLECER-LABORATORIO.bat
```

---

## macOS / Linux

```bash
chmod +x *.sh

./cargar-imagenes.sh
./verificar-instalacion.sh
./iniciar-laboratorio.sh
```

Para detener sin borrar el progreso:

```bash
./detener-laboratorio.sh
```

Para restablecer completamente:

```bash
./restablecer-laboratorio.sh
```

---

# Entorno de desarrollo / instructor

La rama estable de referencia es:

```bash
git checkout checkpoint/vuln-20-stable
```

Para reconstruir desde cero:

```bash
docker compose down -v
docker compose up -d --build
docker compose ps
```

Servicios:

```text
Portal INTERAFAS     http://localhost:8080
Pulso Metropolitano  http://localhost:8091
Portal del Auditor   http://localhost:8092
```

El `docker-compose.yml` del repositorio utiliza bind mounts para facilitar desarrollo. La distribución estudiante utiliza imágenes autocontenidas y un Compose independiente ubicado en `student/`.

Documentación técnica de la distribución:

`STUDENT-EDITION.md`

---

# Student Edition — estructura

```text
student/
├── docker-compose.student.yml
├── .env.example
├── CARGAR-IMAGENES.bat
├── VERIFICAR-INSTALACION.bat
├── INICIAR-LABORATORIO.bat
├── DETENER-LABORATORIO.bat
├── RESTABLECER-LABORATORIO.bat
├── cargar-imagenes.sh
├── verificar-instalacion.sh
├── iniciar-laboratorio.sh
├── detener-laboratorio.sh
├── restablecer-laboratorio.sh
└── README-ESTUDIANTE.md
```

Las imágenes publicadas para la versión actual son:

```text
ghcr.io/servandocontreras/interafas-web:student-v1.0.0
ghcr.io/servandocontreras/interafas-news:student-v1.0.0
ghcr.io/servandocontreras/interafas-auditor:student-v1.0.0
ghcr.io/servandocontreras/interafas-db:student-v1.0.0
ghcr.io/servandocontreras/interafas-ot-sim:student-v1.0.0
```

---

# Automatización

El repositorio incluye GitHub Actions para:

- construir las imágenes de estudiante;
- publicarlas en GHCR;
- generar paquetes LITE y OFFLINE;
- generar hashes SHA-256;
- publicar una Release;
- levantar el stack completo;
- verificar respuesta HTTP de los tres portales;
- desmontar el entorno después del smoke test.

La versión `student-v1.0.0` fue validada levantando los cinco contenedores y comprobando:

```text
Portal INTERAFAS: OK
Pulso Metropolitano: OK
Portal del Auditor: OK
MariaDB: healthy
OT Simulator: running
```

---

# Consideraciones de seguridad académica

La Student Edition reduce la exposición accidental del código fuente, pero una imagen Docker puede ser inspeccionada por un usuario con control total sobre su equipo.

Por ello, la evaluación debe considerar:

```text
banderas
+
secuencia de eventos
+
bitácora
+
evidencias técnicas
```

y no únicamente el número final de banderas obtenidas.

Todo el escenario, las instituciones, personas, sistemas y consecuencias narrativas presentes en el laboratorio son ficticios y se utilizan exclusivamente con fines educativos.

---

## Licencia y uso

Este proyecto está destinado a docencia, investigación y capacitación en ciberseguridad dentro de entornos controlados y autorizados.

No se autoriza su utilización para acceder, alterar, interrumpir o evaluar infraestructura real sin autorización expresa del propietario del sistema.
