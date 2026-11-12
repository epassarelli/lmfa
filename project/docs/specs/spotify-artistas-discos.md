# Spec: Spotify en Artistas y Discos (vínculo, importación manual y tarea programada)

- **ID:** `spotify-artistas-discos`
- **Rama:** `feature/spotify-artistas-discos` (desde `dev`)
- **Estado:** implementado y validado en local (2026-11-12). Sin despliegue a producción.
- **Autonomía:** `IA_CON_VALIDACION`. Incluye una migración aditiva (3 columnas nullable); aprobada por el usuario en el chat el 2026-11-12 («Sí, vayamos por ese camino»).
- **Reemplaza a:** la lógica del script local `run-discos.ps1` (piloto). Mantiene las reglas de `discos-spotify-propuestas.md`.

## 1. Contexto y problema

El piloto (script PowerShell + tarea programada de Claude) busca al artista por nombre en cada corrida y llama a la API del portal. Tiene tres límites: la búsqueda por nombre es ambigua (el artista 1 quedó sin coincidencia), solo corre con la app de Claude abierta en la PC del usuario, y no se puede usar desde el backoffice.

Se mueve la lógica a Laravel y se guarda el **ID de artista de Spotify** en cada artista del portal. Con eso, tres funciones comparten el mismo código.

## 2. Alcance

1. **Vínculo artista ↔ Spotify**: columna `spotify_artist_id`, campo editable, búsqueda de candidatos, barrida semiautomática y pantalla de revisión.
2. **Importación manual desde la ficha del artista**: vista previa de los discos que el portal todavía no tiene; el usuario elige cuáles crear, siempre como **inactivos** (quedan en Moderación).
3. **Comando programable** `spotify:proponer-discos`: un artista vinculado por corrida, máximo 15 discos, inactivos. Desactivado por defecto en el scheduler (se habilita con una variable de entorno).

### Fuera de alcance
- Actualizar discos que ya existen (riesgo de pisar curación).
- Lista de canciones, letras (Spotify no las ofrece) y sello (la API no lo expone).
- Despliegue a producción, secretos de producción y habilitar el scheduler allá.
- Importar artistas nuevos desde Spotify.

## 3. Reglas de negocio

### Vínculo
- Un ID de Spotify pertenece a **un solo** artista del portal (índice único). Si ya está usado por otro, se rechaza y se informa cuál.
- **Nunca se sobrescribe un ID existente de forma automática.** Cambiarlo es una acción manual (editar o desvincular).
- La barrida automática vincula **solo** cuando el nombre normalizado (sin tildes, minúsculas, sin signos) coincide exacto con **un único** resultado y ese ID no está en uso. Todo lo demás queda «pendiente de revisión».
- Se acepta el ID, la URL `https://open.spotify.com/artist/<id>` o la URI `spotify:artist:<id>`. Se valida el formato (22 caracteres alfanuméricos).
- Un artista sin vínculo sigue funcionando con normalidad en el portal.

### Importación de discos
- Todo disco creado queda con `estado = 0`, sin importar el rol de quien importa.
- Dedupe: no se propone un disco cuyo ID de Spotify ya figura en el embed de un disco del artista (`open.spotify.com/embed/album/<id>`). Si coincide el slug o el título normalizado con otro año/edición, se marca **posible duplicado** y viene destildado en la vista previa; la tarea programada lo omite.
- Solo discos del artista vinculado como primer artista, tipos `album`, `single`, `compilation`, con nombre y año de 4 dígitos. Sin repetidos por título+año.
- Campos: `album`, `anio`, `release_date` (si viene completa), `album_type` (`album→studio`, `single→single`, `compilation→compilation`), `spotify` (iframe de embed igual al de los discos existentes), `image_alt`, `slug` único por artista, `user_id` (quien importa o el usuario configurado en la tarea), portada descargada con el flujo de imágenes del portal (queda `source_url` y derechos «unknown»).
- La portada pasa por `ImageSourceResolver` (protección SSRF, límite 5 MB) e `ImageUploadService`. Si la portada falla, el disco **se crea igual** y el error queda informado.
- Tope por corrida de la tarea: 15 (parámetro). La importación manual no tiene tope, pero muestra como máximo lo que devuelve Spotify.

### Seguridad y permisos
- Credenciales solo por variables de entorno (`SPOTIFY_CLIENT_ID`, `SPOTIFY_CLIENT_SECRET`); nunca en código, docs ni logs.
- Vincular y desvincular: quien pueda `update` sobre el artista. Importar discos: además `create` sobre discos. La acción masiva de vinculación: solo administrador.
- Flujo *client credentials* de Spotify (sin datos de usuarios de Spotify). El token se cachea hasta poco antes de su vencimiento.
- Si Spotify responde 429 se respeta `Retry-After` (con reintento acotado). Si responde 401/403 se informa que puede faltar el Premium del dueño de la app.

