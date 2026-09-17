# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).
Versionado siguiendo [Semantic Versioning](https://semver.org/lang/es/).

## [Unreleased]

## [2.2.1] — 2026-09-16

### Corregido

- **Error 500 en recuperación de contraseña**: se agregó el trait `CanResetPassword` al modelo User, que proporciona el método `sendPasswordResetNotification()` necesario para el flujo de password reset de Laravel. Sin este trait, la ruta POST `/password/email` devolvía error 500.

## [2.2.0] — 2026-09-13

### Agregado

- **Buscador de artistas**: buscador por nombre en Artistas (no existía ninguno funcional; dos métodos previos del controller no tenían ruta asignada), con "Agregados recientemente" y "Los más leídos" arriba del listado alfabético.
- **Contadores de volumen**: cifras reales de catálogo ("Ya reunimos X artistas/recetas/discos...") en la Home y en las 8 homes de sección principales.
- **Números de página en toda la paginación**: las secciones que usaban `simplePaginate()` (Noticias, Discos, Artistas, Canciones, Mitos, Recetas) pasan a `paginate()` para mostrar total de resultados y saltar a cualquier página — mismo componente compartido que ya usaba Festivales, no eran componentes distintos.
- **Widget "Artistas más leídos"**: nuevo componente de sidebar para la ficha de artista, la única página de detalle del sitio sin ningún tipo de descubrimiento de más contenido.
- **Guía de imágenes por entidad**: `project/docs/editorial/image-upload-guide.md` con tamaño mínimo y ratio recomendado de subida para cada tipo de contenido, basado en los perfiles reales de `config/image_profiles.php`.
- **Sidebars con criterio explícito por sección**: publicidad en Noticias/Recetas/Mitos, invitación a publicar en Festivales/Cartelera, sidebar propio (ya no heredado del default sin decisión) en Clasificados/Peñas/Radios.
- **Íconos de redes sociales** con los colores reales de marca (Facebook, Instagram, X) como insignias cuadradas, en vez del naranja de marca del sitio aplicado por igual a los tres.
- **Directorio evergreen de Peñas**: propuesta OpenSpec completa para un módulo de espacios culturales permanentes, separado de Eventos y de los datos legacy. Define el contrato editorial, piloto verificable, auditoría, API, SEO y migración segura antes de implementar.
- **Piloto controlado de Content Refresh**: runbook, preflight local y pruebas para las seis operaciones obligatorias de crear y actualizar un Artista, una Receta y un Mito.
- **Recorrido Festival vivo**: especificación de un recorrido transversal desde Festival evergreen hacia un Evento futuro y Artista relacionado, condicionado por cobertura auditada y feature flag.
- **Curación de Biografías P1**: borrador editorial de Juan Bautista Bertorello incorporado al lote controlado BL-0021B.

### Cambiado

- **Copy y tipografía unificados**: mismo contenedor, tamaño de H1 y un solo párrafo corto de intro (en vez de bloques largos de relleno genérico, varios con texto sin terminar) en las 8 homes de sección más Peñas/Radios.
- **Tarjetas de Mitos y Recetas** rediseñadas con el mismo patrón visual que Artistas/Discos (imagen completa arriba, título y extracto abajo) en vez del formato horizontal heredado de Canciones.
- **Orden de bloques**: en Noticias, "Últimas" pasa a ir antes que "Más leídas"; en Artistas, "Agregados recientemente" sube antes que "Los más leídos" y el índice alfabético baja al final de la página.
- **Botones de buscador** unificados a `rounded-lg` (el mismo redondeado del nav y de "Suscribirme") en Artistas, Cartelera, Festivales, Peñas y Radios — antes varios usaban `rounded-full` o colores fuera de la paleta de marca (azul, ámbar).
- **Relacionados abajo, no al costado**: Mitos y Recetas mostraban su bloque de "relacionados" en una columna lateral propia; ahora es una sección de ancho completo debajo del contenido, igual que el resto del sitio.
- **Gobernanza documental**: `00_estado_actual.md` consolidado sin marcadores de conflicto y actualizado con los gates operativos y la propuesta de Peñas.
- **Automatización editorial**: la validación del piloto exige `ACCION_API` explícita y evita actualizaciones vacías antes de cualquier automatización de volumen.
- **CI de Apps Script**: el pipeline incorpora la validación del preflight de Content Refresh.

### Corregido

- **Bug real de performance**: 10 clases de componente Blade con constructor vacío (`app/View/Components/*NoticiaCard, DiscoCard, FestivalCard...`) tapaban los componentes anónimos reales y descartaban en silencio props como `imageLoading`/`imageFetchpriority` — por esto la carga `eager` del LCP de la home nunca funcionó pese a estar en el código desde antes. Se eliminaron esas 10 clases más 8 análogas en `app/View/Components/Sidebar/` (rutas y campos ya inexistentes desde el refactor de modelos: `interprete.show`, `festival.show`, `$cancion->titulo`, etc.), sin uso en ningún lado del sitio.
- **Bug real de imágenes**: `EditorialImageResolver` volcaba un modelo Eloquent completo como JSON en el `alt` de una imagen de fallback para Álbumes/Canciones, por la relación `interprete()` con el mismo nombre que la columna del modelo `Interprete`.
- **Bug de SEO en Clasificados**: el título y la meta description nunca se aplicaban (`@section('title')`/`@section('meta_description')` en vez de los nombres reales que lee el layout); nunca tuvo metadatos propios en producción.
- **Rutas que bloqueaban `route:cache`**: un closure en `/api/user` y una colisión de nombres entre `penia-profiles.index` (API y frontend).
- **Cache de imágenes fallback**: se redujeron ~75% de peso y se renombraron con sufijo `-v2` porque el cache `immutable` del servidor no invalida por contenido, solo por URL.
- **Badge "Nuevo" poco confiable**: se sacó de Artistas/Discos/Mitos/Recetas porque `created_at` en estos catálogos migrados en bloque no refleja alta real de contenido.

## [2.1.0] — 2026-05-02

### Agregado
- **Servicio ImageSourceResolver**: Centraliza la resolución de imágenes desde archivos subidos, rutas locales y URLs externas.
- **Seguridad SSRF**: Protección robusta que bloquea la descarga de imágenes desde IPs privadas o locales (127.0.0.1, 10.x, etc.).
- **Jerarquía de Excepciones**: Nuevas excepciones específicas para el manejo de errores de medios (`ImageSecurityException`, `ImageDownloadException`, `ImageInvalidException`).
- **Tests de Integración**: Suite `UnifiedNewsFlowTest` para validar los flujos de noticias y la seguridad de descargas.

### Corregido
- **Unificación de NewsService**: Punto único de entrada para todas las noticias, garantizando WebP y validación de slugs en todos los flujos (Admin, API, Contribuciones).
- **Desacoplamiento de Autoría**: El servicio ahora soporta `created_by` (autor original) y `approved_by` (moderador) de forma explícita.
- **Limpieza de Temporales**: Eliminación garantizada de archivos descargados tras el procesamiento de la noticia.
- **Moderación y Contribuciones**: Los controladores ahora delegan la lógica de persistencia al servicio centralizado, eliminando código duplicado.

---

## [2.0.2] — 2026-04-27

### Corregido

- **Imágenes legacy con fallback progresivo**: todos los componentes de tarjeta (`noticia-card`, `biografia-card`, `disco-card`, `festival-card`, `show-card`) y vistas de detalle (`noticias/show`, `discos/show`, `festivales/show`, `shows/show`, `interpretes/show`) ahora verifican con `file_exists()` si la imagen legacy existe en disco antes de renderizarla; si no existe, muestran `<x-image-placeholder>` en lugar de la etiqueta rota del navegador.
- **`interpretes-header`** (sidebar): agrega fallback a foto legacy del intérprete antes de mostrar el placeholder.
- **`shows/show`**: amplía la cadena de fallback añadiendo la foto legacy del intérprete (`storage/interpretes/{foto}`) entre `imagen_destacada` y el placeholder.

---

## [2.0.0] — 2026-04-26

Historial de estabilización integrado en la rama `v2` antes de las versiones posteriores.

### Corregido (2026-04-26 — post-deploy)

- **Media URLs**: `ImageUploadService` almacenaba URLs absolutas con el `APP_URL` del momento del upload — `MediaAsset` ahora resuelve rutas dinámicamente con `resolveStorageUrl()`, compatible con registros viejos (extrae ruta relativa de la URL absoluta) y nuevos (ruta relativa pura). Sin cambios de DB. Imágenes subidas en localhost ya se ven en producción.
- **InstagramConnector**: `resolveImageUrl()` envolvía la URL ya resuelta con `asset('storage/...')` — corregido para usar directamente las URLs del modelo.

### Corregido (2026-04-26 — estándares de imagen)

- **`ImageUploadService`**: ahora guarda rutas relativas en `original_path` y `variants_json`, no URLs absolutas. Incluye `disk='public'` explícito.
- **FestivalController**: usaba perfil `news_full` para festivales — corregido a `festival` (las variantes `hero`/`main` nunca se generaban para este tipo).
- **EventService**: ídem — corregido de `news_full` a `event`.

### Agregado (2026-04-26 — estándares de imagen)

- **`x-image-placeholder`**: nuevo componente Blade que muestra ícono SVG de cámara + texto "Sin imagen" sobre fondo gris. Acepta `:label="null"` para modo compacto (sidebars 64×64).
- **Tamaños de variante actualizados** en `config/image_profiles.php`:
  - `artist.card`: 80–160px → 300–800px (tarjetas `h-96` y portrait sidebar 400px)
  - `album.card`: 80–160px → 200–600px (tarjetas `h-96`)
  - `festival`: agregada variante `hero` (768–1600px); `card` ampliada a 4 breakpoints
  - `event.card`: 320–480px → 480–1024px (tarjetas `h-96`, ratio 16:9 a ~682px mínimo); agregada variante `hero`
- **Placeholder en tarjetas**: `noticia-card`, `biografia-card`, `disco-card`, `festival-card`, `show-card` — rama `@else` reemplazada por `<x-image-placeholder>`
- **Placeholder en vistas de detalle**: `noticias/show`, `discos/show`, `festivales/show`, `shows/show`
- **Placeholder en sidebars**: `card-discos`, `card-biografias`, `card-shows` ahora usan `x-optimized-image` si el modelo tiene imagen nueva, legacy `<img>` si hay foto en storage, o placeholder compacto como última opción
- **`interpretes-header`**: fallback `<img>` legacy reemplazado por placeholder

### Corregido (Sprint 1 — 2026-04-26)

- **GAP-01** · ModerationController: query `orWhereNull('published_at')` sin agrupar devolvía toda la tabla — envuelto en closure
- **GAP-02** · UGC: slug collision al aprobar contenido nuevo — se genera slug único con sufijo incremental antes de `save()`
- **GAP-03** · PublicationService: `createRequest()` usaba `Auth::id() ?? 1` fallback — ahora acepta `$userId` como parámetro; `EventReminderJob` pasa `$event->created_by`
- **GAP-04** · API: cualquier token Sanctum podía hacer DELETE/PUT en cualquier entidad — rutas de escritura separadas bajo `middleware('role:administrador')`
- **GAP-11** · UserNotification: tabla `notifications` colisionaba con el sistema nativo de Laravel — renombrada a `user_notifications` (migración pendiente de ejecutar)

### Corregido (Sprint 2 — 2026-04-26)

- **GAP-05** · SocialAccount: tokens ya tenían cast `encrypted` — confirmado como ya resuelto, sin cambios necesarios
- **GAP-06** · Permisos granulares: las 10 policies existían pero 7 no estaban registradas en `AuthServiceProvider` y 6 controllers no llamaban a `authorizeResource` — registradas todas las policies faltantes (Album, Cancion, Festival, Mito, Comida) y agregado `authorizeResource` en NewsController, AlbumController, CancionController, FestivalController, MitoController y ComidaController

### Corregido (Sprint 3 — 2026-04-26)

- **GAP-07** · Pasarela: `wants_portal_social` publicaba en cuentas de todas las organizaciones — ahora filtra por `PORTAL_ORGANIZATION_ID` (variable de entorno); si no está configurada, loguea un warning y no crea targets
- **GAP-08** · Pasarela: admin recibía 403 al ver solicitudes de otros usuarios — `authorizeRequest()` ahora exceptúa al rol `administrador`
- **GAP-09** · UGC: `rank` null en usuarios nuevos mostraba campo vacío — accessor `getRankAttribute()` devuelve `'Colaborador'` como default
- **GAP-10** · Clasificados: `expiration_date` se calculaba en el `store` del frontend y luego se pisaba en la aprobación admin — eliminada del `store` frontend; solo se asigna al aprobar

### Agregado (Sprint 4 — 2026-04-26)

- **GAP-12** · UGC: notificación in-app al usuario cuando su contribución es aprobada o rechazada
- **GAP-13** · Clasificados: notificación in-app al anunciante cuando su aviso es aprobado o rechazado
- **GAP-14** · Pasarela: botón "Reintentar" en targets fallidos — ruta `POST /admin/pasarela/publication-requests/{id}/targets/{target}/retry`
- **GAP-15** · Moderación: botón "Publicar" inline en el panel para news, events, intérpretes, álbumes y canciones — ruta `POST /admin/moderation/{type}/{id}/publish`

### Corregido / Agregado (Sprint 5 — 2026-04-26)

- **GAP-16** · Classified: `is_active` convertido en accessor calculado desde `estado` — eliminado del fillable y casts; ya no puede quedar inconsistente con `estado`
- **GAP-17** · UGC: type `show` reemplazado por `event` en `ALLOWED_TYPES`; se agrega `TYPE_TO_MODEL` map explícito, eliminando el `ucfirst()` dinámico
- **GAP-18** · Perfiles de imagen: agregados `festival`, `mito` y `event` en `config/image_profiles.php`
- **GAP-19** · Clasificados: límite de 5 imágenes por aviso en el `store` frontend
- **GAP-20** · API: nuevo recurso `events` — `GET /api/v1/events`, `GET /api/v1/events/{id}` públicos; write endpoints solo admin
- **GAP-21** · API versioning: diferido — requiere decisión de arquitectura (route groups paralelos vs. Laravel Resources). Ver nota en DEPLOY_PLAN
- **GAP-22** · News: `estado` sincronizado con `editorial_status` via accessor/mutator — setear uno actualiza el otro
- **GAP-23** · Event: mismo patrón que GAP-22 — `estado` y `editorial_status` ahora son consistentes
- **GAP-24** · PublicationRequest: constantes `STATUS_*` definidas en el modelo (`pending`, `processing`, `completed`, `failed`, `cancelled`)

---

## [2.0.0-beta] — 2026-04-26

Primera versión completa del sistema refactorizado. Incluye Pasarela de Contenidos, UGC, API REST v1, sistema de imágenes WebP, roles/permisos Spatie y clasificados.

### Corregido (sesión de estabilización 2026-04-26)

- **Navbar**: ruta `contributions.index` inexistente causaba error en todas las páginas del frontend — corregido a `backend.contributions.index`
- **Noticias**: 404 al ver detalle de noticia con URL `/{interprete}/noticias/{slug}` — error de orden de parámetros en `NoticiasController::show()`
- **Contribuciones**: ruta `contributions.create` inexistente en vistas de detalle de intérprete, canción, festival, show y noticia — corregido a `backend.contributions.create`
- **Canción detalle**: `Call to toIso8601String() on null` en JSON-LD cuando `created_at` es null — agregado guard nulo
- **Kernel**: middlewares de Spatie (`role`, `permission`, `role_or_permission`) no estaban registrados — error `Target class [role] does not exist` en todo el backend con auth
- **ModerationController**: relación `user` inexistente en modelos `Interprete`, `Album` y `Cancion` — eliminado el `with('user')` en esas queries
- **Moderación**: `format() on null` en columna fecha de noticias cuando `created_at` es null — agregado operador nulo
- **Pasarela / create**: `resolveContent()` recibía null cuando se accedía al formulario sin query params — agregado guard; muestra aviso orientativo en lugar de explotar
- **Pasarela / create**: `Attempt to read property "title" on null` en la vista — envuelto el formulario en `@if ($content)`
- **Clasificados**: solapas Pendientes / Activos / Rechazados no funcionaban — atributos Bootstrap 5 (`data-bs-toggle`) reemplazados por Bootstrap 4 (`data-toggle`) compatible con AdminLTE 3
- **Clasificados**: vistas `create.blade.php` y `edit.blade.php` estaban vacías — redirigen a `form.blade.php`
- **Contribuciones (Backend)**: middleware `role:administrador` faltaba en el constructor — cualquier usuario autenticado podía ver todas las contribuciones
- **Contribuciones (Backend)**: puntos siempre se asignaban como edición (20 pts) en lugar de alta nueva (50 pts) — `$isNew` se capturaba después de modificar `contributable_id`
- **Contribuciones (show)**: XSS en vista admin — `{!! $value !!}` sobre payload de usuario reemplazado por `{{ $value }}`

### Agregado

- `CHANGELOG.md` — este archivo
- `project/docs/GAPS_BACKLOG.md` — backlog priorizado de 24 gaps para v2.0.0 estable
- Secciones en `FUNCIONAL.md`: Pasarela (3.4), Sistema de Imágenes (3.6), API expandida (3.3), Roles (4), Clasificados (3.1.12), Flujos nuevos (5.5, 5.6), Glosario (7)

---

## [1.x] — historial anterior

Ver historial de commits en git (`git log --oneline`) para cambios anteriores a la rama `v2`.
