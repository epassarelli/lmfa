# 00 - Estado Actual del Proyecto

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesion de trabajo.
> Ultima actualizacion: 2026-08-06 (Infraestructura de sitemaps separada por tipo, noticias legacy tolerantes a fechas faltantes y redireccion canonica www->apex en Laravel)

---

## Rama activa

`v2` - rama de version mayor salida de `dev`. Contiene la unificacion arquitectonica, la Pasarela de Contenidos y el nuevo `NewsService` unificado.

**Flujo de ramas:** `dev` -> rama feature/version -> validar -> merge a `dev` -> deploy desde `main`.

---

## Estado del proyecto

Backlog original completo (PC-01 a PC-13 en `done`). El proyecto esta en fase de **estabilizacion y auditoria** previo a definir specs de v2.

Se realizo auditoria completa del codigo el 2026-04-26. Ver seccion de bugs corregidos.

---

## Base de datos

**Motor:** MariaDB 10.8 en Docker (`lmfa-db-1`).

### Tablas activas (se leen y escriben desde la app)

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `news` | `News` | Activa - modelo canonico para noticias. Campo principal: `editorial_status`. |
| `events` | `Event` | Activa - modelo canonico para eventos/shows. Campo principal: `start_at`. |
| `interpretes` | `Interprete` | Activa |
| `canciones` | `Cancion` | Activa |
| `albunes` | `Album` | Activa |
| `festivales` | `Festival` | Activa |
| `mitos` | `Mito` | Activa |
| `comidas` | `Comida` | Activa |
| `classifieds` | `Classified` | Activa |
| `contributions` | `Contribution` | Activa - flujo UGC a verificar end-to-end |
| `newsletter_subscribers` | `NewsletterSubscriber` | Activa |
| `organizations` | `Organization` | Activa - Pasarela |
| `organization_members` | `OrganizationMember` | Activa - Pasarela |
| `social_accounts` | `SocialAccount` | Activa - Pasarela |
| `publication_requests` | `PublicationRequest` | Activa - Pasarela |
| `publication_targets` | `PublicationTarget` | Activa - Pasarela |
| `publication_attempts` | `PublicationAttempt` | Activa - Pasarela |
| `publication_templates` | `PublicationTemplate` | Activa - Pasarela |
| `moderation_reviews` | `ModerationReview` | Activa |
| `audit_logs` | `AuditLog` | Activa |
| `notifications` | `UserNotification` | Activa |
| `knowledge_categories` | `KnowledgeCategory` | Activa - taxonomia evergreen de Enciclopedia |
| `knowledge_articles` | `KnowledgeArticle` | Activa - base editorial de `/enciclopedia` |
| `knowledge_article_interprete` | Pivot | Activa - relacion articulos/artistas |
| `knowledge_article_cancion` | Pivot | Activa - relacion articulos/canciones |
| `knowledge_article_album` | Pivot | Activa - relacion articulos/discos |
| `knowledge_article_festival` | Pivot | Activa - relacion articulos/festivales |
| `event_knowledge_article` | Pivot | Activa - relacion articulos/eventos |
| `knowledge_article_provincia` | Pivot | Activa - relacion articulos/provincias |
| `knowledge_article_related` | Pivot | Activa - enlazado interno entre articulos |

### Tablas legacy (existen en BD, no se usan desde la app)

| Tabla | Estado |
|-------|--------|
| `noticias` | Datos previos a la migracion a `news`. No se lee ni escribe. Evaluar limpieza. |
| `shows` | Vaciada al migrar a `events`. Candidata a eliminar. |
| `images` | Reemplazada por `media_assets`. |

### Nota critica - compatibilidad legacy

Los modelos `News` y `Event` tienen accessors que mapean nombres de campos viejos (`titulo`, `noticia`, `fecha`, `estado`) a los nuevos (`title`, `body`, `start_at`, `editorial_status`). Estos solo funcionan en instancias, no en queries `where`. Siempre usar el nombre canonico en consultas.

