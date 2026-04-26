# 🏗️ Documento Técnico — Mi Folklore Argentino

> **Proyecto:** Mi Folklore Argentino (MFA)  
> **Framework:** Laravel 10.x  
> **PHP:** 8.2  
> **Última actualización:** 2026-04-26

---

## 1. Stack Tecnológico

### 1.1 Backend

| Tecnología | Versión | Uso |
|---|---|---|
| **PHP** | 8.2+ | Lenguaje principal |
| **Laravel** | 10.x | Framework PHP |
| **MariaDB** | 10.8 | Motor de base de datos |
| **Apache** | 2.x | Servidor web (con mod_rewrite) |
| **Composer** | Latest | Gestión de dependencias PHP |

### 1.2 Frontend

| Tecnología | Versión | Uso |
|---|---|---|
| **Blade** | — | Motor de templates de Laravel |
| **Tailwind CSS** | 3.x | Framework CSS utilitario |
| **Livewire** | 3.x | Componentes reactivos sin JS explícito |
| **Vite** | — | Build tool para assets (CSS/JS) |
| **AdminLTE** | 3.x | Template del panel de administración |

### 1.3 Infraestructura

| Tecnología | Uso |
|---|---|
| **Docker** | Entorno de desarrollo local |
| **Docker Compose** | Orquestación de servicios locales |
| **Hostinger** | Hosting de producción |
| **Git** | Control de versiones |

---

## 2. Arquitectura de la Aplicación

### 2.1 Patrón Arquitectónico

La aplicación sigue el patrón **MVC (Model-View-Controller)** de Laravel, con las siguientes capas adicionales:

```
┌─────────────────────────────────────────────────────┐
│                    CAPA DE PRESENTACIÓN              │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────┐  │
│  │  Frontend    │  │   Backend    │  │    API      │  │
│  │  (Blade +   │  │  (AdminLTE + │  │  (JSON +    │  │
│  │  Tailwind)  │  │   Blade)     │  │  Sanctum)   │  │
│  └──────┬──────┘  └──────┬───────┘  └──────┬─────┘  │
│         │                │                  │        │
└─────────┼────────────────┼──────────────────┼────────┘
          │                │                  │
┌─────────┼────────────────┼──────────────────┼────────┐
│         ▼                ▼                  ▼        │
│              CAPA DE CONTROLADORES                    │
│  ┌──────────────────────────────────────────────┐    │
│  │  Frontend/  │  Backend/   │  Api/            │    │
│  │  Controllers│  Controllers│  Controllers     │    │
│  └──────────────────────────────────────────────┘    │
│                       │                               │
│                       ▼                               │
│              CAPA DE SERVICIOS                        │
│  ┌────────────────┐  ┌─────────────────┐             │
│  │ ImageUpload    │  │ LinkService     │             │
│  │ Service        │  │                 │             │
│  └────────────────┘  └─────────────────┘             │
│                       │                               │
│                       ▼                               │
│              CAPA DE MODELOS (Eloquent ORM)           │
│  ┌──────────────────────────────────────────────┐    │
│  │  Interprete │ News │ Event │ Cancion │ Album │ ...│   │
│  │  Festival │ Mito │ Comida │ Organization │ ...│   │
│  └──────────────────────────────────────────────┘    │
│                       │                               │
│                       ▼                               │
│              CAPA DE DATOS (MariaDB)                  │
│  ┌──────────────────────────────────────────────┐    │
│  │           Base de datos MySQL/MariaDB         │    │
│  └──────────────────────────────────────────────┘    │
└───────────────────────────────────────────────────────┘
```

### 2.2 Separación por Dominio

Los controladores están organizados en 4 namespaces:

