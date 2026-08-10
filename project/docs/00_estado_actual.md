# 00 - Estado Actual del Proyecto

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesion de trabajo.
> Ultima actualizacion: 2026-08-10 (BL-0011B corregida en Laravel para categorias evergreen por slug/nombre/ID, mas tanda integral de performance backend/frontend/mobile y AGENTS.md consolidado)

---

## Rama activa

`main` - rama activa actual. Contiene la unificacion arquitectonica, la Pasarela de Contenidos, el nuevo `NewsService` unificado, la infraestructura SEO/sitemaps y la tanda de optimizacion de performance realizada el 2026-08-09/10.

**Flujo de ramas:** `dev` -> rama feature/version -> validar -> merge a `dev` -> deploy desde `main`.

---

## Estado del proyecto

Backlog original completo (PC-01 a PC-13 en `done`). El proyecto esta en fase de **estabilizacion, optimizacion y auditoria** previo a nuevas specs funcionales.

Se realizo auditoria completa del codigo el 2026-04-26. Ver seccion de bugs corregidos.

---

## Base de datos

**Motor:** MariaDB 10.8 en Docker (`lmfa-db-1`).

### Tablas activas de dominio (leidas y/o escritas por la app)

#### Contenido editorial principal

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `news` | `News` | Activa - modelo canonico para noticias. Campo principal: `editorial_status`. |
| `events` | `Event` | Activa - modelo canonico para eventos/shows. Campo principal: `start_at`. |
| `interpretes` | `Interprete` | Activa |
| `albunes` | `Album` | Activa |
| `canciones` | `Cancion` | Activa |
| `festivales` | `Festival` | Activa |
| `mitos` | `Mito` | Activa |
| `comidas` | `Comida` | Activa |
| `knowledge_categories` | `KnowledgeCategory` | Activa - taxonomia evergreen de Enciclopedia |
| `knowledge_articles` | `KnowledgeArticle` | Activa - base editorial de `/enciclopedia` |

#### Clasificados y taxonomias auxiliares

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `classifieds` | `Classified` | Activa |
| `categories` | `Category` | Activa - categorias de clasificados |
| `tags` | `Tag` | Activa - etiquetas de clasificados |
| `classified_tag` | `ClassifiedTag` / pivot | Activa - relacion N:M clasificados/tags |
| `categorias` | `Categoria` | Activa - categorias legacy/canonicas usadas por `news` |

#### Geografia y taxonomias de navegacion

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `provincias` | `Provincia` | Activa |
| `localities` | `Locality` | Activa - usada por festivales |
| `meses` | `Mes` | Activa - usada por festivales |

#### Pasarela, UGC y moderacion

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `contributions` | `Contribution` | Activa - flujo UGC |
| `organizations` | `Organization` | Activa - Pasarela |
| `organization_members` | `OrganizationMember` | Activa - Pasarela |
| `social_accounts` | `SocialAccount` | Activa - Pasarela |
| `publication_requests` | `PublicationRequest` | Activa - Pasarela |
| `publication_targets` | `PublicationTarget` | Activa - Pasarela |
| `publication_attempts` | `PublicationAttempt` | Activa - Pasarela |
| `publication_templates` | `PublicationTemplate` | Activa - Pasarela |
| `moderation_reviews` | `ModerationReview` | Activa |
| `audit_logs` | `AuditLog` | Activa |
| `newsletter_subscribers` | `NewsletterSubscriber` | Activa |
| `user_notifications` | `UserNotification` | Activa |

#### Torneos folkloricos

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `folklore_tournaments` | `FolkloreTournament` | Activa |
| `folklore_tournament_groups` | `FolkloreTournamentGroup` | Activa |
| `folklore_tournament_participants` | `FolkloreTournamentParticipant` | Activa |
| `folklore_tournament_matches` | `FolkloreTournamentMatch` | Activa |

#### Pivots y relaciones activas

