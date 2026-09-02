# 10 - Auditoria de Contratos Editoriales por Entidad

> Fecha de auditoria: 2026-08-21
> Alcance: evidencia real sobre repo activo `C:\proyectos\lmfa`, rama `20260820`, commit base `20c0394b5a9273b63ee059d52f9c26b627a43da2`, worktree local con cambios sin commit, documentacion canonica y BD local Docker en modo lectura.
> Limite: no hubo escritura sobre produccion. La evidencia de base corresponde al entorno local disponible el 2026-08-21. No hubo acceso de lectura autenticada a produccion ni a una base productiva en esta auditoria; el contraste productivo se limita a la superficie publica sin credenciales.

---

## Fuentes usadas

- `AGENTS.md`
- `project/docs/FUENTES_CANONICAS.md`
- `project/docs/00_estado_actual.md`
- `project/docs/02_modelo_datos.md`
- `project/docs/04_arquitectura.md`
- `project/docs/06_endpoints_api.md`
- `routes/web.php`
- `routes/admin.php`
- `routes/api.php`
- controladores, requests y formularios backend/API de las entidades auditadas
- lectura de tablas locales: `knowledge_articles`, `festivales`, `interpretes`, `albunes`, `canciones`, `comidas`, `mitos`

---

## Resumen ejecutivo

La auditoria confirma dos generaciones de contrato editorial coexistiendo en el proyecto:

- Contrato moderno: `knowledge_articles` y, parcialmente, `festivales`.
- Contrato legacy-vivo: `interpretes`, `albunes`, `canciones`, `comidas` y `mitos`.

El contrato moderno explicita estado editorial, metadatos SEO, fechas editoriales, sanitizacion rica y relaciones editoriales. El contrato legacy-vivo mantiene nombres historicos (`titulo`, `album`, `cancion`, `publicar`, `estado`, `visitas`), CRUD mas plano y menor simetria entre backend y API.

---

## Matriz por entidad

| Entidad | Tabla | Backend admin | API | Estado editorial real | Relaciones editoriales | Observaciones |
|---|---|---|---|---|---|---|
| Knowledge Article | `knowledge_articles` | `backend.knowledge-articles.*` | `/api/v1/knowledge-articles` + publish/unpublish | `editorial_status` string: `draft/published/archived` | `interpretes`, `canciones`, `albums`, `festivales`, `events`, `provincias`, `relatedArticles` | Contrato mas completo y consistente del proyecto |
| Festival | `festivales` | `backend.festivales.*` | `/api/v1/festivals` | `status` string: `draft/published/archived` | backend: `news`, `events`, `interpretes`, `knowledgeArticles`; API no expone esas relaciones al crear/editar | Modernizado, pero con brecha backend/API |
| Interprete | `interpretes` | `backend.interpretes.*` | `/api/v1/artists` | `estado` boolean/int | no hay relacion editorial configurable desde formulario, pero el modelo participa en noticias, shows, discos y canciones | Biografia normalizada, contrato aun legacy |
| Album | `albunes` | `backend.discos.*` con vistas `backend.albunes.*` | `/api/v1/albums` | `estado` boolean/int, `publicar` date | canciones asociadas solo en edicion backend | Mezcla naming `discos/albunes/albums`; API plana |
| Cancion | `canciones` | `backend.canciones.*` | `/api/v1/songs` | `estado` int, `publicar` datetime | interprete; album asociado solo parcialmente y con codigo comentado | Editor de letra sin perfil editorial explicito |
| Comida | `comidas` | `backend.comidas.*` | `/api/v1/foods` | `estado` int, `publicar` datetime | sin relaciones editoriales reales | Contrato simple, legacy-vivo |
| Mito | `mitos` | `backend.mitos.*` | `/api/v1/myths` | `estado` int, `publicar` datetime | sin relaciones editoriales reales | Contrato simple, legacy-vivo |

---

## Evidencia por entidad

### 1. Knowledge Articles