| Namespace | Prefijo de ruta | Middleware | Responsabilidad |
|---|---|---|---|
| `App\Http\Controllers\Frontend` | `/` | Web | Portal público |
| `App\Http\Controllers\Backend` | `/admin` | Web + Auth | Panel de administración |
| `App\Http\Controllers\Pasarela` | `/admin/pasarela` | Web + Auth | Pasarela de contenidos y redes sociales |
| `App\Http\Controllers\Api` | `/api/v1` | Sanctum | API REST |

---

## 3. Modelo de Datos

> La fuente de verdad de columnas y tipos es la BD real. Usar `DESCRIBE <tabla>` o leer las migraciones en `database/migrations/`. Este documento describe estructura lógica y relaciones.

### 3.1 Diagrama Entidad-Relación

```mermaid
erDiagram
    USERS ||--o{ NEWS : "crea"
    USERS ||--o{ INTERPRETES : "crea"
    USERS ||--o{ EVENTS : "crea"
    USERS ||--o{ CANCIONES : "crea"
    USERS ||--o{ ALBUNES : "crea"
    USERS ||--o{ FESTIVALS : "crea"
    USERS ||--o{ MITOS : "crea"
    USERS ||--o{ COMIDAS : "crea"
    USERS ||--o{ CLASSIFIEDS : "publica"
    USERS ||--o{ CONTRIBUTIONS : "colabora"
    USERS ||--o{ NEWSLETTER_SUBSCRIBERS : "suscribe"
    USERS ||--o{ ORGANIZATIONS : "crea"

    ORGANIZATIONS ||--o{ ORGANIZATION_MEMBERS : "tiene"
    ORGANIZATIONS ||--o{ NEWS : "publica"
    ORGANIZATIONS ||--o{ EVENTS : "publica"
    ORGANIZATIONS ||--o{ SOCIAL_ACCOUNTS : "conecta"

    INTERPRETES ||--o{ EVENTS : "participa_en"
    INTERPRETES ||--o{ CANCIONES : "tiene"
    INTERPRETES ||--o{ ALBUNES : "tiene"
    INTERPRETES }o--o{ NEWS : "aparece_en"

    ALBUNES }o--o{ CANCIONES : "contiene"

    NEWS }o--|| CATEGORIAS : "pertenece_a"

    CLASSIFIEDS }o--|| CATEGORIES : "pertenece_a"
    CLASSIFIEDS }o--o{ TAGS : "etiquetado_con"

    EVENTS }o--|| PROVINCIAS : "ubicado_en"
    FESTIVALS }o--|| PROVINCIAS : "ubicado_en"
    FESTIVALS }o--|| MESES : "realizado_en"

    NEWS ||--o{ PUBLICATION_REQUESTS : "genera"
    EVENTS ||--o{ PUBLICATION_REQUESTS : "genera"
    PUBLICATION_REQUESTS ||--o{ PUBLICATION_TARGETS : "tiene"
    PUBLICATION_TARGETS ||--o{ PUBLICATION_ATTEMPTS : "genera"
    PUBLICATION_TARGETS }o--|| SOCIAL_ACCOUNTS : "usa"
```

### 3.2 Grupos de Tablas

#### Contenido editorial (tablas activas)
| Tabla | Modelo | Descripción |
|---|---|---|
| `news` | `News` | Noticias del portal. Campos canónicos: `title`, `body`, `slug`, `editorial_status`. Tiene compatibilidad legacy con `estado`. |
| `events` | `Event` | Eventos y shows. Campos canónicos: `title`, `body`, `start_at`, `editorial_status`. |
| `interpretes` | `Interprete` | Artistas y músicos folklóricos. Nombre artístico en campo `interprete`. |
| `canciones` | `Cancion` | Letras de canciones. Campo principal `cancion` (título). |
| `albunes` | `Album` | Discografía. Campo principal `album` (nombre). |
| `festivales` | `Festival` | Festivales y fiestas tradicionales. |
| `mitos` | `Mito` | Mitos y leyendas. |
| `comidas` | `Comida` | Recetas de comidas típicas. Campo principal `receta`. |