| Tabla | Tipo | Estado |
|-------|------|--------|
| `interprete_noticia` | Pivot legacy | Activa - relacion noticias legacy/artistas y compatibilidad historica |
| `event_interprete` | Pivot | Activa - relacion eventos/artistas |
| `event_festival` | Pivot | Activa - relacion eventos/festivales |
| `festival_interprete` | Pivot | Activa - relacion festivales/artistas |
| `festival_news` | Pivot | Activa - relacion festivales/noticias |
| `album_interprete` | Pivot | Activa |
| `albunes_canciones` | Pivot | Activa |
| `knowledge_article_interprete` | Pivot | Activa - relacion articulos/artistas |
| `knowledge_article_cancion` | Pivot | Activa - relacion articulos/canciones |
| `knowledge_article_album` | Pivot | Activa - relacion articulos/discos |
| `knowledge_article_festival` | Pivot | Activa - relacion articulos/festivales |
| `event_knowledge_article` | Pivot | Activa - relacion articulos/eventos |
| `knowledge_article_provincia` | Pivot | Activa - relacion articulos/provincias |
| `knowledge_article_related` | Pivot | Activa - enlazado interno entre articulos |

#### Seguridad, autenticacion e infraestructura Laravel

| Tabla | Uso | Estado |
|-------|-----|--------|
| `users` | Usuarios, auth, permisos, publisher fields y tokens sociales | Activa |
| `roles` | Spatie Permission | Activa |
| `permissions` | Spatie Permission | Activa |
| `model_has_roles` | Pivot ACL | Activa |
| `model_has_permissions` | Pivot ACL | Activa |
| `role_has_permissions` | Pivot ACL | Activa |
| `personal_access_tokens` | Sanctum | Activa |
| `sessions` | Sesiones Laravel | Activa |
| `jobs` | Cola database | Activa |
| `failed_jobs` | Fallos de cola | Activa |
| `migrations` | Historial de migraciones | Activa |

### Tablas existentes con uso parcial, ambiguo o pendiente de auditoria

| Tabla | Estado |
|-------|--------|
| `radios` | Existe modelo y rutas frontend. No hay backend administrativo. Requiere auditoria para confirmar alcance real. |
| `penias` | Existe modelo y rutas frontend. No hay backend administrativo. Requiere auditoria para confirmar alcance real. |
| `venues` | Existe en BD por la transformacion a `events`, pero no hay `Venue` model en `app/Models`. Estado funcional incompleto / pendiente de auditoria. |

### Indices de performance agregados recientemente

Se agregaron migraciones de indices para mejorar tiempo de respuesta en frontend y listados administrativos:

- `2026_08_09_120000_add_performance_indexes.php`
- `2026_08_09_140000_add_secondary_performance_indexes.php`
- `2026_08_09_150000_add_content_section_indexes.php`

Cobertura principal:

- `news`: `published_at`, combinaciones por `created_by`, `categoria_id`
- `events`: `start_at`, combinaciones por `editorial_status`, `province_id`
- `festivales`: `status`, `published_at`, `province_id`, `mes_id`, `title`, `user_id`
- `interpretes`: `interprete`, `estado`, `slug`
- `albunes`: `estado`, `created_at`, `interprete_id`, `anio`, `slug`
- `canciones`: `estado`, `cancion`, `interprete_id`, `slug`
- `comidas`: `estado`, `titulo`, `visitas`, `slug`
- `mitos`: `estado`, `titulo`, `visitas`, `slug`

### Tablas legacy (existen en BD, no deben usarse para contenido nuevo)

| Tabla | Estado |
|-------|--------|
| `noticias` | Datos previos a la migracion a `news`. No se lee ni escribe. Evaluar limpieza. |
| `shows` | Legacy previa a `events`. Sigue existiendo en BD por compatibilidad historica. No debe usarse para nuevas altas. |
| `images` | Reemplazada por `media_assets`. |

### Tabla canonica de media

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `media_assets` | `MediaAsset` / `Image` | Activa - almacenamiento canonico de imagenes y variantes webp. El modelo `Image` extiende `MediaAsset` por compatibilidad de codigo. |

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

### Backend administrativo funcional

- CRUD y gestion editorial de `Events`, `News` (unificado), `Knowledge Articles`, `Interpretes`, `Albunes`, `Canciones`, `Comidas`, `Festivales`, `Mitos`, `Categorias`, `Tags`, `Clasificados`, `Roles`, `Users` y `Permissions`.
- Centro de moderacion funcional.
- Gestion de newsletter funcional.
- Backend de torneos folkloricos funcional para torneos y partidos.