- Tabla local: `knowledge_articles` con `knowledge_category_id`, `title`, `slug`, `excerpt`, `body`, `featured_image_id`, `featured_image_path`, `image_alt`, `seo_title`, `meta_description`, `primary_keyword`, `secondary_keywords`, `editorial_status`, `published_at`, `last_verified_at`, `author_id`, `reviewed_by`, `visits`, timestamps y soft delete.
- Backend:
  - rutas resource en `routes/admin.php`
  - preview propio y acciones `publish` / `unpublish`
  - formulario con categoria, estado editorial, fechas, SEO, keywords y selectores de relaciones
- Request backend: `KnowledgeArticleRequest`
  - normaliza contenido
  - valida `editorial_status` en `draft,published,archived`
  - valida arrays relacionales
- Controlador backend: `KnowledgeArticleController`
  - listado con filtros de busqueda, categoria, estado y rango de publicacion
  - usa `KnowledgeArticleService`
  - preview renderiza canonical, breadcrumbs y metadatos
- API:
  - lectura, escritura, borrado, `publish`, `unpublish`, `categories`
  - requests API aceptan categoria por id/slug/name y conservan el contrato editorial completo

Conclusion: es el contrato editorial canonico mas maduro del proyecto.

### 2. Festivales

- Tabla local: `festivales` con `mes_id`, `slug`, `title`, `excerpt`, `body`, `featured_image_id`, `featured_image_path`, `province_id`, `locality_id`, `seo_title`, `meta_description`, `status`, `published_at`, `visitas`, `user_id`, timestamps.
- Frontend publico activo:
  - `/festivales-y-fiestas-tradicionales`
  - filtros por provincia y mes
  - detalle por slug
- Backend:
  - formulario con `title`, `slug`, `excerpt`, `body`, provincia, localidad, mes, imagen, `published_at`, `status`, SEO y relaciones a noticias, eventos, artistas y articulos de enciclopedia
- Request backend: `FestivalRequest`
  - normaliza `body`
  - valida `status` en `draft,published,archived`
  - valida arrays de relaciones
- Controlador backend: `FestivalController`
  - sincroniza pivots `news_ids`, `event_ids`, `interprete_ids`, `knowledge_article_ids`
  - usa `RichTextHeadingSanitizer::normalize`
- API:
  - CRUD en `/api/v1/festivals`
  - normaliza body y slug
  - no expone ni sincroniza relaciones editoriales N:M

Conclusion: contrato modernizado y funcional, pero no totalmente simetrico entre backend y API.

### 3. Interpretes

- Tabla local: `interpretes` con `telefono`, `correo`, `facebook`, `youtube`, `twitter`, `instagram`, `interprete`, `slug`, `biografia`, `foto`, `user_id`, `visitas`, `estado`, timestamps.
- Frontend publico activo:
  - indice alfabetico de biografias
  - detalle por slug raiz `/{interprete:slug}`
  - miniportal con noticias, letras, discografia y shows asociados
- Backend:
  - formulario por componentes con campos personales/sociales, foto y `biografia`
  - `biografia` usa textarea con perfil `editorial-body`
- Request backend: `InterpreteRequest`
  - normaliza `biografia`
  - valida foto requerida en altas
- Controlador backend: `InterpreteController`
  - index con conteos de noticias, shows, discos y canciones
  - `estado` lo fuerza segun rol
- API:
  - CRUD simple en `/api/v1/artists`
  - crea/actualiza slug automaticamente
  - no expone relaciones editoriales ni workflow adicional

Conclusion: entidad central para linking editorial, pero su contrato de edicion sigue siendo legacy con capa de normalizacion moderna.

### 4. Albunes / Discos

- Tabla local: `albunes` con `interprete_id`, `publicar`, `album`, `slug`, `spotify`, `anio`, `foto`, `visitas`, `user_id`, `estado`, timestamps.
- Frontend publico activo:
  - indice `/discografias-del-folklore-argentino`
  - miniportal de artista `/discografia` y `/discografia/{slug}`
