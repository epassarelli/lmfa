# 00 — Estado Actual del Proyecto

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesión de trabajo.
> Última actualización: 2026-04-14 (sesión 2 — shows/events)

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

### Script de migración de datos legacy:
```bash
# Migra los 347 registros de 'noticias' → 'news' (idempotente, seguro de re-ejecutar)
docker exec -i lmfa-db-1 mysql -umfa -pmfa mfa < database/migrate_noticias_to_news.sql
```
**⚠️ PENDIENTE DE EJECUTAR:** La tabla `noticias` tiene 347 registros reales. La tabla `news` está vacía. El usuario debe ejecutar el script arriba para migrar los datos.

### Tablas existentes
| Tabla | Origen | Notas |
|-------|--------|-------|
| `users` | base | + campos publisher: phone, status, is_verified_publisher, publisher_type_default, last_login_at, points, rank |
| `interpretes` | base | sin cambios |
| `noticias` | base | **DEPRECATED** — 347 registros legacy, sin usar por el portal activo. Migrar con `migrate_noticias_to_news.sql` |
| `shows` | base | **DEPRECATED** — datos legacy, sin usar por el portal activo |
| `images` | base | **DEPRECATED** — reemplazada por media_assets |
| `news` | pasarela | nueva tabla principal de noticias — **actualmente vacía, pendiente migración** |
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
⚠️ **La tabla `news` está vacía.** Los datos reales están en `noticias` (347 registros). Ejecutar `migrate_noticias_to_news.sql` para migrarlos.

---

## Modelos principales

### `News` (tabla: `news`)
Reemplaza el modelo legacy `Noticia` (que ahora es un alias deprecated).

**Accessors de compatibilidad** (para vistas y controllers legacy):
- `$noticia->titulo` → `title`
- `$noticia->noticia` → `body`
- `$noticia->foto` → `featured_image_path`
- `$noticia->fecha_publicacion` → `published_at ?? created_at`
- `$noticia->user_id` → `created_by` (mutator + accessor agregados en 2026-04-14)
- `$noticia->publicar` → `published_at` (mutator + accessor agregados en 2026-04-14)

**Relaciones:**
- `categoria()` / `category()` → `Categoria` via `categoria_id`
- `interprete()` → `Interprete` via `interprete_id` (principal)
- `interpretes()` → belongsToMany `Interprete` via `interprete_noticia` (secundarios)
- `images()` / `media()` → morphMany `MediaAsset` via HasMedia trait
- `creator()` → `User` via `created_by`
- `user()` → alias de `creator()` para compatibilidad con eager-loads legacy (agregado 2026-04-14)

**Campos clave:** `title`, `body`, `slug`, `editorial_status`, `featured_image_path`, `interprete_id`, `categoria_id`, `visitas`, `estado`

---

### `Event` (tabla: `events`)
Reemplaza el modelo legacy `Show` (que ahora es un alias deprecated).

**Accessors de compatibilidad:**
- `$show->titulo` → `title`
- `$show->show` → `title` (getter + setter)
- `$show->fecha` → `start_at` (getter + setter)
- `$show->lugar` → `city` (getter + setter)
- `$show->direccion` → `address` (getter + setter)
- `$show->detalle` / `$show->detalles` → `body` (getter + setter)
- `$show->user_id` → `created_by` (getter + setter)
- `$show->publicar` → `published_at` (getter + setter)
- `$show->estado` → `1` si `editorial_status='published'`, `0` si `'draft'` (getter + setter)
- `$show->interprete` → `interpretes->first()` (requiere eager-load)

**Relaciones:**
- `interpretes()` → belongsToMany `Interprete` via `event_interprete` (**sin** `withTimestamps()` — la tabla pivot no tiene esas columnas)
- `creator()` / `user()` → `User` via `created_by`
- `provincia()` → `Provincia` via `province_id`
- `venue()` → `Venue`
- `images()` / `media()` → morphMany `MediaAsset` via HasMedia trait

**Campos clave (canónicos):** `title`, `body`, `slug`, `editorial_status`, `start_at`, `city`, `address`, `province_id`, `ticket_url`, `price_text`, `is_free`

**Pivot `event_interprete`:** columnas `event_id`, `interprete_id`, `role`, `sort_order` — **sin timestamps**.

---

### `MediaAsset` (tabla: `media_assets`)
Sistema de imágenes polimórfico con webp y thumbnails.