#### Tablas legacy (siguen existiendo, no se leen desde la app)
| Tabla | Estado |
|---|---|
| `noticias` | 347 filas. Datos anteriores a la migración a `news`. No se escribe ni lee desde los controllers. |
| `shows` | 0 filas. Vaciada al migrar a `events`. |

#### Usuarios y comunidad
| Tabla | Modelo | Descripción |
|---|---|---|
| `users` | `User` | Usuarios del sistema. Incluye `google_id`, `facebook_id`, `points`, `rank`. |
| `classifieds` | `Classified` | Avisos clasificados. Estado: `pendiente`, `activo`, `rechazado`. |
| `contributions` | `Contribution` | UGC polimórfico. Payload JSON con datos propuestos. |
| `newsletter_subscribers` | `NewsletterSubscriber` | Suscriptores con token de desuscripción. |

#### Pasarela de contenidos
| Tabla | Modelo | Descripción |
|---|---|---|
| `organizations` | `Organization` | Organizaciones (peñas, productoras, artistas independientes). |
| `organization_members` | `OrganizationMember` | Membresías con roles. |
| `social_accounts` | `SocialAccount` | Cuentas de redes sociales vinculadas (polimórfico: User u Organization). |
| `publication_requests` | `PublicationRequest` | Solicitudes de publicación de un contenido (News o Event). |
| `publication_targets` | `PublicationTarget` | Destino específico: portal, red social, etc. |
| `publication_attempts` | `PublicationAttempt` | Cada intento de publicar un target, con payload y respuesta. |
| `publication_templates` | `PublicationTemplate` | Templates de texto por provider y tipo de contenido. |
| `moderation_reviews` | `ModerationReview` | Historial de revisiones de moderación (polimórfico). |
| `audit_logs` | `AuditLog` | Log de acciones críticas. Método estático `AuditLog::log()`. |
| `notifications` | `UserNotification` | Notificaciones internas para usuarios. |

#### Referencia
| Tabla | Contenido |
|---|---|
| `categorias` | Categorías de noticias (Actualidad, Festivales, etc.) |
| `categories` | Categorías de clasificados |
| `tags` | Etiquetas para clasificados |
| `provincias` | Provincias argentinas |
| `meses` | Meses del año |

### 3.3 Tablas Pivote

| Tabla | Relación |
|---|---|
| `interprete_noticia` | Intérpretes ↔ News (M:N) |
| `event_interprete` | Events ↔ Intérpretes (M:N, con `sort_order`) |
| `albunes_canciones` | Álbumes ↔ Canciones (M:N, con campo `orden`) |
| `classified_tag` | Clasificados ↔ Tags (M:N) |

### 3.4 Tablas de Autorización (Spatie Permission)

| Tabla | Contenido |
|---|---|
| `roles` | Roles del sistema |
| `permissions` | Permisos disponibles |
| `model_has_roles` | Asignación de roles a usuarios |
| `model_has_permissions` | Asignación directa de permisos |
| `role_has_permissions` | Permisos asignados a roles |

### 3.5 Nota sobre compatibilidad legacy

Los modelos `News` y `Event` tienen accessors de compatibilidad que mapean nombres de campos viejos a los nuevos (ej: `$news->titulo` → `title`, `$event->fecha` → `start_at`). Estos accessors funcionan en instancias del modelo pero **no en queries WHERE** — siempre usar el nombre canónico en consultas.

---

## 4. Rutas y Endpoints

### 4.1 Rutas Web (Frontend)