- Backend:
  - rutas admin bajo `discos`, vistas en carpeta `albunes`
  - formulario con nombre, slug, interprete, anio, foto y spotify
  - en edicion permite sincronizar canciones con orden en pivot
- Request backend: `AlbumRequest`
  - no aplica sanitizacion rich text
- Controlador backend: `AlbumController`
  - mezcla nombres `Album` / `Discos` / `albunes`
  - sincroniza canciones solo al actualizar
- API:
  - CRUD simple en `/api/v1/albums`
  - no gestiona canciones ni workflow editorial adicional

Conclusion: contrato operativo, pero con fuerte deuda de naming y asimetria entre backend y API.

### 5. Canciones

- Tabla local: `canciones` con `cancion`, `slug`, `letra`, `youtube`, `spotify`, `interprete_id`, `user_id`, `visitas`, `publicar`, `estado`, timestamps.
- Frontend publico activo:
  - indice alfabetico de letras
  - detalle por miniportal de artista
- Backend:
  - formulario con nombre, slug, interprete, youtube, spotify, letra y `publicar`
  - la letra usa `textarea id="editor"` y no evidencia el perfil `editorial-body`
  - selector de album historico esta comentado
- Request backend: `CancionRequest`
  - no evidencia sanitizacion rich text para `letra`
- Controlador backend: `CancionController`
  - listado principal por DataTables ajax
  - tiene `storeAjax` para alta rapida desde discos
- API:
  - CRUD simple en `/api/v1/songs`
  - sin manejo de album/pivot ni normalizacion editorial adicional

Conclusion: contrato funcional pero mas fragil que el de otras entidades con contenido largo.

### 6. Comidas

- Tabla local: `comidas` con `titulo`, `slug`, `receta`, `foto`, `publicar`, `user_id`, `visitas`, `estado`, timestamps.
- Frontend publico activo:
  - indice alfabetico y detalle por slug
- Backend:
  - formulario con `titulo`, `receta`, foto, slug y `publicar`
  - `receta` usa `data-ckeditor-profile="editorial-body"`
- Request backend: `ComidaRequest`
  - normaliza `receta`
- Controlador backend: `ComidaController`
  - CRUD simple con imagen y notificacion
- API:
  - CRUD simple en `/api/v1/foods`
  - normaliza contenido en requests, pero sin metadatos editoriales modernos

Conclusion: contenido largo ya normalizado, pero sigue atado al contrato legacy `estado/publicar`.

### 7. Mitos

- Tabla local: `mitos` con `titulo`, `slug`, `mito`, `foto`, `publicar`, `user_id`, `visitas`, `estado`, timestamps.
- Frontend publico activo:
  - indice alfabetico y detalle por slug
- Backend:
  - formulario con `titulo`, `mito`, foto, slug y `publicar`
  - `mito` usa `data-ckeditor-profile="editorial-body"`
- Request backend: `MitoRequest`
  - normaliza `mito`
- Controlador backend: `MitoController`
  - CRUD simple con imagen y notificacion
- API:
  - CRUD simple en `/api/v1/myths`
  - normaliza contenido en requests, pero sin workflow editorial moderno

Conclusion: situacion equivalente a `comidas`.

---

## Integraciones, jobs y automatizaciones relevantes

### Integraciones y servicios transversales detectados

- `ImageUploadService` interviene en `knowledge_articles` por servicio, y en backend de `festivales`, `interpretes`, `albunes`, `comidas` y `mitos`.
- Los modulos `festivales`, `albunes`, `canciones`, `comidas` y `mitos` disparan avisos por mail al crear contenido desde backend:
  - `FestivalCreated`
  - `AlbumCreated`
  - `CancionCreated`
  - `ComidaCreated`
  - `MitoCreated`
- No se observaron integraciones externas propias por entidad editorial dentro de estos modulos, fuera de imagenes, mail y los endpoints API ya relevados.

### Jobs y automatizacion del proyecto relacionados

