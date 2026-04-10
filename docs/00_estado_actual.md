# 00 — Estado Actual del Proyecto

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesión de trabajo.
> Última actualización: 2026-04-10

---

## Rama activa

`pasarelaContenidos` — toda la Pasarela de Contenidos vive aquí. No crear sub-ramas sin autorización explícita del usuario.

---

## Estado del backlog

**Completo — 0 tareas pendientes.** Todas las historias de usuario (PC-01 a PC-13) están en `done`.

---

## Base de datos

### Motor
MariaDB 10.8 en Docker (`lmfa-db-1`). Volumen en filesystem Windows → **ALTER TABLE pesado puede crashear el contenedor.**

### Estrategia de migraciones
Las migraciones `2026_04_09_*` están **marcadas como ejecutadas** en la tabla `migrations` pero **no se corrieron con artisan migrate** — la estructura se creó via SQL directo para evitar crasheos. Esta es la norma del proyecto.

### Scripts de setup (ejecutar en orden tras un crash):
```bash
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/setup_pasarela_tables.sql
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/patch_missing_columns.sql
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/patch_news_columns.sql
```

### Tablas existentes
| Tabla | Origen | Notas |
|-------|--------|-------|
| `users` | base | + campos publisher: phone, status, is_verified_publisher, publisher_type_default, last_login_at, points, rank |
| `interpretes` | base | sin cambios |
| `noticias` | base | **DEPRECATED** — datos legacy, sin usar por el portal activo |
| `shows` | base | **DEPRECATED** — datos legacy, sin usar por el portal activo |
| `images` | base | **DEPRECATED** — reemplazada por media_assets |
| `news` | pasarela | nueva tabla principal de noticias |
| `events` | pasarela | nueva tabla principal de eventos |
| `media_assets` | pasarela | sistema polimórfico webp + thumbnails |
| `organizations` | pasarela | |
| `organization_members` | pasarela | |
| `venues` | pasarela | |
| `event_interprete` | pasarela | pivot Event ↔ Interprete |
| `interprete_noticia` | base | pivot Interprete ↔ News (reutilizada) |
| `social_accounts` | pasarela | |
| `publication_requests` | pasarela | |
| `publication_targets` | pasarela | |
| `publication_attempts` | pasarela | |
| `publication_templates` | pasarela | |
| `notifications` | pasarela | |
| `audit_logs` | pasarela | columnas: old_values, new_values, ip_address (no _json) |
| `jobs` | pasarela | QUEUE_CONNECTION=database |
| `moderation_reviews` | pasarela | |
| `categories`, `classifieds`, `tags`, `classified_tag` | clasificados | |
| `categorias` | base | para noticias legacy |

### Estado de los datos
⚠️ **La BD está vacía de datos reales** (recreada el 2026-04-10). El usuario necesita recargar sus datos.

---

## Modelos principales

### `News` (tabla: `news`)
Reemplaza el modelo legacy `Noticia` (que ahora es un alias deprecated).

**Accessors de compatibilidad** (para vistas y controllers legacy):
- `$noticia->titulo` → `title`
- `$noticia->noticia` → `body`
- `$noticia->foto` → `featured_image_path`
- `$noticia->fecha_publicacion` → `published_at ?? created_at`

**Relaciones:**
- `categoria()` / `category()` → `Categoria` via `categoria_id`
- `interprete()` → `Interprete` via `interprete_id` (principal)
- `interpretes()` → belongsToMany `Interprete` via `interprete_noticia` (secundarios)
- `images()` / `media()` → morphMany `MediaAsset` via HasMedia trait

**Campos clave:** `title`, `body`, `slug`, `editorial_status`, `featured_image_path`, `interprete_id`, `categoria_id`, `visitas`, `estado`

---

### `Event` (tabla: `events`)
Reemplaza el modelo legacy `Show` (que ahora es un alias deprecated).

