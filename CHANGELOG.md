# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/).
Versionado siguiendo [Semantic Versioning](https://semver.org/lang/es/).

## [Unreleased]

### Agregado

- **Formulario de contacto rehecho y visible** (spec `contact-form-overhaul`): `/contacto` deja de dar error (la tabla `contact_messages` no existía en ninguna migración), se reescribe en Tailwind y accesible, y se enlaza desde el menú (Más) y el footer. Sin captcha visible: campo trampa, trampa de tiempo, límite por IP y por email, detección de duplicados y filtro de enlaces; consentimiento con enlace a privacidad. Los mensajes se guardan siempre y los fallos de correo se registran; el aviso al equipo tiene destinatario configurable y Reply-To del remitente. Nueva bandeja **Mensajes de contacto** en el backoffice (solo administrador: filtros, búsqueda, responder por email, estados, eliminar) y contador en el dashboard. Requiere la migración `2026_10_13_100000` (defensiva: crea la tabla o agrega solo las columnas que falten). Se recompiló `public/build`.
- **Menú de usuario con avatar y página "Mis datos"** (spec `my-account`): el menú del backoffice muestra un avatar de iniciales (generado, sin subida de archivos), el nombre y el rol, y un botón **Mis datos** junto a **Salir**. `/admin/mi-cuenta` permite actualizar nombre, teléfono y contraseña (el email es solo lectura), y resume Mis fichas y solicitudes, Mi contenido (publicado / en revisión) y la suscripción al newsletter. Disponible para cualquier usuario con sesión, independiente del acceso por fichas. El último ingreso (`last_login_at`) se registra en cada login. El CRUD de Usuarios comparte las reglas (contraseña mínima 8) y ahora edita el teléfono.
- **Enlace "Solicitá administrar su ficha" en las páginas públicas** (spec `remove-contribuir-flow`): el home y la biografía del artista, y la ficha del festival, invitan al responsable a pedir el vínculo; pasa por el login y cae en Mis fichas con la ficha elegida (`nofollow`, `data-nosnippet`). Reemplaza a los enlaces "Sugerir corrección" retirados.
- **Noticias, Mitos y Comidas dentro del acceso por fichas** (spec `gate-editorial-sections-by-entities`, opción C; con `BACKOFFICE_ACCESS_BY_ENTITIES` activo): Noticias para quien administra un artista o es `verificado`; Mitos y Comidas solo para `verificado` y administradores. Mensaje de rechazo propio para las secciones solo por rol. Lo ya publicado no se toca.
- **Eventos de festivales para quien administra el festival** (spec `entity-member-content-access-festival-events`, Etapa 3): un miembro owner/editor activo crea eventos que incluyan su festival (o su artista) y edita los eventos de su festival aunque tengan artistas ajenos en la grilla; los eventos enlazados a un festival ajeno quedan bajo ese festival (ya no los edita el administrador de un artista). Todo a revisión. Jornadas siguen la misma regla.
- **Revisión de solicitudes delegada al responsable de la ficha** (spec `owner-delegated-claim-review`): el owner activo de una ficha aprueba o rechaza, desde Mis fichas, las solicitudes de nuevos editores de su propia ficha (rol Editor o Miembro, nunca Responsable; el rechazo exige motivo; no ve email ni teléfono del solicitante). El administrador aprueba al primer owner, las altas de ficha y conserva todo su flujo. Interruptor: `ENTITY_CLAIMS_DELEGATE_TO_OWNERS` (activo por defecto).
- **Camino de confianza para publicar y baja del rol global `prensa`** (spec `roles-trust-ladder-and-cleanup`): nuevo permiso `publish contents` y rol `verificado` (aditivo a colaborador): lo que escribe se publica directo en Discos, Canciones, Eventos, Noticias, Intérpretes y Festivales; el resto sigue en revisión. "Prensa" pasa a modelarse como relación con una ficha. Requiere migración `2026_10_12_100000` (pendiente de ejecución). Modelo documentado en `project/docs/permisos_y_roles.md`.
- **Rol sugerido al aprobar solicitudes de vínculo** (spec `claim-approval-role-suggestion`): el detalle de la solicitud preselecciona el rol (artista, representante u organizador → responsable; prensa u otra → editor; baja a editor si la ficha ya tiene responsable) y muestra el motivo y alertas (organización inactiva, responsable existente, solicitante ya miembro, alta sin respaldo o con nombres parecidos). Solo orienta: el administrador decide. Mapa en `config/entity_management.php`.
- **Eventos y noticias de artistas para quien administra su ficha** (spec `entity-member-content-access-events-news`, Etapa 2): un miembro owner/editor activo crea eventos y noticias que incluyan a su artista (puede sumar invitados) y edita aquellos donde administra todos los artistas vinculados o que creó. Todo queda en borrador para revisión: ahora los servicios de Eventos y Noticias nunca publican si el usuario no puede publicar, aunque el request traiga `estado=1`. Listados de Eventos y Noticias limitados a lo propio o editable.
- **Solicitud de alta de ficha** (spec `entity-creation-requests`): quien no encuentra su artista o festival en Mis fichas puede pedir que se cree, con su relación, mensaje y link de respaldo, viendo antes las fichas con nombre parecido. El administrador crea la ficha mínima sin publicar, la vincula a una existente (duplicado) o rechaza. Requiere migración `2026_10_11_120000` (aplicada en local).
- **Acceso al backoffice según las fichas** (spec `backoffice-access-by-entities`, flag `BACKOFFICE_ACCESS_BY_ENTITIES`, apagado por defecto): con el flag activo, quien no administra fichas ve solo "Mis fichas" en las secciones que dependen de artistas o festivales (Intérpretes, Discos, Canciones, Festivales, Eventos) y es redirigido a Mis fichas con un aviso si entra por URL (403 JSON en AJAX). Con ficha de artista o festival se habilitan las secciones correspondientes. Mitos, Comidas y Noticias no se tocan por ahora.
- **Contenido del artista para quien administra su ficha** (spec `entity-member-content-access`, Etapa 1): un miembro owner/editor activo de una organización activa puede crear, editar y publicar directamente discos y canciones (incluida la letra) de los artistas que administra, sin roles ni permisos globales. Listados y selector de artistas limitados a los suyos; `interprete_id` validado en servidor. `delete` sigue restringido. Etapas 2 (eventos/noticias) y 3 (festivales) pendientes.
- **Demos locales de Eventos:** dos conjuntos de cuatro/cinco jornadas y un evento independiente, con sedes e intérpretes relacionados para probar herencia y excepciones. Datos ficticios identificados; script restringido a local y sin duplicados. Spec `event-demos-and-sidebar`.

- **Eventos con jornadas y Salas/Lugares (implementado y validado en local):** carga común de conjuntos/ediciones con fichas por jornada, artistas propios y excepciones; ABM independiente de lugares con ubicación y coordenadas; CKEditor en Eventos; reglas compartidas en los ingresos editoriales y agenda que contempla duración. Migración local aplicada con autorización; 98 pruebas Feature pasan. Spec `event-programs-and-venues`. Revisión interactiva pendiente; sin despliegue.

- **Solicitud de vínculo usuario ↔ ficha** (spec `entity-claim-requests`): cualquier usuario autenticado, con o sin rol y con login común, Google o Facebook (mismo guard), puede pedir administrar un intérprete o festival desde **Mis fichas** (`admin/mis-fichas`: fichas administradas, formulario con búsqueda, estado de solicitudes y cancelación). El administrador las revisa en **Solicitudes de vínculo** (`admin/claims`): aprueba con rol (usa la organización activa de la ficha o crea la principal y agrega al usuario como miembro activo) o rechaza con motivo visible para el usuario. Límites: una pendiente por usuario y ficha, máximo 5 pendientes, throttle de envío y bloqueo si ya administra la ficha. Una tabla nueva (`entity_claim_requests`), reversible. Sin avisos por correo.
- **Seguimiento de contacto en Organizaciones** (spec `organization-contact-tracking`): Instagram y Facebook, etapa del contacto (`prospect`, `contacted`, `replied`, `email_obtained`, `declined`), último contacto y notas. El listado suma filtro por etapa, enlaces a los perfiles, búsqueda por Instagram y la acción "Registrar contacto". Nuevo export CSV de contactables (solo activas, con consentimiento, sin baja y con email; respeta búsqueda y tipo; neutraliza fórmulas de planilla). Una migración reversible con 5 columnas; sin envío de correos (queda para cuando haya volumen).
- **Backoffice de Organizaciones** (spec `organization-admin-backoffice`, solo rol `administrador`, `admin/organizations`): listado paginado con búsqueda, filtros (tipo, estado, verificadas, contactables) y orden; alta y edición con datos de contacto, consentimiento, baja y verificación; vínculo y desvínculo de intérpretes y festivales con búsqueda remota (Select2) y organización principal por ficha; miembros con rol y estado (aprobar una solicitud es pasar `pending` a `active`). Las organizaciones no se borran: se desactivan, lo que revoca los permisos de sus miembros. Tipos vinculables configurables en `config/entity_management.php`. Sin migraciones; menú "Usuarios y Roles > Organizaciones".
- **Administración de fichas por organización** (spec `organization-entity-management`): nueva tabla `organization_entities` que vincula organizaciones con intérpretes y festivales (N:M, polimórfica). Un usuario con membresía activa `owner` o `editor` en una organización activa puede editar las fichas vinculadas; `member`, `pending` e `inactive` no otorgan escritura. Se conserva el acceso del administrador y el legacy por `user_id`. `organizations` suma consentimiento de contacto, baja y verificación (scope `contactable`). El listado de festivales de colaboradores y prensa incluye las fichas administradas; el de intérpretes se recorta solo con `ENTITY_MANAGEMENT_SCOPE_LISTINGS=true` (apagado por defecto). Al crear una ficha un no administrador, se crea su organización con él como `owner`. Una migración reversible, sin migración de datos. No incluye pantallas de administración, eventos/noticias ni login social.
- **Contenidos vinculados en festivales**: las fichas publicadas muestran próximas fechas, artistas activos, noticias y artículos evergreen relacionados sin depender del piloto Festival Vivo. Los bloques vacíos se ocultan y se mantienen los tres relacionados por provincia y por mes. Las próximas fechas se paginan de tres en tres, con título y hora de cada jornada y acceso a todas las noches cargadas.
- **Ficha de evento con navegación y continuidad** (`/cartelera-de-eventos-folkloricos/{slug}`): ficha técnica arriba del cuerpo (fecha y hora, lugar, dirección, provincia enlazada, precio o "Gratis", botón de entradas y "Cómo llegar"), que solo muestra datos cargados. Aviso "Este evento ya se realizó" en eventos pasados, que siguen indexables. Breadcrumb `Cartelera > Provincia > Evento`. Bloques "Más de {artista}" (próximas fechas o, si no hay, hasta 3 presentaciones anteriores, con enlace a la biografía), "Próximos eventos en {provincia}" y "Artistas en escena", visibles sin depender del piloto Festival Vivo. El buscador del índice de la cartelera se reutiliza en la ficha.
- **Relaciones del evento**: bloques "Forma parte de" (festivales), "Historia y contexto" (enciclopedia), "Peñas" (con `FEATURE_PENIA_DIRECTORY`) y "Noticias relacionadas", derivadas del festival y de los artistas del evento. Solo se muestran si tienen contenido publicado y registran el recorrido con módulos `event_related_*`. Nueva relación `Event::knowledgeArticles()` sobre el pivot existente.
- **Backend de eventos**: selectores Select2 para vincular festivales, artículos de enciclopedia y peñas, y campos `end_at` y `excerpt`. Sincroniza solo los pivots existentes; sin migraciones.

### Cambiado
- **Copa del Folklore, Enciclopedia y Peñas solo para administradores** (con `BACKOFFICE_ACCESS_BY_ENTITIES` activo): se ocultan del sidebar y sus rutas redirigen a Mis fichas con aviso para cualquier otro usuario, incluidos responsables de fichas y `verificado`.
- **Base de tests aislada (`mfa_test`) y datos de referencia para tests/CI** (spec `isolated-test-database`): `.env.testing` apunta a `mfa_test`; nuevo `TestingReferenceDataSeeder` (roles y permisos reales) que el CI ejecuta tras `migrate:fresh`; los tests ya no asumen `user_id=1` ni provincias sembradas. En base limpia la suite pasa completa (557).
- **Login y registro sin botones de Google y Facebook** (spec `hide-social-login-buttons`): se ocultan mientras el login social no funcione; se muestra solo email y contraseña. Se reactivan con `SOCIAL_LOGIN_ENABLED=true` sin tocar código; las rutas `auth/*` se conservan.

- **Ingreso unificado de Eventos:** único Nuevo evento con Una fecha o Varias jornadas, datos comunes y grillas por jornada; listado con una fila por evento y posibilidad de agregar jornadas a uno existente conservando su ficha. Se elimina Conjuntos / Jornadas del sidebar y se mantienen URLs/compatibilidad. Validado en local con regresión de 106 pruebas; sin nueva migración.

- **Sidebar del Backoffice:** módulos de Música y Cultura como enlaces directos, Pasarela de Contenidos plegable y orden alfabético ASC en cada sección y submenú, conservando permisos y URLs.

- **Consultas y SEO de la ficha de festival**: se eliminan las cargas completas y duplicadas de relaciones, con colecciones acotadas y fallback de imagen preservado. La paginación conserva la URL canónica de la ficha y usa `noindex,follow` en páginas posteriores. Sin nuevas dependencias, scripts globales ni migraciones.
- **"Explorar por provincia"** en la cartelera muestra solo provincias con eventos futuros publicados y su cantidad (antes, las 8 primeras en orden alfabético).
- **JSON-LD `Event` de la ficha**: agrega `eventStatus`, `eventAttendanceMode`, `endDate`, dirección estructurada (`PostalAddress`), `offers` solo con datos cargados, `organizer` y `superEvent`.
- **Performance de la cartelera**: provincias, resolución de provincia por slug e intérpretes del buscador quedan en caché; se eliminaron consultas sin uso en la ficha. Las consultas de continuidad tienen `limit` en SQL y eager loading.

### Eliminado

- **Flujo público "Publicar / Contribuir"** (spec `remove-contribuir-flow`): se retiran `/admin/contribuir`, la moderación `/admin/contributions`, sus controladores, vistas, el modelo `Contribution`, el contador del dashboard y los 4 enlaces "Sugerir corrección" de las páginas públicas. "Publicar" ahora lleva a `/admin` (pasando por el login) y rigen las fichas, solicitudes y roles; con el acceso por fichas activo, quien no es administrador entra a Mis fichas. Migración `2026_10_12_140000` elimina la tabla `contributions` y las columnas `users.points`/`users.rank` (sin datos en producción, confirmado; `down()` recrea la estructura, no los datos).

### Corregido
- **`canciones/store-ajax` sin autorización:** cualquier usuario autenticado podía crear canciones publicadas para cualquier artista. Ahora aplica la regla de creación de Canciones, limita los artistas a los que administra quien no tiene permiso global y publica solo si el usuario puede publicar. Tests de Eventos y Noticias ya no dependen de provincias sembradas.
- **Permisos de edición y publicación:** `update album/cancion/noticia` ya no permiten editar contenido ajeno (edita el creador o quien administra el artista); Álbumes y Canciones conservan el creador al editar; el estado de un Festival ya no lo decide el request; `UserPolicy::viewAny` solo para administrador; `isAdmin()` solo reconoce `administrador`.

- **Description del JSON-LD en la cartelera de Eventos:** ahora usa la misma prioridad que la ficha (meta_description, excerpt, body, texto genérico) en lugar de solo `body`, que podía salir vacío. Sin cambios de BD ni de URLs. Spec `event-agenda-description-fallback`.

- **Listado de Eventos con roles combinados:** un administrador que también es colaborador/prensa ya no recibe el filtro por autor; conserva visibilidad administrativa. Los demás colaboradores siguen viendo sus eventos.

- **Imágenes rotas cuando el archivo no existe**: `EditorialImageResolver` verifica en el disco local que exista la imagen propia, la legacy o la del artista relacionado y, si falta, usa el fallback editorial de la entidad. Aplica a todas las secciones. `optimized-image` toma el primer grupo de variantes no vacío.
- **Ficha de evento a ancho completo**: se quitaron el contenedor anidado y la columna `lg:w-2/3`, que dejaban el contenido en la mitad del ancho disponible.

## [2.3.1] — 2026-09-18

### Corregido

- **Loop de login en `/admin`**: tras un login exitoso, `/admin` volvía a redirigir a `/login` en vez de mostrar el panel. Causa: `Spatie\ResponseCache\Middlewares\CacheResponse` corre en el stack global, antes de `StartSession` (ver `App\Http\Kernel`), así que `request()->user()` es siempre `null` cuando `PublicPagesCacheProfile::shouldCacheRequest()` decide si cachear — la regla "nunca cachear si hay usuario logueado" nunca se disparaba. Como el paquete trata los redirects como cacheables (`CacheAllSuccessfulGetRequests::hasCacheableResponseCode`), un solo `GET /admin` sin sesión (bot, o el primer visitante del día) dejaba cacheado el 302 a `/login`, y ese redirect viejo se le servía después a cualquiera — logueado o no — sin llegar a evaluar la sesión real. Se agregaron `admin*`, `avisos-clasificados/publicar` y `avisos-clasificados/mis-avisos` (las otras rutas GET protegidas con `auth` fuera de `/admin`) a `excludedPatterns` en `App\Support\ResponseCache\PublicPagesCacheProfile`. Requiere desplegar y correr `artisan responsecache:clear` (ya incluido en `scripts/post-deploy.sh`) para purgar el redirect ya cacheado en producción.

## [2.3.0] — 2026-09-17

### Agregado

- **Mensajes de autenticación y validación en español**: `config/app.php` ya declaraba `locale => 'es'`, pero `resources/lang` sólo tenía la carpeta `en`, así que todo caía al fallback inglés. Se agregaron `resources/lang/es/{auth,passwords,validation,pagination}.php` y `resources/lang/es.json`. Cubre los mensajes de login y registro, los cinco de recuperación de contraseña, las 101 reglas de validación con nombres de campo legibles, y el cuerpo completo de los correos transaccionales de recuperación y verificación. Los textos de los formularios ya estaban en español porque los aporta AdminLTE.

### Cambiado

- **`scripts/post-deploy.sh`**: el script regeneraba las cachés sin actualizar antes el mapa de clases. Ahora corre composer primero (`install --no-dev --optimize-autoloader` si cambió `composer.lock`, detectado por hash; `dump-autoload -o` en caso contrario), suma el `responsecache:clear` que faltaba pese a tener instalado `spatie/laravel-responsecache`, permite forzar el binario de PHP en hosts con varias versiones, y avisa que con la config cacheada los cambios en `.env` dejan de tener efecto.

### Corregido

- **Error 500 en `/password/email`**: la causa era de configuración, no de código. El SMTP de Hostinger rechazaba la autenticación porque `MAIL_PASSWORD` estaba sin comillas en el `.env` de producción y el carácter `#` abre un comentario, truncando la contraseña. Con `QUEUE_CONNECTION=sync` el envío ocurre dentro del request, así que la excepción de transporte se convertía en un 500 visible.

### Revertido

- El diagnóstico previo de ese mismo error, que atribuía el 500 a la falta del trait `CanResetPassword` en el modelo User. `Illuminate\Foundation\Auth\User` ya lo incluye, y el test que acompañaba el cambio no verificaba nada. La versión 2.2.1 que lo documentaba fue retirada y su tag nunca llegó al remoto.

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