- El scheduler global del proyecto ejecuta:
  - `newsletter:send-weekly`
  - `EventReminderJob` diario
  - `queue:work --stop-when-empty` cada cinco minutos
- Existen jobs de publicacion (`PublishToProviderJob`) y newsletter (`SendNewsletterJob`), pero no se detectaron jobs especificos dedicados a `knowledge_articles`, `festivales`, `interpretes`, `albunes`, `canciones`, `comidas` o `mitos`.
- Existe automatizacion MFA/orquestador en el repo (`mfa:orchestrate-backlog` y `app/Support/Automation/Mfa/*`), pero forma parte del sistema de trabajo asistido y no del contrato editorial funcional de estas entidades.

Conclusion operativa:

- hay integraciones transversales de imagen y notificacion por mail
- hay scheduler y jobs globales del proyecto
- no se evidencia un workflow asincrono propio por entidad editorial dentro del alcance auditado

---

## Diferencias documentales y contractuales detectadas

1. El proyecto convive con naming mixto canonico/legacy:
   - moderno: `knowledge_articles`, `title`, `editorial_status`, `published_at`
   - legacy-vivo: `albunes`, `cancion`, `titulo`, `publicar`, `estado`

2. `festivales` quedo a mitad de camino:
   - backend con relaciones editoriales y sanitizacion moderna
   - API sin sincronizacion de relaciones N:M

3. `canciones` es la brecha mas visible en contenido largo:
   - backend usa un editor generico `id="editor"`
   - request sin evidencia de sanitizacion rich text equivalente a `knowledge_articles`, `festivales`, `interpretes`, `comidas` y `mitos`

4. `albunes` mantiene inconsistencia de naming en tres capas:
   - tabla `albunes`
   - rutas admin `discos`
   - API `albums`

5. Las entidades legacy-vivas exponen API CRUD plana, pero no un contrato editorial comparable al de `knowledge_articles`.

---

## Anexo: auditoría focalizada de Festivales (BL-0014A)

### Evidencia de modelo y datos locales (solo lectura)

- La tabla `festivales` tiene 46 registros: 28 `published`, 18 `draft` y 0 `archived`.
- El modelo `Festival` usa el scope `publishedVisible()`: exige `status = published` y evita fechas `published_at` futuras.
- El esquema conserva los campos editoriales modernos `title`, `excerpt`, `body`, `featured_image_*`, `seo_title`, `meta_description`, `status` y `published_at`; también incorpora provincia, localidad, mes, usuario y visitas.
- Las relaciones declaradas cubren noticias, eventos, intérpretes y artículos de Enciclopedia. Esto distingue correctamente un Festival (ficha cultural/permanente) de un Evento (ocurrencia de cartelera) aunque puedan relacionarse N:M.

### Superficies, validación y SEO

- Rutas locales verificadas: CRUD administrativo completo bajo `admin/festivales`; índice público, filtros por provincia/mes, combinación provincia-mes y detalle por slug; API REST bajo `/api/v1/festivals`.
- El backend valida y sincroniza `news_ids`, `event_ids`, `interprete_ids` y `knowledge_article_ids`; normaliza el body rico y restringe el estado a `draft|published|archived`.
- La API valida y normaliza los campos editoriales y aplica por defecto `published_only=true`, pero no acepta ni sincroniza las relaciones N:M del backend.
- La superficie pública de producción respondió `200` en `/festivales-y-fiestas-tradicionales` el 2026-08-21. La prueba local `FestivalFrontendRestructureTest` pasó completa: 3 pruebas y 14 aserciones, incluyendo canonical/noindex de combinaciones débiles y presencia selectiva en sitemap.
- Rendimiento: frontend pagina de a 12 con eager loading de relaciones de tarjeta; API pagina de a 15 y el listado admin de a 25. Los selectores de relaciones del formulario se limitan a 200 registros por tipo, un límite razonable hoy que deberá revisarse si el catálogo crece.

### Brechas y riesgos priorizados