**Accessors de compatibilidad:**
- `$show->titulo` → `title`
- `$show->show` → `title`
- `$show->fecha` → `start_at`
- `$show->lugar` → `city`
- `$show->direccion` → `address`
- `$show->detalle` / `$show->detalles` → `body`
- `$show->interprete` → `interpretes->first()` (requiere eager-load)

**Relaciones:**
- `interpretes()` → belongsToMany `Interprete` via `event_interprete`
- `provincia()` → `Provincia` via `province_id`
- `venue()` → `Venue`
- `images()` / `media()` → morphMany `MediaAsset` via HasMedia trait

**Campos clave:** `title`, `body`, `slug`, `editorial_status`, `start_at`, `city`, `address`, `province_id`

---

### `MediaAsset` (tabla: `media_assets`)
Sistema de imágenes polimórfico con webp y thumbnails.

**Columnas reales en BD:** `original_path`, `original_width`, `original_height`, `alt`, `mime`, `variants_json`
**NO existen:** `alt_text`, `mime_type`, `width`, `height`, `path`

`variants_json` contiene un mapa `{ "card": { "320": "/url/320.webp", ... }, "detail": {...} }`

---

### Modelos deprecated (siguen funcionando como alias)
- `Noticia` → extiende `News`, tabla `news`
- `Show` → extiende `Event`, tabla `events`
- `Image` → extiende `MediaAsset`, tabla `media_assets`

---

## Controladores frontend

| Controlador | Modelo | Filtro activo |
|-------------|--------|---------------|
| `HomeController` | `Noticia` / `Show` (alias) | `estado=1` para noticias, `editorial_status=approved` + `start_at>=now` para shows |
| `NoticiasController` | `News` | `editorial_status=approved` |
| `ShowsController` | `Event` | `editorial_status=approved` + `start_at>=now` |
| `InterpretesController` | `Interprete` | `estado=1` |

---

## Tests

- **Ubicación:** `tests/Feature/Pasarela/`
- **Total:** 74 tests, todos pasando
- **Trait obligatorio:** `DatabaseTransactions` (NUNCA `RefreshDatabase`)
- **BD de test:** `mfa` (misma que producción local, via transacciones que se revierten)

| Archivo de test | Cobertura |
|-----------------|-----------|
| `SocialAccountTest.php` | PC-06-HU-01 — 9 tests |
| `PublicationRequestTest.php` | PC-07-HU-01 — 12 tests |
| `PublicationTargetTest.php` | PC-07-HU-02 — 10 tests |
| `PublishToProviderJobTest.php` | PC-07-HU-03/04/05/06/07 — 11 tests |
| `PublicationTemplateTest.php` | PC-08-HU-01 — 11 tests |
| `EventReminderJobTest.php` | PC-09-HU-01 — 4 tests |
| `DashboardPublicadorTest.php` | PC-10-HU-01 — 4 tests |
| `DashboardAdminTest.php` | PC-11-HU-01 — 2 tests |
| `NotificationTest.php` | PC-12-HU-01 — 7 tests |
| `AuditLogTest.php` | PC-13-HU-01 — 4 tests |

---

## Servicios y conectores

| Clase | Responsabilidad |
|-------|----------------|
| `PublicationService` | Orquesta publication_requests y genera targets |
| `TemplateService` | Resuelve template por content_type + provider + variant |
| `ImageUploadService` | Sube imágenes, genera variantes webp, guarda MediaAsset |
| `NativePortalConnector` | Publica en el portal (marca editorial_status=published) |
| `FacebookConnector` | Graph API v19.0 |
| `InstagramConnector` | 2-step: media upload + publish |
| `TelegramConnector` | Bot API sendMessage HTML |

---

## Infraestructura Docker

| Contenedor | Rol |
|------------|-----|
| `lmfa-app-1` | PHP 8.2 / Laravel 10 |
| `lmfa-db-1` | MariaDB 10.8 |
| `lmfa-phpmyadmin-1` | phpMyAdmin |

**Comando para ver logs de DB si crashea:**
```bash
docker logs lmfa-db-1 --tail 50
```