### Nota tecnica - DDL en entorno local

MariaDB 10.8.8 en este entorno Docker/WSL se cae con ciertos `ALTER TABLE ... ADD UNIQUE` sobre tablas nuevas InnoDB. Como mitigacion duradera, la migracion `2026_08_04_010200_create_knowledge_article_relationship_tables.php` crea pivotes e indices inline con SQL explicito.

---

## Arquitectura de rutas

| Archivo | Middleware | Proposito |
|---------|------------|-----------|
| `routes/web.php` | `web` | Frontend publico + colaboraciones UGC |
| `routes/admin.php` | `web`, `auth` | Panel admin + Pasarela de Contenidos bajo `/admin` |
| `routes/api.php` | `api`, `auth:sanctum` | API REST v1 |

**Nomenclatura:** rutas admin usan prefijo `backend.*` (ej: `backend.events.index`). Pasarela usa `pasarela.*`.

---

## Estado por modulo

### Completamente funcional

Backend CRUD: Events, News (unificado), Enciclopedia, Interpretes, Albumes, Canciones, Comidas, Festivales, Mitos, Categorias, Tags, Clasificados, Moderacion, Newsletter, Roles, Users y Permissions.

Frontend: Home, Noticias (unificado), Enciclopedia, Cartelera, Miniportal artista, Canciones, Discografia, Festivales, Mitos, Recetas, Clasificados, Contacto, Buscador, Sitemap, Newsletter y Social Auth.

API REST: `news`, `knowledge-articles`, `knowledge-categories`, `albums`, `songs`, `foods`, `festivals`, `artists`, `myths` y `events`.

### Implementado y validado localmente

- **Enciclopedia del folklore argentino**:
  - Frontend publico en `/enciclopedia`, `/enciclopedia/{categorySlug}` y `/enciclopedia/{categorySlug}/{articleSlug}`
  - Backend admin en `/admin/knowledge-articles`
  - API REST en `/api/v1/knowledge-articles` y `/api/v1/knowledge-categories`
  - Sitemap integrado para portada, familias activas y articulos publicados
  - Taxonomia inicial cargada por `KnowledgeCategorySeeder`
  - Relaciones editoriales con `Interprete`, `Cancion`, `Album`, `Festival`, `Event`, `Provincia` y articulos relacionados
  - Cobertura local validada con tests feature del modulo

### Implementado, sin probar en produccion

- **Pasarela de Contenidos** (`/admin/pasarela`): social accounts, publication requests, templates, notificaciones y dashboards. Codigo completo, nunca probado end-to-end.
- **Colaboraciones UGC** (`/admin/contribuir`): flujo unificado con `NewsService` para Noticias. Verificado end-to-end.

### Modulos diferidos - proxima version

- **Entrevistas**: rutas activas en `web.php`, controller sin metodos `byArtista`/`show`, sin vistas, modelo inexistente.
- **Radios**: indice funcional, detalle sin implementar, sin backend.
- **Penas**: idem Radios.

---

## Bugs corregidos

### 2026-04-26

| Archivo | Problema | Fix |
|---------|----------|-----|
| `BusquedaController` | Usaba columnas inexistentes en DB para Noticias y Shows | Cambiado a `News::where('title', ...)` y `Event::where('title', ...)` |
| `SitemapController` | Consultaba `events` con campos legacy `estado` y `fecha` | Cambiado a `editorial_status` y `start_at` |
| `SitemapController` | `newsIndex()` usaba `Noticia::where('estado', 1)` | Cambiado a `News::where('editorial_status', 'published')` |

### 2026-08-04

| Archivo | Problema | Fix |
|---------|----------|-----|
| `app/Http/Controllers/Backend/ContributionController.php` | Archivo sintacticamente roto, bloqueaba comandos Artisan globales | Reescritura completa del controlador y flujo de aprobacion/rechazo |
| `database/migrations/2026_08_04_010200_create_knowledge_article_relationship_tables.php` | Tipos FK incompatibles con tablas legacy y DDL inestable en MariaDB local | Migracion reescrita con tipos reales e indices inline creados por SQL explicito |
| `app/Http/Controllers/Api/KnowledgeArticleController.php` | La API dependia del middleware de ruta y no reforzaba policy en controlador | Se agregaron autorizaciones explicitas por policy |