1. **P1 — asimetría backend/API:** la API no puede gestionar las cuatro relaciones editoriales que el backoffice sí sincroniza; una integración puede crear una ficha válida pero editorialmente incompleta.
2. **P2 — datos históricos:** 18 de 46 registros siguen en `draft`; antes de cualquier publicación masiva requiere una revisión editorial, no un cambio automático de estado.
3. **P2 — pruebas de contrato API:** la cobertura focalizada actual protege SEO y navegación pública; falta una prueba feature del CRUD API y de la exclusión de relaciones no soportadas.

Conclusión: el módulo es funcional y la separación Festival/Evento es explícita en modelo, rutas, relaciones y UI. No se modificaron datos, configuración, producción ni código durante esta auditoría.

---

## Anexo: auditoría focalizada de Biografías (BL-0015A)

- La tabla `interpretes` contiene 441 registros locales: 439 activos y 2 inactivos. Conserva el contrato legacy-vivo (`interprete`, `biografia`, `foto`, `estado`, `visitas`) y vínculos sociales; no posee metadatos editoriales modernos propios.
- Se verificaron CRUD administrativo bajo `admin/interpretes`, índice y filtros alfabéticos públicos y API `/api/v1/artists`. La superficie pública `/biografias-de-artistas-folkloricos` respondió `200` durante el contraste del 2026-08-21.
- El backend normaliza `biografia`, valida URLs y exige foto al crear. La API sigue siendo CRUD plano, sin relaciones editoriales, política de estado o normalización editorial equivalente visible en su controlador.
- UX/performance: los índices público y por letra usan eager loading de imágenes y `simplePaginate(24)`; el admin pagina de a 25; la API pagina de a 15. El detalle limita noticias, canciones, discos y eventos relacionados, y cachea la biografía autoenlazada por una hora.
- Brechas priorizadas: **P1** agregar contrato API equivalente para estado, relaciones y normalización; **P2** incorporar metadata SEO/fuentes editoriales en el modelo o una capa asociada; **P2** agregar pruebas feature específicas del CRUD API y de la visibilidad público/inactiva.

Conclusión: Biografías es un módulo público activo y escalable en sus listados, pero su contrato de autoría permanece más legacy que Festival y Enciclopedia. La auditoría no modificó datos ni código.

---

## Ruta, rama y commit auditados

- Ruta: `C:\proyectos\lmfa`
- Rama observada: `20260820`
- Commit base observado: `20c0394b5a9273b63ee059d52f9c26b627a43da2`
- Nota de reproducibilidad: el worktree local auditado no estaba limpio al momento de la revision, por lo que el commit base no describe por si solo todo el estado inspeccionado.

## Contraste de superficie publica

El 2026-08-21 a las 09:27 AR se verificaron, mediante solicitudes HTTP `GET` de solo lectura y sin credenciales, las rutas publicas correspondientes a las siete familias auditadas. Todas respondieron `200`:

- `/enciclopedia`
- `/festivales-y-fiestas-tradicionales`
- `/biografias-de-artistas-folkloricos`
- `/recetas-de-comidas-tipicas-argentinas`
- `/mitos-y-leyendas-argentinas`
- `/letras-de-canciones-folkloricas`
- `/discografias-del-folklore-argentino`

Esta comprobacion confirma que las superficies publicas auditadas estan accesibles y se corresponden con las rutas locales relevadas. No sustituye una lectura autenticada de la API ni de la base de produccion, que no era necesaria para esta definicion de terminado y permanecen fuera del alcance seguro de este lote.

---

## Estado de cierre de la auditoria

La auditoria deja evidencia real y util de:

- repositorio activo
- rutas web/admin/api
- formularios y requests backend
- controladores backend/api
- estructura efectiva de tablas locales en lectura
- integraciones transversales, scheduler y jobs relevantes
- diferencias documentales y contractuales

La definicion de terminado de `BL-0013A` queda satisfecha: las siete familias fueron comparadas contra codigo, BD local en lectura y documentacion; las brechas se priorizaron sin modificar datos; y se agrego el contraste reproducible de sus superficies publicas.