```
GET  /                                          → home
GET  /noticias-del-folklore-argentino            → noticias.index
GET  /noticias-del-folklore-argentino/{slug}      → noticias.show
GET  /cartelera-de-eventos-folkloricos           → cartelera.index
GET  /cartelera-de-eventos-folkloricos/{slug}     → cartelera.show
GET  /biografias-de-artistas-folkloricos         → interpretes.index
GET  /biografias-de-artistas-folkloricos/letra/{l}→ interpretes.letra
GET  /letras-de-canciones-folkloricas            → canciones.index
GET  /letras-de-canciones-folkloricas/letra/{l}   → canciones.letra
GET  /discografias-del-folklore-argentino        → discografias.index
GET  /festivales-y-fiestas-tradicionales         → festivales.index
GET  /festivales-y-fiestas-tradicionales/{slug}   → festivales.show
GET  /radios-de-folklore-argentino               → radios.index
GET  /radios-de-folklore-argentino/{slug}         → radios.show
GET  /penias-folkloricas-de-argentina            → penias.index
GET  /penias-folkloricas-de-argentina/{slug}      → penias.show
GET  /mitos-y-leyendas-argentinas                → mitos.index
GET  /mitos-y-leyendas-argentinas/letra/{slug}    → mitos.letra
GET  /mitos-y-leyendas-argentinas/{slug}          → mitos.show
GET  /recetas-de-comidas-tipicas-argentinas       → comidas.index
GET  /recetas-de-comidas-tipicas-argentinas/letra/{s} → comidas.letra
GET  /recetas-de-comidas-tipicas-argentinas/{slug} → comidas.show
GET  /contacto                                   → contacto
POST /contacto                                   → contacto.store
GET  /buscar                                     → buscar
POST /compartir                                  → compartir.store

# Clasificados
GET  /avisos-clasificados                        → classifieds.index
GET  /avisos-clasificados/publicar               → classifieds.create [auth]
POST /avisos-clasificados/publicar               → classifieds.store [auth]
GET  /avisos-clasificados/mis-avisos             → classifieds.mis-avisos [auth]
POST /avisos-clasificados/renovar/{id}           → classifieds.renovar [auth]
GET  /avisos-clasificados/{slug}                 → classifieds.show

# Colaboraciones [auth] — migradas al backend
GET  /admin/contribuir                           → backend.contributions.index
GET  /admin/contribuir/crear/{type}/{id?}        → backend.contributions.create
POST /admin/contribuir/store                     → backend.contributions.store

# Miniportal del artista
GET  /{artista}                                  → artista.show
GET  /{artista}/biografia                        → artista.biografia
GET  /{artista}/noticias                         → artista.noticias
GET  /{artista}/noticias/{slug}                  → artista.noticia
GET  /{artista}/letras                           → artista.canciones
GET  /{artista}/letras/{slug}                    → artista.cancion
GET  /{artista}/discografia                      → artista.discografia
GET  /{artista}/discografia/{slug}               → artista.disco
GET  /{artista}/shows                            → artista.shows
GET  /{artista}/shows/{slug}                     → artista.show.detalle
GET  /{artista}/entrevistas                      → artista.entrevistas [🔮 diferido]
GET  /{artista}/entrevistas/{slug}               → artista.entrevista  [🔮 diferido]

# Social Auth
GET  /auth/google                                → auth.google
GET  /auth/google/callback                       → callback
GET  /auth/facebook                              → auth.facebook
GET  /auth/facebook/callback                     → callback

# Newsletter
POST /newsletter/subscribe                       → newsletter.subscribe
GET  /newsletter/unsubscribe/{token}             → newsletter.unsubscribe

# Sitemap
GET  /sitemap.xml                                → sitemap
GET  /sitemap-news.xml                           → sitemap news
```

### 4.2 Rutas Admin (Backend)

Todas bajo prefijo `/admin` con middleware `auth`:

