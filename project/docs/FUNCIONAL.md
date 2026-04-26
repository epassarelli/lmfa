# 📄 Documento Funcional — Mi Folklore Argentino

> **Proyecto:** Mi Folklore Argentino (MFA)  
> **URL:** [https://mifolkloreargentino.com.ar](https://mifolkloreargentino.com.ar)  
> **Tipo:** Portal web de contenido cultural  
> **Propietario:** Proyecto propio  
> **Última actualización:** 2026-04-26

---

## 1. Descripción General

**Mi Folklore Argentino** es un portal web dedicado a la difusión y preservación de la cultura folklórica argentina. Funciona como un hub centralizado de información sobre artistas, música, noticias, eventos, festivales, recetas típicas, mitos y leyendas, peñas, radios y más.

El sitio combina un **portal público** orientado al SEO y la consulta de contenido, un **panel de administración** (backend) para la gestión editorial, una **API REST** para integración con sistemas externos y un sistema de **contenido generado por usuarios** (UGC) que permite colaboraciones de la comunidad.

---

## 2. Público Objetivo

| Segmento | Descripción |
|---|---|
| **Amantes del folklore** | Personas interesadas en la música, danzas y tradiciones argentinas |
| **Músicos y artistas** | Intérpretes y compositores del género folklórico |
| **Productores y organizadores** | Responsables de festivales, peñas y eventos folklóricos |
| **Periodistas y comunicadores** | Profesionales que cubren la escena folklórica |
| **Público general** | Personas con curiosidad por la cultura argentina |
| **Turistas** | Visitantes interesados en festivales, gastronomía típica y tradiciones regionales |

---

## 3. Módulos Funcionales

### 3.1 Portal Público (Frontend)

#### 3.1.1 Home
- Página principal con carrusel/grilla de las **últimas 50 noticias**.
- Secciones organizadas por categorías de noticias.
- Sidebar con formulario de newsletter, enlaces a redes sociales y botón de donación.
- Datos estructurados (JSON-LD) para SEO con schema de `WebSite` y `Organization`.
- Meta title y meta description personalizados.

#### 3.1.2 Noticias
- **Listado general** (`/noticias-del-folklore-argentino`): Muestra todas las noticias activas.
- **Detalle de noticia** (`/noticias-del-folklore-argentino/{slug}`): Vista individual de noticia general.
- **Noticias por artista** (`/{artista}/noticias`): Noticias vinculadas a un intérprete específico.
- **Detalle de noticia de artista** (`/{artista}/noticias/{slug}`): Vista individual dentro del miniportal del artista.
- Relación muchos a muchos con intérpretes.
- Categorización por categoría (Actualidad, Festivales, Lanzamientos, Entrevistas, Cartelera).
- Contador de visitas.
- Soporte para múltiples imágenes (relación polimórfica).

#### 3.1.3 Miniportal del Artista (Intérpretes)
Cada artista tiene su propio "miniportal" accesible mediante slug directo (`/{slug-del-artista}`), con subsecciones internas:

| Subsección | Ruta | Descripción |
|---|---|---|
| Perfil | `/{artista}` | Página principal del artista |
| Biografía | `/{artista}/biografia` | Biografía detallada |
| Noticias | `/{artista}/noticias` | Noticias relacionadas al artista |
| Letras | `/{artista}/letras` | Canciones y letras del artista |
| Discografía | `/{artista}/discografia` | Álbumes y discos |
| Shows | `/{artista}/shows` | Próximos eventos y shows |
| Entrevistas | `/{artista}/entrevistas` | 🔮 Diferido — próxima versión. Rutas activas pero controller sin métodos ni vistas. |

- **Índice general de artistas** (`/biografias-de-artistas-folkloricos`) con navegación alfabética por letra.
- Datos del artista: nombre, biografía, foto, teléfono, correo, Instagram, Twitter, YouTube.
- Estado activo/inactivo para control de visibilidad.

#### 3.1.4 Canciones / Letras
- **Índice general** (`/letras-de-canciones-folkloricas`) con navegación alfabética.
- **Detalle por artista** (`/{artista}/letras/{cancion}`).
- Campos: título, letra completa, enlaces a YouTube y Spotify.
- Relación con álbumes (muchos a muchos con tabla pivote `albunes_canciones`).
- Contador de visitas.

#### 3.1.5 Discografías / Álbumes
- **Índice general** (`/discografias-del-folklore-argentino`).
- **Detalle por artista** (`/{artista}/discografia/{slug}`).
- Campos: nombre del álbum, año, foto de portada, enlace a Spotify.
- Relación con canciones (muchos a muchos con orden en pivote).

#### 3.1.6 Cartelera de Shows / Eventos
- **Índice general** (`/cartelera-de-eventos-folkloricos`): Muestra eventos futuros ordenados por fecha.
- **Detalle** (`/cartelera-de-eventos-folkloricos/{slug}`).
- **Shows por artista** (`/{artista}/shows/{slug}`).
- Campos: nombre, detalle, fecha, hora, lugar, dirección, precio de entrada, link de entradas, coordenadas GPS (lat/lng), provincia, imagen destacada.
- Soporte para flag "destacado".

#### 3.1.7 Festivales
- **Índice** (`/festivales-y-fiestas-tradicionales`).
- **Detalle** (`/festivales-y-fiestas-tradicionales/{slug}`).
- Campos: título, detalle, foto, provincia, mes de realización.
- Filtrado por provincia y mes.

#### 3.1.8 Radios
- 🔮 **Módulo diferido — próxima versión.** Las rutas de índice existen pero el detalle (`/radios-de-folklore-argentino/{slug}`) está sin implementar. Sin gestión backend.

#### 3.1.9 Peñas Folklóricas
- 🔮 **Módulo diferido — próxima versión.** Las rutas de índice existen pero el detalle (`/penias-folkloricas-de-argentina/{slug}`) está sin implementar. Sin gestión backend.

#### 3.1.10 Mitos y Leyendas
- **Índice** (`/mitos-y-leyendas-argentinas`) con navegación alfabética.
- **Por letra** (`/mitos-y-leyendas-argentinas/letra/{letra}`).
- **Detalle** (`/mitos-y-leyendas-argentinas/{slug}`).
- Campos: título, contenido del mito, foto.

#### 3.1.11 Recetas de Comidas Típicas
- **Índice** (`/recetas-de-comidas-tipicas-argentinas`) con navegación alfabética.
- **Por letra** (`/recetas-de-comidas-tipicas-argentinas/letra/{letra}`).
- **Detalle** (`/recetas-de-comidas-tipicas-argentinas/{slug}`).
- Campos: título, receta (contenido), imágenes.

#### 3.1.12 Clasificados
- **Índice** (`/avisos-clasificados`): Listado público de clasificados activos y no expirados. Filtros por categoría, búsqueda de texto y provincia.
- **Detalle** (`/avisos-clasificados/{slug}`): Vista individual con avisos relacionados por categoría.
- **Publicar** (`/avisos-clasificados/publicar`): Formulario de alta (requiere auth). El aviso se crea en estado `pendiente`.
- **Mis avisos** (`/avisos-clasificados/mis-avisos`): Panel del usuario con sus propios avisos paginados.
- **Renovar** (`POST /avisos-clasificados/renovar/{classified}`): Reactiva un aviso propio vuelviéndolo a estado `pendiente` por 30 días nuevos desde la aprobación.

**Campos:** `title`, `description`, `price` (libre), `location`, `contact_info`, `contact_whatsapp`, `expiration_date`, `is_featured`, `estado`, `is_active`, `moderator_comment`. Relación con categoría (1:1), tags (N:N) e imágenes (polimórfico).

**Ciclo de vida:**
```
[Usuario] crea → pendiente → Admin aprueba → activo (30 días desde aprobación)
                           → Admin rechaza → rechazado (con motivo)
[activo] vence → expirado (implícito: expiration_date < hoy)
[Usuario] renueva → pendiente → Admin aprueba → activo (30 días nuevos)
[Admin] crea directamente → activo (sin moderación)
```

**Slug único:** se genera como `{slug-del-titulo}-{5 chars random}` para evitar colisiones.

#### 3.1.13 Colaboraciones (UGC)
- **Índice** (`/admin/contribuir`): Panel personal del colaborador — muestra sólo sus propias contribuciones (requiere auth).
- **Crear** (`/admin/contribuir/crear/{type}/{id?}`): Formulario para proponer contenido nuevo o sugerir edición a uno existente. Tipos habilitados: `interprete`, `noticia`, `cancion`, `festival`, `show`.
- Sistema polimórfico: las contribuciones se almacenan con `contributable_type / contributable_id` y un `payload` JSON con los datos propuestos.
- Flujo de moderación: `pending` → `approved` / `rejected`. El moderador puede agregar un comentario al rechazar.
- Al aprobarse: si es contenido nuevo se crea el modelo con `estado=1` y se genera slug automático; si es edición se aplica el payload sobre el registro existente.
- Sistema de puntos: +50 pts por contenido nuevo aprobado, +20 pts por edición aprobada. Rangos: > 500 pts → "Folclorista de Plata", > 1000 pts → "Folclorista de Oro".
- Ver sección 3.2.3 para las rutas de moderación (exclusivas de `administrador`).

#### 3.1.14 Contacto
- **Formulario** (`/contacto`): Formulario público de contacto.
- Envío de emails al equipo y respuesta automática al remitente.

#### 3.1.15 Buscador
- **Buscador global** (`/buscar`): Búsqueda transversal en múltiples entidades del sistema.

#### 3.1.16 Compartir en Redes
- Funcionalidad para compartir contenido en redes sociales.
- Registro de logs de compartidos (`ShareLog`).

#### 3.1.17 Newsletter
- **Suscripción** vía formulario en el sidebar (componente presente en todas las páginas).
- **Desuscripción** mediante enlace con token único (`/newsletter/unsubscribe/{token}`).
- Envío semanal automático con contenido reciente (en fase de prueba).
- Fuente de suscripción rastreable.

#### 3.1.18 Sitemap
- **Sitemap general** (`/sitemap.xml`): Incluye todas las URLs del sitio.
- **Sitemap de noticias** (`/sitemap-news.xml`): Específico para Google News.

---

### 3.2 Panel de Administración (Backend)

Accesible en `/admin`, protegido por autenticación. Basado en **AdminLTE**.

#### 3.2.1 Dashboard
- Vista resumen con contadores de todas las entidades principales:
  - Noticias, canciones, usuarios, intérpretes, shows, discos, comidas, festivales.
  - Contribuciones pendientes de moderación.
- Información del usuario logueado: roles y permisos.

#### 3.2.2 Gestión de Contenido (CRUD completo)

| Módulo | Descripción |
|---|---|
| **Intérpretes** | Alta, edición y eliminación de artistas |
| **Noticias** | Gestión de noticias con categorización y asociación a artistas |
| **Canciones** | Gestión de letras con creación AJAX y listados con DataTables |
| **Álbumes/Discos** | Gestión de discografía con relación a artista |
| **Shows** | Gestión de eventos con geolocalización |
| **Festivales** | Gestión de festivales por provincia y mes |
| **Mitos** | Gestión de mitos y leyendas |
| **Comidas** | Gestión de recetas de comidas típicas |
| **Clasificados** | Gestión y moderación de avisos clasificados (aprobar/rechazar) |
| **Categorías** | Gestión de categorías para clasificados |
| **Tags** | Gestión de etiquetas |

#### 3.2.3 Moderación

El sistema tiene **dos flujos de moderación distintos** que no se solapan:

**Moderación editorial** (`/admin/moderation` — `ModerationController`): revisa contenido cargado directamente por staff que quedó en borrador o sin publicar. Aplica a:
- News con `editorial_status='draft'` o `published_at=null`
- Events con `editorial_status='draft'`
- Intérpretes, álbumes y canciones con `estado=0`

La acción de publicar se hace desde el formulario de edición de cada entidad (no desde este panel). El panel solo muestra el listado y un enlace a "Revisar".

**Moderación UGC** (`/admin/contributions` — `ContributionController`, solo `administrador`): revisa propuestas de usuarios externos. Muestra comparativa original vs. propuesto. Acciones: aprobar (aplica el payload y otorga puntos) o rechazar (con comentario opcional).

**Moderación de clasificados** (`/admin/classifieds` — `ClassifiedController`): revisa avisos enviados por usuarios. Acciones: aprobar (activa por 30 días) o rechazar (con motivo). El admin también puede crear clasificados directamente como `activo`.

#### 3.2.4 Gestión de Usuarios y Permisos
- **Usuarios**: CRUD completo de usuarios del sistema.
- **Roles**: Gestión de roles (basado en Spatie Permission).
- **Permisos**: Gestión granular de permisos.

#### 3.2.5 Newsletter (Backend)
- Listado de suscriptores del newsletter.
- Toggle de estado activo/inactivo por suscriptor.

---

### 3.3 API REST

API versionada (`/api/v1/`) protegida con **Laravel Sanctum** (token Bearer). Rate limit global: 60 req/min por IP o user_id.

```
Authorization: Bearer {sanctum_token}
```

Las respuestas de `index` son paginadas (15/página, formato estándar Laravel). Las de `show/store/update` devuelven el modelo directo sin Resource class. `destroy` responde `204 No Content`.

#### Endpoints

| Recurso | URL base | Notas |
|---------|----------|-------|
| Noticias | `/api/v1/news` | Filtros: `?categoria_id=N`, `?estado=N`. Store/update pasan por `NewsService` y soportan `multipart/form-data` para imagen. |
| Álbumes | `/api/v1/albums` | CRUD estándar |
| Canciones | `/api/v1/songs` | CRUD estándar |
| Comidas | `/api/v1/foods` | CRUD estándar |
| Festivales | `/api/v1/festivals` | CRUD estándar |
| Artistas | `/api/v1/artists` | Auto-genera `slug` desde campo `interprete` en store y update |
| Mitos | `/api/v1/myths` | CRUD estándar |

#### Gaps y limitaciones actuales

- **Sin control de roles en escritura**: cualquier token Sanctum válido puede crear, editar o eliminar cualquier recurso. No hay verificación de rol en store/update/destroy.
- **Sin recurso `events`**: los Eventos/Shows no tienen endpoints API.
- **Sin recurso `classifieds`**: los clasificados tampoco están expuestos.
- **Sin versioning real**: el prefix `v1` existe pero no hay mecanismo de deprecación ni routing alternativo para una `v2`.

> **Estado actual:** La API está disponible pero no tiene consumidores activos. Preparada para integración con aplicaciones móviles u otros servicios.

---

### 3.4 Pasarela de Contenidos

Sistema de distribución multicanal que permite a publicadores solicitar la publicación de un **Evento** o **Noticia** en uno o más destinos: el portal nativo, sus propias redes sociales, o las cuentas institucionales del portal.

Accesible en `/admin/pasarela/`. Requiere autenticación.

#### 3.4.1 Actores

| Actor | Descripción |
|-------|-------------|
| **Publicador** | Usuario con contenido (`Event` o `News`) asociado a una organización de la que es miembro activo, o que creó directamente. |
| **Administrador** | Ve el dashboard global: pendientes de moderación, publicaciones del día, fallos por canal, tokens vencidos. |
| **Sistema** | Job de cola que ejecuta cada canal asíncronamente. Scheduler diario para recordatorios de eventos. |

#### 3.4.2 Entidades

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `PublicationRequest` | `publication_requests` | Intención de publicar un contenido. Campos clave: `content_type`, `content_id`, `mode`, `wants_portal_publish`, `wants_own_social`, `wants_portal_social`, `scheduled_at`, `status`. |
| `PublicationTarget` | `publication_targets` | Un canal destino específico dentro de un request. Estados: `pending`, `processing`, `published`, `failed`. |
| `PublicationAttempt` | `publication_attempts` | Registro de cada intento de ejecución sobre un target. Guarda payload de request y response, URL externa generada, error si falla. |
| `SocialAccount` | `social_accounts` | Credenciales de redes sociales. Polimórfico (`owner_type / owner_id`). MVP: token ingresado manualmente. |
| `PublicationTemplate` | `publication_templates` | Texto del post con tokens reemplazables. Un template se asocia a `provider` + `content_type` + `variant_name`. |
| `UserNotification` | `user_notifications` | Notificaciones in-app por usuario, con flag `is_read`. |

#### 3.4.3 Modos de publicación

| Modo | Portal nativo | Redes propias | Redes del portal |
|------|:---:|:---:|:---:|
| `portal_only` | ✓ | — | — |
| `social_only` | — | ✓ | — |
| `full` | ✓ | ✓ | ✓ |

Cada canal habilitado genera un `PublicationTarget` independiente.

#### 3.4.4 Canales (Connectors)

| Provider | Connector | Descripción |
|----------|-----------|-------------|
| `native_portal` | `NativePortalConnector` | No hace HTTP. Marca el contenido como `published`, asigna `published_at` y devuelve la URL pública. |
| `facebook` | `FacebookConnector` | Publica en la página de Facebook del publicador usando el token de la `SocialAccount`. |
| `instagram` | `InstagramConnector` | Publica en la cuenta profesional de Instagram. |
| `telegram` | `TelegramConnector` | Publica en el canal o grupo de Telegram. |

Todos los connectors extienden `BaseConnector`, que provee `isHealthy()` (verifica estado y expiración del token), `resolveContent()` y `recordAttempt()`.

El job `PublishToProviderJob` tiene `tries=3` y `backoff=60s`. Si el conector reporta fallo con `is_retryable=false`, el target queda en `failed` sin reintentos adicionales.

#### 3.4.5 Templates

El `TemplateService` resuelve el texto del post en cascada:

1. `content_type` + `provider` + `variant_name` (match exacto)
2. `content_type` + `provider` (sin variant)
3. `provider` + `content_type=null` (default del proveedor)
4. Fallback hardcoded: `"{title}\n\n{excerpt}"`

**Tokens disponibles en el texto del template:** `{title}`, `{subtitle}`, `{excerpt}`, `{url}`, `{date}`, `{city}`, `{venue}`

Variantes estándar: `default`, `facebook_default`, `instagram_default`, `telegram_default`, `institutional`.

#### 3.4.6 Recordatorio automático de eventos

El job `EventReminderJob` corre diariamente vía Laravel Scheduler. Detecta eventos con `editorial_status='approved'` y `start_at` dentro de las próximas 48 horas que aún no tienen `published_at`. Por cada uno: crea un `PublicationRequest` tipo `portal_only` (si no existe previamente) y genera una `UserNotification` para el creador.

#### 3.4.7 Rutas

| URL | Nombre | Descripción |
|-----|--------|-------------|
| `GET /admin/pasarela/` | `pasarela.index` | Dashboard overview |
| `GET /admin/pasarela/dashboard` | `pasarela.dashboard` | Dashboard del publicador (mis stats) |
| `GET /admin/pasarela/admin/dashboard` | `pasarela.admin.dashboard` | Dashboard global (admin) |
| `GET /admin/pasarela/publication-requests` | `pasarela.publication-requests.index` | Mis solicitudes paginadas |
| `GET /admin/pasarela/publication-requests/create?content_type=&content_id=` | `pasarela.publication-requests.create` | Formulario de solicitud |
| `POST /admin/pasarela/publication-requests` | `pasarela.publication-requests.store` | Registrar solicitud |
| `GET /admin/pasarela/publication-requests/{id}` | `pasarela.publication-requests.show` | Detalle y estado de targets |
| `GET/POST /admin/pasarela/social-accounts` | `pasarela.social-accounts.*` | Gestión de cuentas sociales |
| `GET/POST/PUT/DELETE /admin/pasarela/templates` | `pasarela.templates.*` | ABM de templates |
| `POST /admin/pasarela/templates/preview` | `pasarela.templates.preview` | Preview del template (JSON) |
| `GET /admin/pasarela/notifications` | `pasarela.notifications.index` | Centro de notificaciones |
| `POST /admin/pasarela/notifications/{id}/read` | `pasarela.notifications.mark-read` | Marcar leída |
| `POST /admin/pasarela/notifications/read-all` | `pasarela.notifications.mark-all-read` | Marcar todas leídas |
| `GET /admin/pasarela/notifications/count` | `pasarela.notifications.unread-count` | Conteo sin leer (JSON) |

#### 3.4.8 Gaps y limitaciones actuales

- **Token no cifrado:** `SocialAccount.token_encrypted` almacena el token en texto plano. El campo lleva el sufijo `_encrypted` por intención futura pero no usa el cast `encrypted` de Laravel.
- **Fallback a user_id=1 en scheduler:** `PublicationService` usa `Auth::id() ?? 1`. Las solicitudes creadas por el job quedan asignadas al usuario con `id=1`.
- **Portal social sin filtro de organización:** cuando `wants_portal_social=true`, la query busca cuentas de cualquier `Organization` activa — no sólo la del portal. Requiere definir cuál es la "organización institucional".
- **Sin reintento manual desde UI:** no hay ruta para reintentar un target fallido. Solo reintentos automáticos del job (máx. 3).

---

### 3.5 Autenticación

| Método | Descripción |
|---|---|
| **Email/Password** | Registro y login tradicional (Laravel UI) |
| **Google OAuth** | Login con cuenta de Google (Socialite) |
| **Facebook OAuth** | Login con cuenta de Facebook (Socialite) |
| **API Token** | Autenticación por token para la API (Sanctum) |

---

### 3.6 Sistema de Imágenes

Infraestructura transversal usada por el backend, la API y los clasificados. Implementada en `ImageUploadService` + `config/image_profiles.php`. Genera variantes WebP en múltiples tamaños y las registra en la tabla `media_assets` (modelo `Image` / `MediaAsset`) mediante relación polimórfica.

#### Cómo funciona

```
UploadedFile → ImageUploadService::process($file, $model, $profile, $folder)
    ├── Guarda el original en storage/public/{folder}/{Y/m}/{filename}_original.{ext}
    └── Por cada variante del perfil:
            ├── Redimensiona con ratio (cover) o solo ancho (scale)
            └── Convierte a WebP (calidad 85)
                → Guarda en storage/public/{folder}/{Y/m}/{filename}_{variant}_{size}.webp
→ Crea registro MediaAsset con variants_json (mapa variante → tamaño → URL)
```

No escala hacia arriba: si el original es más pequeño que un tamaño definido, esa variante se omite.

#### Perfiles disponibles

| Perfil | Usado en | Variantes |
|--------|----------|-----------|
| `news_full` | Noticias, clasificados | `card` 16:9 (320/480/768px), `detail` 16:9 (768/1200/1600px), `sidebar` 1:1 (120/240/320px) |
| `artist` | Intérpretes | `card` 1:1 (80/160px), `main` 3:4 (300/450/768px) |
| `album` | Discos | `card` 1:1 (80/160px), `main` 1:1 (300/600/800px) |
| `recipe` | Comidas | `card` 4:3 (80/160px), `main` 4:3 (400/800px) |
| `hero` | Hero banners | `main` 16:9 (768/1280/1600/1920px) |

#### Acceso a las variantes desde una vista

```php
$model->images->first()->variants_json['card'][480]  // URL del webp 480px de la variante card
```

El componente Blade `optimized-image` encapsula esto con lazy loading y srcset automático.

#### Gaps

- El perfil `news_full` se usa también para clasificados aunque no genera variante `detail` útil para esa entidad — fue reutilizado por conveniencia.
- No existe perfil para `festival`, `mito` ni `show` — esos módulos no tienen upload de imagen vía `ImageUploadService`.

---

## 4. Roles y Permisos

El sistema utiliza **Spatie Laravel Permission**. Hay 3 roles definidos en el seeder (más el estado "sin rol" para usuarios registrados comunes):

| Rol | Quién es | Acceso clave |
|-----|----------|--------------|
| `administrador` | Equipo del portal | Todo: CRUD completo, todas las moderaciones, gestión de usuarios y roles, dashboard admin Pasarela |
| `prensa` | Periodistas / colaboradores de confianza | create+read+update en casi todo el contenido (sin delete, sin gestión de usuarios); Pasarela propia; sin `manage templates` |
| `colaborador` | Aportantes externos | Solo create+read en todas las entidades (sin update ni delete); sin Pasarela |
| *(sin rol)* | Usuario registrado común | Solo frontend: publicar clasificados, enviar contribuciones UGC, ver sus avisos |
| *(anónimo)* | Visitante | Solo lectura pública, newsletter, contacto |

#### Permisos granulares (Spatie)

Por cada entidad (`user`, `interprete`, `noticia`, `show`, `cancion`, `album`, `festival`, `mito`, `comida`) existen 5 permisos:

| Permiso | Descripción |
|---------|-------------|
| `access {entidad}` | Acceso al módulo en el backend |
| `create {entidad}` | Crear registros |
| `read {entidad}` | Ver registros |
| `update {entidad}` | Editar registros |
| `delete {entidad}` | Eliminar registros |

Permisos específicos de la Pasarela:

| Permiso | Quién lo tiene |
|---------|----------------|
| `publish contents` | `administrador`, `prensa` |
| `manage social accounts` | `administrador`, `prensa` |
| `manage templates` | solo `administrador` |

> **Nota:** La verificación de permisos en el backend actualmente usa `role:administrador` en los middlewares, no los permisos granulares individuales. Los permisos granulares están definidos en el seeder pero no están siendo chequeados en los controllers. El sistema está preparado para un control más fino cuando se requiera.

---

## 5. Flujos Principales

### 5.1 Flujo de Publicación de Contenido
```
Administrador → Backend (CRUD) → Contenido con estado=1 → Visible en Frontend
```

### 5.2 Flujo de Colaboración UGC
```
Usuario registrado → /colaborar → Selecciona tipo y completa formulario
→ Contribución creada (status: pending)
→ Administrador revisa en backend → Aprueba / Rechaza con comentario
```

### 5.3 Flujo de Clasificados
```
[Usuario] → /avisos-clasificados/publicar → Completa formulario
→ Clasificado creado (estado: pendiente, is_active: false, expiration_date: +30 días)
→ Admin revisa en /admin/classifieds
    ├── Aprueba → estado: activo, is_active: true, expiration_date: ahora+30 días
    └── Rechaza → estado: rechazado (con motivo opcional)

[activo] → pasa expiration_date → se oculta del índice público (sin cambio de estado)
→ Usuario entra a /avisos-clasificados/mis-avisos → Renueva
→ estado: pendiente nuevamente → vuelve al flujo de aprobación

[Admin] → /admin/classifieds/create → crea directo como activo (sin moderación)
```

### 5.4 Flujo de Newsletter
```
Visitante → Formulario sidebar → Suscripción (genera token único)
→ Job semanal recopila contenido → Envía email a suscriptores activos
→ Enlace de desuscripción con token en cada email
```

### 5.5 Flujo de Publicación Multicanal (Pasarela)
```
Publicador → /admin/pasarela/publication-requests/create?content_type=&content_id=
→ Selecciona modo y canales → POST store
→ PublicationService.createRequest()
    ├── Crea PublicationRequest (status: pending)
    └── generateTargets() → crea 1 PublicationTarget por canal
         └── dispatchJobs() → encola PublishToProviderJob por cada target

→ [Queue worker] PublishToProviderJob.handle()
    ├── ConnectorFactory::make($provider)
    ├── isHealthy() → si falla: target=failed, fin
    ├── publish() → BaseConnector.recordAttempt() → guarda PublicationAttempt
    └── target.status = 'published' | 'failed'
         └── si failed y is_retryable → reintento automático (máx. 3, delay 60s)
```

### 5.6 Flujo de Recordatorio Automático de Eventos
```
Scheduler diario → EventReminderJob
→ Busca eventos: editorial_status=approved, start_at ∈ [now, now+48h], published_at=null
→ Por cada evento:
    ├── Si no existe PublicationRequest previo → crea uno (portal_only) → encola jobs
    └── Crea UserNotification para el creador del evento
```

---

## 6. Estrategia SEO

| Elemento | Implementación |
|---|---|
| **URLs amigables** | Slugs descriptivos y largos ya posicionados (ej: `/noticias-del-folklore-argentino`) |
| **Meta tags** | Title y description personalizados por sección |
| **Datos estructurados** | JSON-LD con schemas de WebSite, Organization y SearchAction |
| **Sitemaps** | Sitemap general + sitemap de noticias para Google News |
| **Breadcrumbs** | Componente de migas de pan para navegación |
| **Imágenes optimizadas** | Componente `optimized-image` para carga eficiente |

---

## 7. Integraciones Externas

| Servicio | Uso |
|---|---|
| **Google Analytics** | Seguimiento de tráfico y comportamiento de usuarios |
| **Google AdSense** | Monetización mediante publicidad |
| **Google OAuth** | Login social |
| **Facebook OAuth** | Login social |
| **YouTube** | Enlaces a videos de canciones y artistas |
| **Spotify** | Enlaces a canciones y álbumes |
| **Facebook Graph API** | Publicación en páginas de Facebook (Pasarela) |
| **Instagram Graph API** | Publicación en cuentas profesionales (Pasarela) |
| **Telegram Bot API** | Publicación en canales y grupos (Pasarela) |

---

## 8. Módulos Planificados (Próxima versión)

| Módulo | Descripción |
|---|---|
| **Entrevistas** | Sección dedicada a entrevistas con artistas. Controller existe sin métodos ni vistas. Requiere spec completa. |
| **Radios** | Directorio de radios de folklore. Índice frontend funciona, detalle sin implementar. Sin backend. |
| **Peñas** | Directorio de peñas folklóricas. Mismo estado que Radios. |
| **Escuelas de Danzas** | Directorio de escuelas y academias de danzas folklóricas. No iniciado. |
| **Videos** | Sección de videos. No iniciado. |

---

## 9. Componentes de UI Reutilizables

El frontend utiliza componentes Blade reutilizables:

| Componente | Uso |
|---|---|
| `noticia-card` | Tarjeta de noticia en listados |
| `noticia-card-artista` | Tarjeta de noticia en contexto de artista |
| `biografia-card` | Tarjeta de artista/biografía |
| `disco-card` | Tarjeta de álbum |
| `letra-card` | Tarjeta de canción/letra |
| `show-card` | Tarjeta de evento/show |
| `festival-card` | Tarjeta de festival |
| `mito-card` | Tarjeta de mito/leyenda |
| `receta-card` | Tarjeta de receta |
| `compartir-redes` | Botones de compartir en redes sociales |
| `breadcrumbs` | Navegación tipo migas de pan |
| `optimized-image` | Imagen optimizada con lazy loading |
| `navbar` | Barra de navegación principal |
| `sidebar.*` | Componentes de sidebar (newsletter, redes, donación) |