No se realizaron commits, despliegues ni escrituras sobre produccion.

---

## Anexo: estado real de Mitos y Leyendas (BL-0017A)

> Auditoría: 2026-08-22 21:03–21:06 AR. Alcance de sólo lectura sobre el repositorio activo, la base local y la superficie pública; sin cambios de datos, producción, despliegue ni commit.

### Resultado

El módulo existe y contiene contenido operativo, pero no constituye todavía un modelo cultural de mitos y leyendas. La decisión de alcance para la siguiente etapa es **adaptar el módulo `Mito` existente**, conservando sus URL públicas y sus 284 entradas, y evolucionar el contrato de forma trazable. No corresponde crear un segundo módulo ni consolidar datos en otra entidad antes de definir el modelo cultural de `BL-0017B`.

### Evidencia verificada

- **Modelo y contenido local:** `mitos` tiene 284 filas, todas con `estado = 1`; su esquema se limita a `titulo`, `slug`, cuerpo `mito`, `foto`, `publicar`, `user_id`, `visitas` y timestamps. No hay campos ni pivots para región, comunidad, variantes, fuentes, evidencia, advertencias o relaciones culturales. Los 284 cuerpos locales no contienen `<h1>`; 284 filas tienen autor y ninguna utiliza la columna legacy `foto`.
- **Código y rutas:** la web mantiene índice, filtro alfabético y detalle bajo `/mitos-y-leyendas-argentinas`; el backend tiene CRUD protegido por `MitoPolicy`; la API CRUD queda en `/api/v1/myths`. El formulario y los requests web/API normalizan el cuerpo rico con el perfil `editorial-body`; la API sigue siendo plana y no expresa workflow editorial, taxonomías ni relaciones.
- **UX y rendimiento local:** índice limita los bloques a seis ítems con eager loading de imágenes; los listados por letra usan `simplePaginate(12)`. El detalle consulta relacionados por inicial y no tiene una taxonomía que permita navegación por región, comunidad o variante.
- **Validación local:** `docker exec lmfa-app-1 sh -c "cd /var/www/html && php artisan test tests/Feature/Mitos/MitosFrontendTest.php"` pasó: 1 prueba, 5 aserciones. Cubre que la portada local renderice sólo mitos publicados.
- **Contraste público independiente:** una lectura sin credenciales de `https://mifolkloreargentino.com/mitos-y-leyendas-argentinas` devolvió HTTP 500 el 2026-08-22. Esto contradice el contraste 200 del 2026-08-21 y la corrección local OpenSpec `fix-mitos-home-500`; indica una brecha de despliegue/configuración que no se modifica dentro de esta auditoría.

### Brechas y siguiente paso

1. **P1 — disponibilidad pública:** verificar y desplegar de forma autorizada la corrección de la portada que ya está validada localmente; hasta entonces la URL indexable permanece en error 500.
2. **P1 — modelo cultural:** `BL-0017B` debe especificar, con validación humana en sus gates, los conceptos de relato, variante, región/comunidad, fuentes/evidencia, advertencias y relaciones. No se debe inventar ni migrar estructura antes de esa especificación.
3. **P2 — contrato editorial:** una vez aprobado el modelo, alinear backend y API, preservando el criterio transversal de que el título de página es el único H1 y los cuerpos no contienen `<h1>`.

### Revisión independiente contra la definición de terminado

**Aprobada.** Se revisaron código, tabla, rutas, API, taxonomía inexistente, contenido local y superficie pública. La decisión queda documentada entre las alternativas disponibles: adaptar el módulo existente. La incidencia pública se registra explícitamente como trabajo separado y no se oculta como cierre funcional.

---

## Anexo: Letras, Canciones, Obras y Discos (BL-0018A)

> Auditoría: 2026-08-22 21:07–21:09 AR. Lectura de repositorio, BD local y superficies públicas; no se copiaron ni publicaron letras, ni se modificaron datos, producción, despliegues o commits.