```
# Dashboard
GET  /admin                                      → dashboard

# CRUD Resources
Resource: /admin/events          → backend.events.*
Resource: /admin/news            → backend.news.*
Resource: /admin/interpretes     → backend.interpretes.*
Resource: /admin/canciones       → backend.canciones.*
Resource: /admin/discos          → backend.discos.*
Resource: /admin/festivales      → backend.festivales.*
Resource: /admin/mitos           → backend.mitos.*
Resource: /admin/comidas         → backend.comidas.*
Resource: /admin/classifieds     → backend.classifieds.*
Resource: /admin/categories      → backend.categories.*
Resource: /admin/tags            → backend.tags.*
Resource: /admin/roles           → roles.*
Resource: /admin/users           → users.*
Resource: /admin/permissions     → permissions.*

# Acciones especiales
POST /admin/canciones/store-ajax                 → backend.canciones.store-ajax
GET  /admin/canciones/data                       → backend.canciones.get
POST /admin/classifieds/{id}/approve             → backend.classifieds.approve
POST /admin/classifieds/{id}/reject              → backend.classifieds.reject

# Contribuciones y moderación
GET  /admin/contribuir                           → backend.contributions.index
GET  /admin/moderation                           → backend.moderation.index

# Newsletter
GET  /admin/newsletter-subscribers               → backend.newsletter.index
POST /admin/newsletter-subscribers/{id}/toggle   → backend.newsletter.toggle

# Pasarela de contenidos
GET  /admin/pasarela                             → pasarela.index
GET  /admin/pasarela/dashboard                   → pasarela.dashboard
GET  /admin/pasarela/admin/dashboard             → pasarela.admin.dashboard
Resource: /admin/pasarela/social-accounts        → pasarela.social-accounts.*
Resource: /admin/pasarela/publication-requests   → pasarela.publication-requests.*
Resource: /admin/pasarela/templates              → pasarela.templates.*
GET  /admin/pasarela/notifications               → pasarela.notifications.index
POST /admin/pasarela/notifications/{id}/read     → pasarela.notifications.mark-read
POST /admin/pasarela/notifications/read-all      → pasarela.notifications.mark-all-read
GET  /admin/pasarela/notifications/count         → pasarela.notifications.unread-count
```

### 4.3 API REST (v1)

Todas bajo prefijo `/api/v1` con middleware `auth:sanctum`:

```
GET|POST        /api/v1/news                     → News CRUD
GET|PUT|DELETE  /api/v1/news/{id}                → News individual
GET|POST        /api/v1/albums                   → Albums CRUD
GET|PUT|DELETE  /api/v1/albums/{id}              → Album individual
GET|POST        /api/v1/songs                    → Songs CRUD
GET|PUT|DELETE  /api/v1/songs/{id}               → Song individual
GET|POST        /api/v1/foods                    → Foods CRUD
GET|PUT|DELETE  /api/v1/foods/{id}               → Food individual
GET|POST        /api/v1/festivals                → Festivals CRUD
GET|PUT|DELETE  /api/v1/festivals/{id}           → Festival individual
GET|POST        /api/v1/artists                  → Artists CRUD
GET|PUT|DELETE  /api/v1/artists/{id}             → Artist individual
GET|POST        /api/v1/myths                    → Myths CRUD
GET|PUT|DELETE  /api/v1/myths/{id}               → Myth individual
```

---

## 5. Dependencias del Proyecto

### 5.1 Dependencias PHP (Producción)

| Paquete | Versión | Uso |
|---|---|---|
| `laravel/framework` | ^10.10 | Framework principal |
| `laravel/sanctum` | ^3.3 | Autenticación API (tokens) |
| `laravel/socialite` | ^5.15 | OAuth social (Google, Facebook) |
| `laravel/ui` | ^4.5 | Scaffolding de autenticación |
| `laravel/tinker` | ^2.8 | REPL interactivo |
| `livewire/livewire` | ^3.0 | Componentes reactivos |
| `spatie/laravel-permission` | ^6.7 | Gestión de roles y permisos |
| `jeroennoten/laravel-adminlte` | ^3.11 | Template panel admin |
| `yajra/laravel-datatables-oracle` | ^10.11 | DataTables server-side |
| `intervention/image` | ^3.11 | Procesamiento de imágenes |
| `guzzlehttp/guzzle` | ^7.2 | Cliente HTTP |
| `realrashid/sweet-alert` | ^7.1 | Alertas frontend |