### 2026-08-06

| Archivo | Problema | Fix |
|---------|----------|-----|
| `app/Http/Controllers/Frontend/SitemapController.php` + `routes/web.php` | `sitemap.xml` indexaba solo un sitemap general y un sitemap news acoplados; habia mezcla de familias y compatibilidad legacy poco clara | Se separo la arquitectura en sitemaps por tipo (`estaticas`, `artistas`, `biografias`, `noticias`, `google-news`, `eventos`, `festivales`, `discografias`, `letras`, `evergreen`) y se agregaron redirects 301 desde `/sitemap-main.xml` y `/sitemap-news.xml` |
| `resources/views/sitemap-*.blade.php` | XML de sitemaps con estructura fija y metadatos no semanticos para todas las familias | Se reemplazo por vistas especificas para sitemapindex, urlset generico y Google News usando fechas tolerantes a legacy |
| `app/Http/Controllers/Frontend/NoticiasController.php` + `resources/views/frontend/noticias/show.blade.php` | Noticias publicadas legacy podian responder 500 por `created_at` nulo y metadatos SEO inflexibles | Se reforzo el filtro publico/no futuro y se agregaron fallbacks seguros para `published_at` / `created_at` / `updated_at` sin ocultar 404 reales |
| `app/Http/Middleware/EnforceCanonicalDomain.php` + `app/Http/Kernel.php` | La consolidacion de `www` a `https://mifolkloreargentino.com` dependia solo de Apache y no estaba garantizada cuando la request llegaba a Laravel | Se agrego middleware global posterior a `TrustProxies` para forzar host/protocolo canonicos respetando `X-Forwarded-*` y manteniendo ruta + query string |

---

## Archivos eliminados - 2026-04-26

| Archivo | Motivo |
|---------|--------|
| `routes/pasarela.php` | Duplicado no cargado - rutas ya en `admin.php` |
| `routes/_web.php` | Backup antiguo de `web.php`, no cargado |
| `app/Http/Controllers/Backend/ShowController.php` | Reemplazado por `EventController`, tenia bugs de tipo |
| `app/Http/Controllers/Backend/NoticiaController.php` | Reemplazado por `NewsController` |

---

## Modelos y politicas

- **`EventPolicy`**: CRUD de `Event`. Admins con acceso total, colaboradores pueden crear/editar propios.
- **`NewsPolicy`**: CRUD de `News`. Compatibilidad con permisos legacy `read noticia`.
- **`KnowledgeArticlePolicy`**: CRUD de `KnowledgeArticle`. Reutiliza permisos legacy de Noticias y acceso total para `administrador`.
- Las tres estan registradas en `AuthServiceProvider`.

---

## Validacion local reciente

- `php artisan migrate:status`
- `php artisan test tests/Feature/Knowledge/KnowledgeAuthorizationTest.php tests/Feature/Knowledge/KnowledgeCategorySeederTest.php tests/Feature/Knowledge/KnowledgeArticleApiTest.php tests/Feature/Knowledge/KnowledgeFrontendVisibilityTest.php`

Resultado mas reciente: `9 passed (18 assertions)`.

---

## Pendientes identificados

1. Probar Pasarela de Contenidos end-to-end en browser.
2. Extender `ImageSourceResolver` a otros modulos como Artistas y Festivales para unificar sus flujos de contribucion.
3. Evaluar limpieza de tablas legacy (`noticias`, `shows`, `images`) despues de confirmar que no exista informacion unica.
4. Verificar si conviene actualizar la imagen de MariaDB del stack local para evitar el bug de DDL observado en WSL/Docker.
5. Completar `AGENTS.md` con secciones operativas pendientes del plan aprobado.