## 4. Diseño técnico

### Base de datos (migración aditiva, aprobada)
Tabla `interpretes`:
- `spotify_artist_id` varchar(32) nullable, **unique**.
- `spotify_link_checked_at` timestamp nullable: última búsqueda de vínculo (para no reintentar sin sentido).
- `spotify_albums_checked_at` timestamp nullable: última revisión de discos (rotación de la tarea).

`down()` elimina las tres columnas. No toca datos existentes.

### Código
- `config/spotify.php` (`client_id`, `client_secret`, `proposals_user_id`, `schedule_enabled`).
- `App\Services\Spotify\SpotifyClient`: token cacheado, `searchArtists`, `artist`, `artistAlbums` (paginado), reintento en 429, excepción `SpotifyException` con mensaje claro.
- `App\Services\Spotify\SpotifyArtistLinker`: normalización, `extractId`, `candidates`, `autoLink`, `link`, `unlink`.
- `App\Services\Spotify\SpotifyAlbumImporter`: `preview`, `import`.
- `App\Http\Controllers\Backend\InterpreteSpotifyController`: `show`, `link`, `unlink`, `import`, `bulkLink`.
- Rutas en `routes/admin.php` (nombres `backend.interpretes.spotify.*`).
- Vistas: `backend/interpretes/spotify.blade.php`; campo y enlace en el formulario del artista; filtro y acceso en el listado.
- Comandos: `spotify:vincular-artistas {--dry-run} {--limit=}` y `spotify:proponer-discos {--artist=} {--max=15} {--dry-run} {--user=}`.
- Scheduler (`app/Console/Kernel.php`): `spotify:proponer-discos` diario 10:00 solo si `SPOTIFY_SCHEDULE_ENABLED=true`.

## 5. Criterios de aceptación (Dado / Cuando / Entonces)
1. Dado un artista con un único resultado exacto en Spotify, cuando corre la barrida, entonces queda vinculado; con 0 o 2+ resultados exactos queda sin vincular y se lista como pendiente.
2. Dado un ID ya usado por otro artista, cuando se intenta vincular, entonces se rechaza con mensaje.
3. Dado un artista vinculado, cuando se abre la pantalla Spotify, entonces se listan los discos nuevos con portada y los posibles duplicados destildados.
4. Dado un conjunto de discos tildados, cuando se importa, entonces se crean inactivos, con embed y portada, y aparecen en Moderación.
5. Dado un disco ya existente (mismo ID de Spotify), cuando se vuelve a importar, entonces no se duplica.
6. Dado el comando programado, cuando corre, entonces procesa un solo artista vinculado, crea hasta el tope y actualiza `spotify_albums_checked_at`.
7. Dado un usuario sin permiso sobre el artista, cuando intenta vincular o importar, entonces recibe 403.
8. Dado que Spotify falla, entonces el portal no se rompe y se informa el error.

## 6. Casos borde
- Homónimos o nombres parecidos: no se vincula solo; se revisa a mano.
- Artistas con varios nombres (apodos): el usuario pega la URL correcta.
- Colaboraciones: se omiten discos donde el artista vinculado no es el primero.
- Reediciones con otro año: posible duplicado, a decisión humana.
- Portada caída o muy pesada: el disco se crea sin portada y se informa.
- Spotify en modo desarrollo: búsqueda máx. 10 resultados; puede endurecerse sin aviso.

## 7. Impacto
- **Editorial:** los discos entran siempre inactivos; sin letras, sello ni lista de temas. Derechos de portadas: `unknown`, a revisar al aprobar.
- **SEO:** nada cambia hasta que un humano activa un disco.
- **Performance:** llamadas a Spotify solo en el backoffice o por comando, nunca en el frontend público. Token cacheado.
- **Operativo:** la integración depende de que el Premium del dueño de la app siga activo.

## 8. Validación prevista
Tests Feature con `Http::fake()` (nunca llamadas reales) y `DatabaseTransactions`; prueba manual local con la cuenta real de Spotify en modo simulación (`--dry-run`) antes de crear datos.

## 9. Dependencias y pendientes
- Rotar el Client Secret de Spotify (pasó por una sesión de trabajo).
- Para producción: otro `.env`, usuario propietario de las propuestas y decisión explícita del usuario para habilitar el scheduler.