### 5.2 Dependencias PHP (Desarrollo)

| Paquete | Uso |
|---|---|
| `barryvdh/laravel-debugbar` | Debug bar para desarrollo |
| `fakerphp/faker` | Generación de datos fake |
| `kitloong/laravel-migrations-generator` | Generador de migraciones desde BD |
| `laravel/pint` | Code style fixer |
| `laravel/sail` | Docker dev environment |
| `phpunit/phpunit` | Testing |
| `spatie/laravel-ignition` | Página de error mejorada |

### 5.3 Dependencias Frontend

Definidas en `package.json`:

| Paquete | Uso |
|---|---|
| `vite` | Build tool |
| `tailwindcss` | Framework CSS |
| `postcss` | Procesador CSS |
| `autoprefixer` | Prefijos CSS automáticos |

---

## 6. Patrones de Diseño Utilizados

### 6.1 CommonMethodsTrait

Trait compartido entre entidades de contenido que proporciona métodos comunes:

| Método | Descripción |
|---|---|
| `getNLast($model, $n)` | Obtiene los últimos N registros activos |
| `getNMostVisited($model, $n)` | Obtiene los N más visitados |
| `getNStartsWith($model, $n, $letter)` | Filtra por letra inicial |
| `search($model, $term, $columns)` | Búsqueda en múltiples columnas |
| `getRelatedContent($interprete, $seccion, $actual)` | Contenido relacionado del artista |

**Modelos que lo usan:** `Interprete`, `News`, `Cancion`, `Album`, `Event`, `Festival`, `Mito`, `Comida`

### 6.2 Relaciones Polimórficas

#### Images (Morphable)
La tabla `images` usa relación polimórfica `morphMany` para asociar imágenes a cualquier modelo:

```php
// En cada modelo
public function images()
{
    return $this->morphMany(Image::class, 'imageable');
}
```

**Modelos con imágenes:** Interprete, News, Event, Album, Festival, Mito, Comida, Classified

#### Contributions (Morphable)
Las contribuciones de usuarios se asocian polimórficamente a cualquier entidad:

```php
public function contributable()
{
    return $this->morphTo();
}
```

### 6.3 Scopes de Eloquent

| Modelo | Scope | Descripción |
|---|---|---|
| `Interprete` | `scopeActive` | Filtra activos, ordena por nombre |
| `NewsletterSubscriber` | `scopeActive` | Filtra suscriptores activos |
| `Classified` | `scopeActivo` | Filtra clasificados activos |
| `Classified` | `scopePendiente` | Filtra clasificados pendientes |

### 6.4 Service Layer

| Servicio | Responsabilidad |
|---|---|
| `ImageUploadService` | Procesamiento y almacenamiento de imágenes subidas |
| `LinkService` | Generación y gestión de enlaces |

### 6.5 URL Generation Pattern

Cada modelo de contenido implementa un método `getUrl()` que genera la URL pública correcta, teniendo en cuenta si el contenido está asociado a un intérprete (miniportal) o es general:

```php
public function getUrl(): string
{
    if ($this->interprete) {
        return route('artista.noticia', [
            'interprete' => $this->interprete->slug,
            'noticia' => $this->slug
        ]);
    }
    return route('noticias.show', ['slug' => $this->slug]);
}
```

---

## 7. Autenticación y Autorización

### 7.1 Autenticación

| Método | Implementación |
|---|---|
| **Session-based** | Laravel UI (`Auth::routes()`) |
| **Google OAuth** | Socialite + `SocialiteController` |
| **Facebook OAuth** | Socialite + `SocialiteController` |
| **API Tokens** | Laravel Sanctum (`HasApiTokens` en User) |

### 7.2 Middleware