### Frontend publico funcional

- Home.
- Noticias del folklore argentino.
- Enciclopedia (`/enciclopedia`) con portada, familias y articulos.
- Cartelera de eventos folkloricos.
- Miniportal del artista con biografia, noticias, letras, discografia y shows.
- Cancionero / letras de canciones.
- Discografias.
- Festivales y fiestas tradicionales.
- Mitos y leyendas.
- Recetas de comidas tipicas.
- Clasificados.
- Contacto.
- Buscador.
- Sitemaps.
- Newsletter.
- Social Auth.
- Copa del Folklore Argentino 2026: portada, participantes, fixture, zonas, llaves y reglamento.

### API REST funcional

- Endpoints de lectura/escritura autenticada para:
  - `news`
  - `knowledge-articles`
  - `knowledge-categories`
  - `albums`
  - `songs`
  - `foods`
  - `festivals`
  - `artists`
  - `myths`
  - `events`
- Lectura disponible para cualquier token Sanctum valido.
- Escritura restringida a `role:administrador`.

### Optimizaciones recientes validadas localmente

- **Listados backend**:
  - `News`, `Events`, `Festivales`, `Interpretes` y `Users` dejaron de depender de tablas client-side pesadas para el flujo principal.
  - Se uso paginacion server-side y consultas mas acotadas.
  - Se redujeron eager loads innecesarios y se quitaron miniaturas donde no aportaban al listado.

- **Frontend publico**:
  - Home, Noticias, Festivales, Enciclopedia, Cartelera, Cancionero, Mitos, Recetas, Discos e Interpretes recibieron recortes de queries, cache y render.
  - Se cachearon bloques editoriales y sidebars donde era razonable.
  - Se corrigieron multiples `file_exists()` y otros checks costosos en vistas/components.
  - Se redujo el peso de paginas alfabeticas de `mitos` y `comidas` con paginacion mas corta y tarjetas mas livianas.

- **Performance mobile / Core Web Vitals**:
  - El frontend publico ahora usa bundle propio: `resources/css/app-public.css` y `resources/js/app-public.js`.
  - `Font Awesome` dejo de cargarse globalmente en el frontend publico y quedo restringido al bundle del backend/AdminLTE.
  - Alpine salio del bundle base publico y solo se carga en pantallas que realmente lo necesitan, como clasificados con slider.
  - Google Analytics y AdSense pasaron a carga diferida via `layouts/partials/third-party-scripts.blade.php`.
  - Se priorizo mejor la primera imagen visible de home/noticias.
  - `optimized-image.blade.php` fue reescrito para elegir un `src` inicial mas razonable por variante y usar `decoding="async"`.
  - Se agrego `content-visibility: auto` para sidebar, footer y bloques secundarios de varias homes/listados, buscando reducir costo de render inicial en mobile.

- **Criterio de trabajo**:
  - `AGENTS.md` fue consolidado y ahora deja explicito que toda implementacion futura debe priorizar performance, UX y SEO desde el inicio, no como correccion posterior.

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

- **Pasarela de Contenidos** (`/admin/pasarela`): dashboards, social accounts, publication requests, notifications y templates. Codigo completo, nunca probado end-to-end en produccion.
- **Colaboraciones UGC** (`/admin/contribuir`): flujo unificado para contribuciones. Noticias verificadas end-to-end; el resto del flujo todavia requiere validacion operativa completa en produccion.

### Modulos diferidos - proxima version

- **Entrevistas**: rutas activas en `web.php`, controller sin metodos `byArtista`/`show`, sin vistas, modelo inexistente.
- **Radios**: existen rutas `index` y `show` y controller frontend, pero no estan documentados como modulo cerrado ni cuentan con backend administrativo propio. Requiere auditoria puntual para confirmar alcance real.
- **Penias**: existen rutas `index` y `show` y controller frontend, pero no estan documentadas como modulo cerrado ni cuentan con backend administrativo propio. Requiere auditoria puntual para confirmar alcance real.

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

### 2026-08-09 / 2026-08-10