**Columnas reales en BD:** `original_path`, `original_width`, `original_height`, `alt`, `mime`, `variants_json`
**NO existen:** `alt_text`, `mime_type`, `width`, `height`, `path`

`variants_json` contiene un mapa `{ "card": { "320": "/url/320.webp", ... }, "detail": {...} }`

**Perfil `news_full`** (config/image_profiles.php):
- `card`: 16:9, cover → 320px / 480px / 768px
- `detail`: 16:9, cover → 768px / 1200px / 1600px
- `sidebar`: 1:1, cover → 120px / 240px / 320px

La API de noticias (`POST /api/v1/news`) acepta `foto` solo como **string URL** — no procesa imágenes. El procesamiento webp ocurre únicamente vía el backend web (formulario admin).

---

### Modelos deprecated (siguen funcionando como alias)
- `Noticia` → extiende `News`, tabla `news`
- `Show` → extiende `Event`, tabla `events`
- `Image` → extiende `MediaAsset`, tabla `media_assets`

---

## Controladores frontend

| Controlador | Modelo | Filtro activo |
|-------------|--------|---------------|
| `HomeController` | `Noticia` / `Show` (alias) | `estado=1` para noticias, `editorial_status=published` + `start_at>=now` para shows |
| `NoticiasController` | `News` | `editorial_status=published` |
| `ShowsController` | `Event` | `editorial_status=published` + `start_at>=now` |
| `InterpretesController` | `Interprete` | `estado=1` |

> **Nota:** El filtro correcto para el portal público es `editorial_status='published'`. El estado `'approved'` es intermedio (moderación aprobada, pendiente de publicar via pasarela). Los controllers frontend fueron corregidos el 2026-04-14 para usar `published`.

---

## API REST

**Base URL:** `/api/v1`
**Autenticación:** Laravel Sanctum — Bearer token

**Obtener token (una sola vez, guardar el valor):**
```php
// En artisan tinker dentro del contenedor lmfa-app-1:
$user = \App\Models\User::find(1);
$token = $user->createToken('automatizacion')->plainTextToken;
echo $token;
```

**Endpoints de noticias:**
```
GET    /api/v1/news              → lista paginada (filtros: categoria_id, estado)
POST   /api/v1/news              → crear noticia
GET    /api/v1/news/{id}         → detalle
PUT    /api/v1/news/{id}         → actualizar
DELETE /api/v1/news/{id}         → eliminar
```

**JSON canónico para POST/PUT:**
```json
{
  "title": "Título",
  "body": "Contenido",
  "categoria_id": 1,
  "created_by": 1,
  "interprete_id": null,
  "featured_image_path": "https://...",
  "published_at": "2026-04-14T00:00:00",
  "editorial_status": "published",
  "estado": "1"
}
```
Los nombres legacy (`titulo`, `noticia`, `foto`, `user_id`, `publicar`) también son aceptados vía accessors/mutators del modelo.

---

## Tests

- **Ubicación:** `tests/Feature/Pasarela/`
- **Total:** 100 tests, todos pasando (actualizado 2026-04-14 sesión 2)
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
| `NoticiasBugFixTest.php` | Bugs noticias/news 2026-04-14 — 10 tests |
| `ShowsBugFixTest.php` | Bugs shows/events 2026-04-14 — 16 tests |

---

## Bugs corregidos — sesión 2026-04-14 (noticias/news)

| # | Síntoma | Causa | Fix |
|---|---------|-------|-----|
| a | `admin/noticias` → `Call to undefined relationship [user]` | NoticiaController eager-load `'user'` pero News solo tenía `creator()` | Agregado alias `user()` en News model |
| b | `interprete/noticias` → `Undefined variable $metaTitle` | NoticiasController@noticias() y @byArtista() no definían $metaTitle antes de compact() | Definidas las variables en ambos métodos |
| c | `/noticias-del-folklore-argentino` no mostraba noticias | Controllers frontend filtraban `editorial_status='approved'` pero el portal usa `'published'` | Cambiado a `'published'` en NoticiasController (8 ocurrencias), ShowsController (2) y HomeController (1) |
| — | `NoticiaRequest` slug unique apuntaba a tabla `noticias` | `Rule::unique('noticias', 'slug')` en tabla deprecated | Cambiado a `unique('news', 'slug')` |
| — | `NoticiaController` usaba nombres de campo viejos | `user_id`, `publicar`, `orderBy('publicar')` en controlador backend | Corregidos a `created_by`, `published_at` |
| — | API: `created_by` nunca se persistía | `user_id` no estaba en $fillable ni tenía mutator | Agregados mutators `user_id`/`publicar` al modelo y al $fillable |