| Middleware | Uso |
|---|---|
| `auth` | Rutas de backend, clasificados (CRUD), colaboraciones |
| `auth:sanctum` | API REST |
| `web` | Rutas públicas del frontend |

### 7.3 Autorización (Spatie Permission)

```php
// User model
use HasRoles;

// Verificar en controlador
$user->getRoleNames();
$user->getAllPermissions();
```

---

## 8. Sistema de Emails

### 8.1 Mailables

| Clase | Trigger | Descripción |
|---|---|---|
| `ContactSendEmail` | Formulario de contacto | Respuesta automática al remitente |
| `ContactRecieveEmail` | Formulario de contacto | Notificación al equipo |
| `WeeklyNewsletterMail` | Job semanal | Newsletter con contenido reciente |
| `AlbumCreated` | Creación de álbum | Notificación de nuevo álbum |
| `CancionCreated` | Creación de canción | Notificación de nueva canción |
| `ComidaCreated` | Creación de receta | Notificación de nueva receta |
| `FestivalCreated` | Creación de festival | Notificación de nuevo festival |
| `MitoCreated` | Creación de mito | Notificación de nuevo mito |
| `ShowCreated` | Creación de show | Notificación de nuevo show |

### 8.2 Jobs

| Job | Cola | Frecuencia |
|---|---|---|
| `SendNewsletterJob` | default | Semanal (programado) |

---

## 9. Configuración Docker

### 9.1 docker-compose.yml

3 servicios orquestados:

```yaml
services:
  app:       # PHP 8.2 + Apache (custom build)
    ports: [80:80]
    volumes:
      - ./:/var/www/html
      - /var/www/html/vendor  # vendor queda en el contenedor

  db:        # MariaDB 10.8
    ports: [3306:3306]
    volumes:
      - ./database_local:/var/lib/mysql

  phpmyadmin: # phpMyAdmin 5.1.1
    ports: [8080:80]
```

### 9.2 Dockerfile

Imagen custom basada en `php:8.2-apache`:
- Extensiones PHP: `zip`, `pdo_mysql`, `gd` (con freetype, jpeg, webp), `opcache`.
- Apache con `mod_rewrite` habilitado.
- Document root: `/var/www/html/public`.
- Node.js 20.x para build de assets.
- Composer latest.
- `php.ini` customizado.

### 9.3 Notas Importantes

- El volumen `vendor` está excluido del bind mount (`/var/www/html/vendor`) para que las dependencias vivan dentro del contenedor y no se sobreescriban desde el host Windows.
- La carpeta `database_local/` almacena los datos de MariaDB persistentes entre reinicios de Docker.

---

## 10. Configuración de Assets

### 10.1 Build Tools

| Herramienta | Archivo de config |
|---|---|
| **Vite** | `vite.config.js` |
| **Tailwind CSS** | `tailwind.config.js` |
| **PostCSS** | `postcss.config.js` |

> **Nota:** También existe `webpack.mix.js` (legacy), pero el proyecto migró a Vite.

### 10.2 Comandos

```bash
# Desarrollo (con hot reload)
npm run dev

# Producción (minificado)
npm run build
```

---

## 11. SEO Técnico

### 11.1 Estrategia de URLs

Las URLs del frontend están diseñadas con slugs largos orientados al posicionamiento SEO, ya posicionados en motores de búsqueda:

```
/noticias-del-folklore-argentino
/biografias-de-artistas-folkloricos
/letras-de-canciones-folkloricas
/discografias-del-folklore-argentino
/cartelera-de-eventos-folkloricos
/festivales-y-fiestas-tradicionales
/radios-de-folklore-argentino
/penias-folkloricas-de-argentina
/mitos-y-leyendas-argentinas
/recetas-de-comidas-tipicas-argentinas
/avisos-clasificados
```

> ⚠️ **No modificar estas URLs** — están posicionadas en Google y cambiarlas afectaría el SEO negativamente.

### 11.2 Datos Estructurados

