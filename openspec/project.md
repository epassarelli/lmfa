# Contexto del Proyecto — Mi Folklore Argentino

> Este archivo es la fuente de contexto para agentes IA al crear y evaluar specs OpenSpec.
> Actualizar cuando cambie el stack, los módulos activos o las convenciones.
> Última actualización: 2026-04-26

---

## Dominio

Portal cultural argentino enfocado en folklore: noticias, artistas, discografía, canciones, festivales, mitos, recetas (comidas), clasificados, cartelera de shows. Incluye sistema UGC (contribuciones de usuarios) y Pasarela de Contenidos para publicación en redes sociales.

URL producción: https://mifolkloreargentino.com.ar

---

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 10, PHP 8.2 |
| Base de datos | MariaDB 10.8 (Docker local: `lmfa-db-1`) |
| Frontend público | Blade clásico + Tailwind CSS 3.x |
| Panel admin | AdminLTE 3 + Blade |
| Auth | Laravel Auth + OAuth (Google, Facebook) vía Socialite |
| Permisos | Spatie Laravel Permission (roles + permisos granulares) |
| API | Laravel Sanctum (tokens) |
| Colas | `QUEUE_CONNECTION=database` — nunca Redis |
| Imágenes | Servicio propio `ImageUploadService` + `image_profiles.php` |
| Livewire | Solo donde ya existe; no agregar sin autorización |

**No usar:** React, Vue, Bootstrap (es contaminación de agentes anteriores — reportar si se encuentra).

---

## Arquitectura de rutas

| Archivo | Prefijo | Middleware | Uso |
|---------|---------|------------|-----|
| `routes/web.php` | `/` | `web` | Frontend público + contribuciones UGC |
| `routes/admin.php` | `/admin` | `web`, `auth` | Panel admin + Pasarela |
| `routes/api.php` | `/api/v1` | `api`, `auth:sanctum` | API REST |

Nomenclatura de rutas: `backend.*` para admin, `pasarela.*` para Pasarela de Contenidos, sin prefijo para frontend público.

---

## Modelos activos y sus tablas

| Modelo | Tabla | Notas |
|--------|-------|-------|
| `News` | `news` | Modelo canónico para noticias. Campo clave: `editorial_status` |
| `Event` | `events` | Modelo canónico para shows/cartelera. Campo clave: `start_at` |
| `Interprete` | `interpretes` | Artistas. Estado activo: `estado = 1` |
| `Cancion` | `canciones` | Estado activo: `estado = 1` |
| `Album` | `albunes` | Estado activo: `estado = 1` |
| `Festival` | `festivales` | Estado activo: `estado = 1` |
| `Mito` | `mitos` | Estado activo: `estado = 1` |
| `Comida` | `comidas` | Estado activo: `estado = 1` |
| `Classified` | `classifieds` | Estados: `pendiente`, `activo`, `rechazado` |
| `Contribution` | `contributions` | UGC. Estados: `pending`, `approved`, `rejected` |
| `NewsletterSubscriber` | `newsletter_subscribers` | |
| `Organization` | `organizations` | Pasarela |
| `OrganizationMember` | `organization_members` | Pasarela |
| `SocialAccount` | `social_accounts` | Pasarela |
| `PublicationRequest` | `publication_requests` | Pasarela |
| `PublicationTarget` | `publication_targets` | Pasarela |
| `PublicationAttempt` | `publication_attempts` | Pasarela |
| `PublicationTemplate` | `publication_templates` | Pasarela |
| `ModerationReview` | `moderation_reviews` | |
| `AuditLog` | `audit_logs` | |
| `UserNotification` | `notifications` | |

### Modelos alias (compatibilidad — no usar en código nuevo)

- `Show extends Event` → tabla `events`
- `Noticia extends News` → tabla `news`

### Tablas legacy (existen en BD, no usar desde la app)

- `noticias` — 347 filas de antes de la migración a `news`
- `shows` — vaciada al migrar a `events`

---

## Convención de campos — News y Event

**Nombres canónicos** (usar siempre en queries):

| Modelo | Campo canónico | Campo legacy (accessor solo en instancia) |
|--------|---------------|------------------------------------------|
| `News` | `title` | `titulo` |
| `News` | `body` | `noticia` |
| `News` | `editorial_status` | `estado` (real en BD pero con valores distintos) |
| `Event` | `title` | `titulo`, `show` |
| `Event` | `start_at` | `fecha` |
| `Event` | `editorial_status` | `estado` (NO existe como columna real) |

> **Crítico:** Los accessors funcionan en instancias de modelo pero NO en cláusulas `WHERE`. Usar siempre el nombre canónico en queries.

### Estados editoriales (News y Event — campo `editorial_status`)

`draft` → `pending_review` → `approved` → `published` → `archived` / `rejected`

Solo los registros con `editorial_status = 'published'` son visibles en el frontend.

---

## Módulos — estado actual

### Completamente funcionales ✅
Backend CRUD: Events, News, Interpretes, Albumes, Canciones, Comidas, Festivales, Mitos, Categorias, Tags, Clasificados, Moderacion, Newsletter, Roles, Users.
Frontend: Home, Noticias, Cartelera, Miniportal artista, Canciones, Discografía, Festivales, Mitos, Recetas, Clasificados, Contacto, Buscador, Sitemap, Newsletter, Social Auth.
API REST: news, albums, songs, foods, festivals, artists, myths.

### Implementados, sin probar end-to-end ⚠️
- **Pasarela de Contenidos** (`/admin/pasarela`): código completo, nunca probado en browser.
- **Colaboraciones UGC** (`/admin/contribuir`): flujo migrado, requiere verificación completa.

### Diferidos — no implementar sin spec ❌
- **Entrevistas**: rutas activas, controller sin métodos, sin vistas, sin modelo.
- **Radios**: índice funcional, detalle sin implementar, sin backend.
- **Peñas**: ídem Radios.

---

## Convenciones de código

- **Controllers backend**: `app/Http/Controllers/Backend/`
- **Controllers frontend**: `app/Http/Controllers/Frontend/`
- **Controllers API**: `app/Http/Controllers/Api/`
- **Controllers Pasarela**: `app/Http/Controllers/Pasarela/`
- **Vistas backend**: `resources/views/backend/` (AdminLTE)
- **Vistas frontend**: `resources/views/frontend/` (Tailwind)
- **Form Requests**: siempre para validación en store/update
- **Policies**: registradas en `AuthServiceProvider`
- **Slugs**: generados automáticamente desde el nombre/título
- **Imágenes**: vía `ImageUploadService`, perfiles en `config/image_profiles.php`

---

## Entorno de desarrollo

- Docker: `docker-compose up -d`
- App: http://localhost
- phpMyAdmin: http://localhost:8080
- DB: `mysql -umfa -pmfa mfa` en contenedor `lmfa-db-1`
- Logs: `docker-compose logs -f app`

---

## Lo que NO hacer

- No usar `RefreshDatabase` en tests (crashea MariaDB en Docker/Windows)
- No ejecutar `php artisan migrate` sin mostrar `migrate:status` primero
- No modificar rutas SEO sin evaluar impacto de indexación
- No inventar columnas, modelos o relaciones — verificar siempre contra la BD
- No usar Bootstrap en el frontend público (stack es Tailwind CSS 3.x)
- No agregar Livewire donde no existe sin autorización
