# Cambio: discos-spotify-propuestas

Estado: aprobada por el usuario en el chat (2026-11-11) para piloto en entorno local. Autonomía: `IA_CON_VALIDACION`. Producción queda fuera hasta una decisión explícita.

## Objetivo

Una tarea programada local de Claude que, una vez por día y para un solo artista por corrida, busque en Spotify discos de artistas ya cargados en el portal y los cree **inactivos** (`estado = false`) mediante `POST /api/v1/albums`, para que aparezcan en el Centro de Moderación y el usuario los apruebe.

## Problema actual

La discografía se carga a mano. Hay 458 artistas y la cobertura de discos es desigual. No existe un proceso que proponga discos con datos verificables y revisión humana previa.

## Alcance incluido

- Una corrida diaria que procesa **un artista** (`interpretes`) por vez.
- Fuente: Spotify Web API (credenciales de app, flujo client credentials).
- Crear discos inactivos con los campos de `StoreAlbumRequest` que Spotify provee.
- Descargar la carátula mediante `featured_image_url` (la API la procesa y la guarda en `albunes`).
- Guardar el reproductor en el campo `spotify` con el mismo formato `<iframe>` de embed que ya usan los discos existentes.
- Archivo de estado local con la fecha del último chequeo por artista.
- Notificación al terminar cada corrida con el resumen.

## Fuera de alcance

- Letras de canciones y tracklist (la API de álbumes no los acepta y `AGENTS.md` exige decisión humana sobre derechos).
- Campo `label` (Spotify lo quitó de la API en febrero de 2026; el usuario indicó que por ahora no importa).
- Aprobar o publicar discos: lo hace el usuario.
- Editar o borrar discos existentes.
- Producción, nuevos permisos de API y cambios de código del portal.
- Cargar artistas nuevos.

## Archivos afectados

- `project/docs/specs/discos-spotify-propuestas.md` (esta spec)
- `project/docs/00_estado_actual.md` y `CHANGELOG.md` al cierre
- Tarea programada local (fuera del repositorio) y archivo de estado fuera del repositorio
- Sin cambios en código de la aplicación.

## Datos y mapeo

| Campo API | Origen en Spotify |
|---|---|
| `interprete_id` | artista del portal que se está procesando |
| `album` | `name` |
| `anio` | año de `release_date` |
| `release_date` | `release_date` solo si viene con día completo (YYYY-MM-DD) |
| `album_type` | `album` → `studio`; `single` → `single`; `compilation` → `compilation`; si no, vacío |
| `spotify` | iframe de embed con el `id` del álbum (formato idéntico a los existentes) |
| `featured_image_url` | imagen de mayor resolución de `images` |
| `image_alt` | `Portada de <album> de <artista>` |
| `estado` | siempre `false` |
| `slug` | lo genera la API desde `album` |

`excerpt`, `seo_title` y `meta_description` quedan vacíos: no se inventa contenido editorial.

## Reglas funcionales

### Selección del artista
Dado el archivo de estado y la lista de artistas (`GET /artists`)
Cuando arranca la corrida
Entonces toma el artista nunca chequeado o el de chequeo más antiguo, y lo registra con fecha al terminar.

### Match del artista en Spotify
Dado un artista del portal
Cuando se busca por nombre (`GET /search`, máximo 10 resultados)
Entonces solo continúa si hay un resultado cuyo nombre coincide exactamente (sin distinguir mayúsculas ni tildes) y ese match es único. Si hay duda u homónimos, omite al artista, lo registra como «sin match claro» y no crea nada.

### Detección de duplicados
Dado el match de Spotify
Cuando se comparan sus discos con los del portal (`GET /albums?interprete_id=`)
Entonces un disco ya existe si su ID de Spotify figura en el campo `spotify` de algún disco del portal, o si el slug coincide para ese intérprete. Los existentes se omiten, estén activos o inactivos.

### Qué discos se proponen
Dado un disco nuevo de ese artista
Cuando es de tipo `album`, `single` o `compilation` y tiene año y nombre
Entonces se crea. Se omiten discos donde el artista figura solo como invitado y se omiten ediciones repetidas del mismo título (se propone una sola por título y año).

### Alta
Dado un disco a proponer
Cuando se envía `POST /api/v1/albums` con `estado: false`
Entonces responde 201 y el disco aparece en Moderación (`ModerationQueue::albums()` lista `estado = 0`).

### Errores
Dado un 422, 401/403 o error de red
Cuando ocurre en un disco
Entonces no reintenta en bucle: registra el error y sigue con el siguiente; si falla la autenticación, corta la corrida y lo notifica.

### Límite de volumen
Cada corrida crea como máximo 15 discos. Si el artista tiene más, el resto queda para la próxima corrida del mismo artista (no se marca como chequeado hasta completar).

## Reglas técnicas

- Credenciales solo por variables de entorno: `SPOTIFY_CLIENT_ID`, `SPOTIFY_CLIENT_SECRET`, `LMFA_API_TOKEN`, `LMFA_API_URL`. Nunca en el prompt de la tarea, el repo ni los mensajes.
- Piloto en local: `LMFA_API_URL=http://mfa.localhost/api/v1` (el proxy enruta por nombre de host; `localhost` da 503).
- La app de Spotify está en modo desarrollo y requiere que el dueño tenga Premium. Si la suscripción se cancela, la tarea falla con 403 y debe avisarlo.
- Respetar la cuota de Spotify: sin ráfagas; un artista por corrida.
- Sin letras, sin tracklist, sin tocar discos existentes.

## Validación

1. Piloto manual con un artista en local: verificar 201, `estado = 0`, imagen guardada, iframe correcto y aparición en `/backend/moderacion` (o la ruta vigente del Centro de Moderación).
2. Repetir la misma corrida: no debe crear duplicados.
3. Artista con homónimo o sin match: debe omitirse sin crear nada.
4. Revisar que el reproductor se vea en la ficha del disco al activarlo en local.
5. No hay tests Feature nuevos: no se modifica código de la aplicación. El checklist manual de arriba es la evidencia.

## Riesgos

- **Match incorrecto de artista** (homónimos): mitigado con coincidencia exacta y única; igual el usuario aprueba cada disco.
- **Derechos de la carátula:** la imagen es de terceros. Los discos nacen inactivos y la fuente queda registrada; la decisión de publicar es del usuario.
- **Token con rol administrador:** puede hacer más que crear discos. Para producción conviene un usuario dedicado o un permiso específico (spec aparte).
- **Credenciales en el historial de la sesión:** el Client Secret fue leído durante la configuración; se recomienda rotarlo en el panel de Spotify.
- **Dependencia de Premium y de condiciones de la API de Spotify,** que cambiaron en 2026.
- **Cobertura:** 458 artistas a un artista por día son más de un año de ciclo completo.

## Criterio de aceptación

Terminado el piloto local cuando: un artista real produce discos inactivos con portada y reproductor, visibles en Moderación; una segunda corrida no duplica; un artista ambiguo se omite; y el usuario revisó los resultados. Pasar a producción requiere una decisión y un token propios.