---

## Bugs corregidos — sesión 2026-04-14 (shows/events)

| # | Síntoma | Causa | Fix |
|---|---------|-------|-----|
| 1 | `abel-pintos` → `SQLSTATE Column not found: event_interprete.created_at` | `Event::interpretes()` e `Interprete::events()` usaban `->withTimestamps()` pero la tabla pivot no tiene esas columnas | Removido `withTimestamps()` en ambos modelos |
| 2 | `admin/shows` → `Column not found: fecha in 'where clause'` | `ShowController@index` filtraba por `where('fecha')` y `where('user_id')` — columnas inexistentes en `events` | Cambiado a `where('start_at')` y `where('created_by')` |
| 3 | `ShowRequest` slug unique apuntaba a tabla `shows` | `unique:shows,slug` en tabla deprecated | Cambiado a `unique:events,slug` |
| 4 | `InterpretesController@show` query de shows fallaba | `->where('fecha', '>=', now())` sobre columna virtual (accessor, no columna real) | Cambiado a `->where('start_at', '>=', now())` |
| 5 | `byArtista.blade.php` → `Undefined property: descripcion` | Vista usaba `$evento->descripcion`, campo inexistente en `events` | Cambiado a `@if($evento->excerpt)` |
| 6 | `show-card.blade.php` → error null en artista sin intérprete | `$show->interprete->interprete` sin null-safe | Refactorizado con variable temporal y operador `?->` |
| 7 | Backend `index.blade.php` mostraba error en shows sin intérprete | `$show->interprete->interprete` sin null-safe | Cambiado a `$show->interprete?->interprete ?? '—'` |
| 8 | `Event` model sin mutators de compatibilidad | El modelo no tenía setters para `user_id`, `publicar`, `estado`, `fecha`, `lugar`, `direccion` | Agregados todos los setters + accessor `getEstadoAttribute` |

---

## Modernización del formulario backend — 2026-04-14

El formulario `resources/views/backend/shows/form.blade.php` fue actualizado de los nombres legacy del esquema `shows` a los nombres canónicos de `events`:

| Campo anterior | Campo nuevo | Notas |
|----------------|-------------|-------|
| `show` | `title` | |
| `fecha` (date) + `hora` (time) | `start_at` (datetime-local) | Combinados en un solo input |
| `detalle` | `body` | |
| `lugar` | `city` | |
| `direccion` | `address` | |
| `publicar` | `published_at` | |
| `interprete_id` (select simple, FK directa) | `interprete_id` (select, sync a pivot M2M) | Lee `$show->interpretes->first()?->id` |
| `provincia_id` (comentado) | `province_id` (activo) | |
| `link_entradas` (hidden) | `ticket_url` (visible) | |
| `precio_entrada` (hidden) | `price_text` (visible) | |
| — | `is_free` (checkbox) | Nuevo campo |

`ShowRequest.php` y `ShowController.php` actualizados en consecuencia.

---

## Datos de prueba — events

5 shows insertados directamente en `events` con `editorial_status='published'` para pruebas de frontend:

| ID | Título | Intérprete | Fecha | Ciudad |
|----|--------|-----------|-------|--------|
| 193 | Noche de Folklore en el Gran Rex | Abel Pintos | 10/05/2026 | Buenos Aires |
| 194 | Ahyre en Cosquín | Ahyre | 25/07/2026 | Cosquín |
| 195 | Abel Pintos — Estadio Único de La Plata | Abel Pintos | 06/06/2026 | La Plata |
| 196 | Peña de Los Sentidos | 5 sentidos | 30/05/2026 | Buenos Aires |
| 197 | Abi González — Debut en el ND/Ateneo | Abi González | 15/08/2026 | Buenos Aires |

---

## Servicios y conectores

| Clase | Responsabilidad |
|-------|----------------|
| `PublicationService` | Orquesta publication_requests y genera targets |
| `TemplateService` | Resuelve template por content_type + provider + variant |
| `ImageUploadService` | Sube imágenes, genera variantes webp, guarda MediaAsset |
| `NativePortalConnector` | Publica en el portal → pone `editorial_status='published'` |
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
