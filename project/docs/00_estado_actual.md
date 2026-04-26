# 00 — Estado Actual del Proyecto

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesión de trabajo.
> Última actualización: 2026-04-26 (Auditoría completa + limpieza de archivos legacy)

---

## Rama activa

`v2` — rama de versión mayor salida de `dev`. Contiene la unificación arquitectónica y la Pasarela de Contenidos.

**Flujo de ramas:** `dev` → rama feature/versión → validar → merge a `dev` → deploy desde `main`.

---

## Estado del proyecto

Backlog original completo (PC-01 a PC-13 en `done`). El proyecto está en fase de **estabilización y auditoría** previo a definir specs de v2.

Se realizó auditoría completa del código el 2026-04-26. Ver sección de bugs corregidos.

---

## Base de datos

**Motor:** MariaDB 10.8 en Docker (`lmfa-db-1`).

### Tablas activas (se leen y escriben desde la app)

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `news` | `News` | Activa — 351 filas. Modelo canónico para noticias. Campo principal: `editorial_status`. |
| `events` | `Event` | Activa — 6 filas. Modelo canónico para eventos/shows. Campo principal: `start_at`. |
| `interpretes` | `Interprete` | Activa |
| `canciones` | `Cancion` | Activa |
| `albunes` | `Album` | Activa |
| `festivales` | `Festival` | Activa |
| `mitos` | `Mito` | Activa |
| `comidas` | `Comida` | Activa |
| `classifieds` | `Classified` | Activa |
| `contributions` | `Contribution` | Activa — flujo UGC a verificar end-to-end |
| `newsletter_subscribers` | `NewsletterSubscriber` | Activa |
| `organizations` | `Organization` | Activa — Pasarela |
| `organization_members` | `OrganizationMember` | Activa — Pasarela |
| `social_accounts` | `SocialAccount` | Activa — Pasarela |
| `publication_requests` | `PublicationRequest` | Activa — Pasarela |
| `publication_targets` | `PublicationTarget` | Activa — Pasarela |
| `publication_attempts` | `PublicationAttempt` | Activa — Pasarela |
| `publication_templates` | `PublicationTemplate` | Activa — Pasarela |
| `moderation_reviews` | `ModerationReview` | Activa |
| `audit_logs` | `AuditLog` | Activa |
| `notifications` | `UserNotification` | Activa |

### Tablas legacy (existen en BD, no se usan desde la app)

| Tabla | Filas | Estado |
|-------|-------|--------|
| `noticias` | 347 | Datos previos a la migración a `news`. No se lee ni escribe. Evaluar limpieza. |
| `shows` | 0 | Vaciada al migrar a `events`. Candidata a eliminar. |
| `images` | 2 | Reemplazada por `media_assets`. |

### Nota crítica — compatibilidad legacy
Los modelos `News` y `Event` tienen accessors que mapean nombres de campos viejos (`titulo`, `noticia`, `fecha`, `estado`) a los nuevos (`title`, `body`, `start_at`, `editorial_status`). Estos **solo funcionan en instancias, no en queries WHERE**. Siempre usar el nombre canónico en consultas.

---

## Arquitectura de Rutas

| Archivo | Middleware | Propósito |
|---------|------------|-----------|
| `routes/web.php` | `web` | Frontend público + colaboraciones UGC |
| `routes/admin.php` | `web`, `auth` | Panel admin + Pasarela de Contenidos bajo `/admin` |
| `routes/api.php` | `api`, `auth:sanctum` | API REST v1 |

**Nomenclatura:** rutas admin usan prefijo `backend.*` (ej: `backend.events.index`). Pasarela usa `pasarela.*`.

---

## Estado por módulo

### Completamente funcional ✅
Backend CRUD: Events, News, Interpretes, Albumes, Canciones, Comidas, Festivales, Mitos, Categorias, Tags, Clasificados, Moderacion, Newsletter, Roles, Users, Permissions.

Frontend: Home, Noticias, Cartelera, Miniportal artista, Canciones, Discografía, Festivales, Mitos, Recetas, Clasificados, Contacto, Buscador, Sitemap, Newsletter, Social Auth.

API REST: news, albums, songs, foods, festivals, artists, myths (CRUD completo).

### Implementado, sin probar en producción ⚠️
- **Pasarela de Contenidos** (`/admin/pasarela`): social accounts, publication requests, templates, notificaciones, dashboards. Código completo, nunca probado end-to-end.
- **Colaboraciones UGC** (`/admin/contribuir`): migrado de `/colaborar`. Requiere verificación del flujo completo.

### Módulos diferidos — próxima versión 🔮
- **Entrevistas**: rutas activas en `web.php`, controller sin métodos `byArtista`/`show`, sin vistas, modelo inexistente.
- **Radios**: índice funcional, detalle sin implementar, sin backend.
- **Peñas**: ídem Radios.

---

## Bugs corregidos — 2026-04-26

| Archivo | Problema | Fix |
|---------|----------|-----|
| `BusquedaController` | Usaba `Noticia::where('titulo',...)` y `Show::where('show',...)` — columnas inexistentes en DB | Cambiado a `News::where('title',...)` y `Event::where('title',...)` |
| `SitemapController` | Usaba `Show::where('estado',1)->where('fecha',...)` — `fecha` y `estado` no existen en `events` | Cambiado a `Event::where('editorial_status','published')->where('start_at',...)` |
| `SitemapController` | `newsIndex()` usaba `Noticia::where('estado',1)` | Cambiado a `News::where('editorial_status','published')` |

## Archivos eliminados — 2026-04-26

| Archivo | Motivo |
|---------|--------|
| `routes/pasarela.php` | Duplicado no cargado — rutas ya en `admin.php` |
| `routes/_web.php` | Backup antiguo de web.php, no cargado |
| `app/Http/Controllers/Backend/ShowController.php` | Reemplazado por `EventController`, tenía bugs de tipo |
| `app/Http/Controllers/Backend/NoticiaController.php` | Reemplazado por `NewsController` |

---

## Modelos y Políticas

- **`EventPolicy`**: CRUD de `Event`. Admins con acceso total, colaboradores pueden crear/editar propios.
- **`NewsPolicy`**: CRUD de `News`. Compatibilidad con permisos legacy `read noticia`.
- Ambas registradas en `AuthServiceProvider`.

---

## Pendientes identificados

1. **Probar Pasarela de Contenidos** end-to-end en el browser.
2. **Verificar flujo UGC** (`/admin/contribuir` create → store → moderación).
3. **Evaluar limpieza de tablas legacy** (`noticias`, `shows`, `images`) — requiere confirmar que no hay datos únicos antes de eliminar.
4. **Crear `openspec/project.md`** con contexto del proyecto para el workflow OpenSpec.
5. **Completar AGENTS.md** con secciones operativas (pendiente del plan aprobado).