### Mapa real

- **Canciones / letras:** `canciones` y `Cancion` son la entidad operativa. Tiene `cancion`, `letra`, intérprete, enlaces opcionales de YouTube/Spotify, fecha, estado y visitas. El índice público, el filtro alfabético y el detalle por artista viven bajo `/letras-de-canciones-folkloricas` y los miniportales del artista. No existe entidad `Obra` distinta: el concepto queda absorbido ambiguamente por la canción y su atribución a un intérprete.
- **Discos:** `albunes` y `Album` son la entidad operativa. Mantienen álbum, año, intérprete, foto, Spotify, estado y visitas. La relación N:M con canciones usa el pivot legacy-vivo `albunes_canciones` con orden; las rutas públicas son `/discografias-del-folklore-argentino` y los miniportales de artista. Los nombres `albunes` (tabla), `discos` (backend/ruta) y `albums` (API) son intencionalmente heterogéneos y no deben renombrarse sin una migración aprobada.
- **Relaciones y legado:** hay 685 enlaces álbum-canción que cubren 67 discos y 615 canciones. `knowledge_article_cancion` y `knowledge_article_album` no tienen enlaces locales, por lo que el interlinking de Enciclopedia hacia música todavía no se materializa en datos.

### Evidencia cuantitativa y de superficie

- Base local: 4.611 canciones, 4.608 activas, asociadas a 184 intérpretes; 411 discos, 397 activos, asociados a 100 intérpretes. Hay 10 letras placeholder `No disponible aún`, cinco pares título-intérprete duplicados y 100 canciones sin fecha de publicación.
- Tráfico local acumulado: 9.765.811 visitas de canciones (máximo individual 32.692) y 477.320 de discos (máximo individual 3.645). Las métricas validan una superficie relevante, pero no sustituyen analítica de producción.
- Rendimiento: canción indexa con `simplePaginate(36)` y eager loading de intérprete/álbumes; discos con `simplePaginate(24)` y eager loading de intérprete/imágenes. El backend de canciones usa DataTables server-side; el de discos carga la colección completa, una deuda a revisar cuando el catálogo crezca.
- Superficie pública: los índices de letras y discografías son accesibles en los últimos rastreos disponibles y exponen navegación, paginación y enlaces hacia artistas. Los sitemaps específicos de letras y discografías están declarados en rutas web.

### Fuentes, derechos y decisión de alcance

El modelo no contiene campos de autoría de obra, compositor, versión, editorial, fuente, licencia, dominio público, autorización ni fecha de verificación. A la vez, el catálogo expone letras completas mediante `letra`. Por ello, cualquier alta o enriquecimiento de letras queda sujeto a una **decisión humana explícita de derechos y política de publicación**; no corresponde automatizar, copiar ni ampliar letras en esta etapa.

La decisión documental es conservar el modelo vigente como fuente operativa, y preparar una especificación posterior que separe canción, obra, versión/grabación y créditos sólo después de resolver el gate de derechos. No se crean tablas, no se consolidan duplicados y no se alteran URLs en esta auditoría.

### Brechas priorizadas y revisión independiente

1. **P1 — derechos y procedencia:** definir política, evidencia y controles antes de nuevas letras completas o automatizaciones.
2. **P1 — modelo conceptual:** distinguir obra, canción/versión, grabación, autores y créditos mediante spec aprobada; hoy `Cancion` no puede expresarlo.
3. **P2 — relaciones y calidad:** completar links con Enciclopedia, revisar placeholders, fechas faltantes y los cinco duplicados sólo después de la política anterior.
4. **P2 — mantenibilidad:** preservar nombres físicos legacy y paginación pública; evaluar paginación del índice admin de discos con evidencia de escala.

**Revisión independiente: aprobada.** La definición de terminado se cumple: modelos vigentes/legacy, duplicados conceptuales, fuentes/derechos, rutas, plantillas, relaciones y señales de tráfico fueron identificados sin reproducir letras ni modificar contenido.