| Area | Problema | Fix |
|------|----------|-----|
| Backend listados | Listados administrativos pesados, con demasiados registros, imagenes y procesamiento del lado cliente | Paginacion server-side, consultas mas selectivas y simplificacion de vistas para `news`, `events`, `festivales`, `interpretes` y `users` |
| Frontend queries | Varias homes e indices publicos ejecutaban demasiadas queries o bloques repetidos | Se agrego cache por seccion, eager loading mas fino y reduccion de consultas accesorias |
| Vistas/components | Existian checks costosos por item (`file_exists`) y tarjetas con demasiado peso de render | Se eliminaron checks innecesarios, se uso imagen optimizada minimal donde correspondia y se redujo markup inicial en secciones alfabeticas |
| Mobile frontend | El frontend publico arrastraba JS/CSS global del backend y terceros al arranque | Se separo bundle publico (`app-public.css/js`), se quito Font Awesome global, se minimizo JS base y se difirio carga de Analytics/AdSense |
| Imagenes | El componente de imagen iniciaba demasiado grande para ciertos contextos | `optimized-image.blade.php` ahora selecciona un `src` inicial acorde a la variante (`card`, `hero`, `detail`, etc.) y mantiene `srcset` |
| Render mobile | Sidebar, footer y bloques secundarios seguian penalizando el render inicial | Se introdujo `content-visibility: auto` (`cv-auto`) en sidebar global, footer y secciones secundarias de home, noticias, festivales y enciclopedia |
| Gobernanza del repo | `AGENTS.md` estaba incompleto respecto del criterio operativo actual | Se consolido el archivo y se incorporo la politica transversal de performance, UX y SEO por defecto |
| API Enciclopedia / automatizacion editorial | El circuito Google Sheets/Apps Script podia bloquear Evergreen con `422` por categoria si la automatizacion dependia de IDs numericos o mandaba una familia invalida/inactiva | La API ahora resuelve categoria evergreen por `knowledge_category_id`, `knowledge_category_slug` o `knowledge_category_name`, exige que la familia exista y este activa, y devuelve `code: BLOQUEADO_CATEGORIA` cuando el error es deterministico |

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
- `php artisan test tests/Feature/Festivals/FestivalFrontendRestructureTest.php tests/Feature/Seo/PublicTemplateSeoTest.php tests/Feature/Seo/SeoInfrastructureTest.php`

Resultados recientes relevantes:

- Knowledge: `9 passed (18 assertions)`
- SEO + Festivales + Canonical + Sitemaps: `30 passed (52060 assertions)` en la tanda previa y `27 passed (52046 assertions)` en las ultimas tandas de performance/mobile

### Medicion local orientativa de performance

Mejoras observadas en entorno local Docker/Apache antes de despliegue:

- Home: paso de tiempos significativamente mas altos a respuestas cercanas a sub-segundo en escenarios estables de cache local.
- `festivales-y-fiestas-tradicionales`: bajo a menos de 1s en una de las pasadas medidas.
- Paginas por letra de `mitos` y `recetas`: bajaron de ~2.5-3s a menos de 1s tras reducir cards auxiliares y peso visual.

Nota: los `curl` locales no reflejan completamente la mejora de Core Web Vitals mobile. El impacto real debe validarse nuevamente en produccion con PageSpeed Insights luego del deploy, la compilacion de assets y el refresco de caches.

---

## Pendientes identificados

1. Probar Pasarela de Contenidos end-to-end en browser.
2. Volver a medir en PageSpeed Insights mobile y desktop sobre produccion para cuantificar el impacto real de las tres tandas de performance.
3. Extender el criterio del bundle publico liviano y del render diferido a otras plantillas publicas que aun queden pesadas.
4. Evaluar limpieza de tablas legacy (`noticias`, `shows`, `images`) despues de confirmar que no exista informacion unica.
5. Verificar si conviene actualizar la imagen de MariaDB del stack local para evitar el bug de DDL observado en WSL/Docker.
6. Validar en el Apps Script externo que los `422` con `code: BLOQUEADO_CATEGORIA` pasen a estado de correccion y no vuelvan a reintentarse automaticamente.