- **JSON-LD** en el home con schemas `WebSite`, `Organization`, `SearchAction`.
- Configurción de `sameAs` con redes sociales oficiales.

### 11.3 Sitemaps

| Archivo | Generador | Contenido |
|---|---|---|
| `/sitemap.xml` | `SitemapController@index` | Todas las URLs del sitio |
| `/sitemap-news.xml` | `SitemapController@newsIndex` | Noticias recientes (Google News) |

---

## 12. Testing

### 12.1 Framework de Testing

| Herramienta | Versión |
|---|---|
| **PHPUnit** | ^10.1 |
| **Mockery** | ^1.4.4 |

### 12.2 Estado Actual

Tests corriendo sobre la BD `mfa` con `DatabaseTransactions`. Ubicados en `tests/Feature/`. **No usar `RefreshDatabase`** — crashea MariaDB en Docker/Windows.

### 12.3 Ejecución

```bash
php artisan test
# o
./vendor/bin/phpunit
```

---

## 13. Archivos de Configuración Externos

| Archivo | Propósito |
|---|---|
| `.env` | Variables de entorno (no se commitea) |
| `.env.example` | Template de variables de entorno |
| `.htaccess` | Reglas de Apache (root y public) |
| `apache.conf` | Configuración de VirtualHost para Docker |
| `php.ini` | Configuración personalizada de PHP |
| `Dockerfile` | Definición de imagen Docker |
| `docker-compose.yml` | Orquestación de servicios Docker |
| `composer.json` | Dependencias PHP |
| `package.json` | Dependencias Node.js |
| `vite.config.js` | Configuración de Vite |
| `tailwind.config.js` | Configuración de Tailwind CSS |
| `postcss.config.js` | Configuración de PostCSS |
| `phpunit.xml` | Configuración de PHPUnit |
| `.editorconfig` | Estilo de código del editor |
| `.styleci.yml` | Estilo de código CI |
| `postman_collection.json` | Colección de Postman para la API |

---

## 14. Scripts y Herramientas Auxiliares

| Archivo | Propósito |
|---|---|
| `generate_postman.php` | Genera la colección de Postman para la API |
| `generate_token.php` | Genera tokens de autenticación para la API |
| `postman_collection.json` | Colección exportada de Postman |

---

## 15. Consideraciones Técnicas y Deuda Técnica

### 15.1 Observaciones

| Área | Observación | Prioridad |
|---|---|---|
| **Tablas legacy** | `noticias` (347 filas) y `shows` (0 filas) siguen en la BD sin usarse. Evaluar limpieza. | 🟡 Media |
| **Modelos legacy** | `Show.php` y `Noticia.php` son aliases deprecados. Eliminar cuando BusquedaController y SitemapController no los referencien (ya resuelto). | 🟢 Baja |
| **Pasarela sin probar** | La Pasarela de Contenidos está implementada pero nunca fue probada en producción. | 🟠 Alta |
| **Colaboraciones UGC** | Flujo migrado de `/colaborar` a `/admin/contribuir` pero no verificado end-to-end. | 🟡 Media |
| **Entrevistas / Radios / Peñas** | Módulos diferidos a próxima versión. Rutas activas pero sin implementación completa. | 🔮 Futuro |
| **Nomenclatura mixta** | Modelos legacy en español (`Interprete`, `Cancion`, `Album`) conviven con nuevos en inglés (`News`, `Event`). Normalizar en próxima versión. | 🟢 Baja |
| **N+1 Queries** | Verificar eager loading en consultas de listados del frontend. | 🟡 Media |
| **Rate Limiting** | No configurado para rutas sensibles (login, API, newsletter). | 🟠 Alta |
| **Newsletter** | En fase de prueba, no validado en producción. | 🟡 Media |
| **`estado`/`publicar` legacy** | Campos duplicados en tablas viejas. `news` y `events` usan `editorial_status` como campo canónico. | 🟢 Baja |
