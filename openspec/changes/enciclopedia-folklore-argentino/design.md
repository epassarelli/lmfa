# Diseno tecnico: enciclopedia-folklore-argentino

## Resumen

La Enciclopedia del folklore argentino se implementara como un modulo editorial propio con dos piezas centrales: una taxonomia navegable y una entidad de articulo evergreen. El backend sera la fuente de verdad del modelo, mientras que admin, API y frontend consumiran ese modelo a traves de Eloquent, controllers especificos, requests, policies y servicios de dominio livianos. El objetivo es seguir los patrones ya observados en `News` y `Event`, sin mezclar contenido de referencia con contenido coyuntural. La ruta publica canonica base del silo sera `/enciclopedia`.

## Decisiones clave

### 1. Entidad editorial separada de News

Se define una entidad propia para contenido evergreen, preferentemente `KnowledgeArticle`.

Motivos:

- `News` ya representa noticias y contenido coyuntural
- el nuevo silo necesita `last_verified_at`, taxonomia propia y navegacion tematica
- separar modelos evita contaminar los flujos actuales de noticias, moderacion y newsletter

La implementacion final puede ajustar el nombre si el relevamiento detallado descubre una convencion mas coherente, pero no debe reutilizar `News`.

### 2. Taxonomia propia con familia navegable

Se define una tabla de categorias del modulo, preferentemente `knowledge_categories`, para modelar:

- familia o seccion publica
- nombre visible
- slug
- descripcion
- orden
- estado activo
- SEO del indice

La URL publica se resuelve desde esa taxonomia y el slug del articulo, sin hardcodear rutas por familia.

Motivacion de la base `/enciclopedia`:

- evita redundancia con el dominio `mifolkloreargentino.com`
- comunica un sistema permanente de conocimiento
- funciona de manera natural para ritmos, historia, regiones, canciones y aprendizaje
- deja que cada URL especifica cargue la palabra clave principal sin repetir la tematica del dominio

### 3. Relaciones reutilizando entidades reales

Los articulos evergreen deben vincularse con modelos existentes del proyecto. El diseno asume, sujeto a validacion fina durante implementacion:

- `Interprete`
- `Cancion`
- `Album`
- `Festival`
- `Event`
- `Provincia`
- `Region` o equivalente real

Estrategia:

- relaciones many-to-many mediante tablas pivote especificas por entidad
- no crear duplicados de catalogos existentes
- si alguna entidad no existe de forma estructurada, dejar el punto de extension documentado en lugar de inventar una tabla incompatible

### 4. Estados editoriales alineados con el proyecto

El modulo debe reutilizar el lenguaje editorial ya presente en `News` y `Event`.

Estados minimos esperados:

- `draft`
- `published`
- `archived`

Reglas:

- crear no implica publicar automaticamente
- `published` exige `published_at`
- `draft`, `archived` y publicaciones futuras no se exponen publicamente
- el flujo de publicacion y despublicacion debe poder invocarse desde admin y API

### 5. Backend con patrones ya existentes

La implementacion debe seguir la organizacion ya observada:

- `routes/admin.php` para CRUD admin bajo `/admin`
- `routes/api.php` para API versionada bajo `/api/v1`
- `routes/web.php` para frontend publico
- `FormRequest` para validaciones
- `Policy` para autorizacion
- servicio de dominio solo donde simplifique logica compartida

No se debe introducir un mini-framework nuevo dentro del modulo.

## Modelo de datos propuesto

### knowledge_categories

Taxonomia del silo evergreen.

Campos:

- `id`
- `parent_id` nullable
- `name`
- `slug`
- `description` nullable
- `sort_order` default `0`
- `is_active` boolean default `true`
- `seo_title` nullable
- `meta_description` nullable
- timestamps

Indices:

- unique `slug`
- index `parent_id`
- index `is_active`
- index `sort_order`

Seeder inicial idempotente:

- `ritmos`
- `danzas`
- `instrumentos`
- `regiones`
- `provincias`
- `historia`
- `tradiciones`
- `cancionero`
- `aprender`

Nota:

La propuesta consolidada usa nueve familias publicas de primer nivel para reflejar las URLs objetivo confirmadas:

- `ritmos`
- `danzas`
- `instrumentos`
- `regiones`
- `provincias`
- `historia`
- `tradiciones`
- `cancionero`
- `aprender`

`provincias` queda como familia publica propia y no como subdivision tecnica de `regiones`, porque eso se alinea mejor con la navegacion y con URLs como `/enciclopedia/provincias/santiago-del-estero`.

### knowledge_articles

Entidad principal del modulo.

Campos:

- `id`
- `knowledge_category_id`
- `title`
- `slug`
- `excerpt` nullable
- `body`
- `featured_image_id` nullable
- `featured_image_path` nullable
- `image_alt` nullable
- `seo_title` nullable
- `meta_description` nullable
- `primary_keyword` nullable
- `secondary_keywords` nullable
- `editorial_status` default `draft`
- `published_at` nullable
- `last_verified_at` nullable
- `author_id` nullable
- `reviewed_by` nullable
- timestamps
- `deleted_at` nullable si se adopta `SoftDeletes`

Indices:

- composite unique sugerido sobre `knowledge_category_id` + `slug` si la URL admite repetir slugs entre familias
- index `editorial_status`
- index `published_at`
- index `last_verified_at`
- index `author_id`

Decision de slug:

- se recomienda unicidad por categoria para permitir URLs resueltas por familia + slug
- si el proyecto privilegia slugs globalmente unicos, esa decision debe confirmarse antes de implementar porque cambia validaciones y potenciales redirecciones

### Tablas pivote sugeridas

Minimo previsto:

- `knowledge_article_interprete`
- `knowledge_article_cancion`
- `knowledge_article_album`
- `knowledge_article_festival`
- `event_knowledge_article` o nombre coherente con convencion real
- `knowledge_article_provincia`
- `knowledge_article_region`
- `knowledge_article_related`

Para relaciones entre articulos evergreen:

- una tabla pivote simetrica o una tabla dirigida `knowledge_article_related` con `article_id` y `related_article_id`
- la implementacion debe evitar duplicados y autorrelaciones

## Servicios

### KnowledgeArticleService

Responsabilidad:

- crear y actualizar articulos evergreen centralizando reglas compartidas entre panel y API
- resolver slug por defecto
- sincronizar relaciones many-to-many
- gestionar publicacion y despublicacion
- integrar imagen destacada con la infraestructura de media existente si aplica

Estrategia:

- inspirarse en `NewsService` y `EventService`
- evitar logica excesiva en controllers
- usar transacciones en operaciones compuestas

### KnowledgeCategoryResolver o logica equivalente

Responsabilidad:

- resolver la categoria activa a partir del slug de URL
- encapsular criterios de visibilidad publica

Puede implementarse como servicio dedicado o mediante query scopes del modelo, segun simplicidad real del codigo.

## Superficie HTTP

### Frontend publico

Rutas previstas:

- `GET /enciclopedia`
- `GET /enciclopedia/{categorySlug}`
- `GET /enciclopedia/{categorySlug}/{articleSlug}`

Reglas:

- solo categorias activas
- solo articulos `published` con `published_at <= now()`
- 404 para borradores, archivados, futuras publicaciones y categorias inactivas
- breadcrumbs visibles y canonical absoluto
- no se requieren redirecciones desde `/folklore-argentino/...` porque esa variante no llego a publicarse

### Admin

Rutas previstas:

- `Route::resource('knowledge-articles', ...)` bajo `/admin`
- `GET /admin/knowledge-articles/{article}/preview`
- `POST /admin/knowledge-articles/{article}/publish`
- `POST /admin/knowledge-articles/{article}/unpublish`
- `Route::resource('knowledge-categories', ...)` si el ABM de taxonomia se incluye en esta etapa

Nombres sugeridos:

- `backend.knowledge-articles.*`
- `backend.knowledge-categories.*`

Si el proyecto ya centraliza taxonomias simples sin CRUD completo, el ABM de categorias puede limitarse a lectura y asignacion inicial por seeder en el MVP.

### API

Rutas previstas:

- `GET /api/v1/knowledge-articles`
- `POST /api/v1/knowledge-articles`
- `GET /api/v1/knowledge-articles/{article}`
- `PUT /api/v1/knowledge-articles/{article}`
- `PATCH /api/v1/knowledge-articles/{article}`
- `DELETE /api/v1/knowledge-articles/{article}`
- `POST /api/v1/knowledge-articles/{article}/publish`
- `POST /api/v1/knowledge-articles/{article}/unpublish`
- `GET /api/v1/knowledge-categories`

Reglas:

- grupo protegido por `auth:sanctum`
- escritura restringida a `role:administrador`
- respuestas JSON estables
- filtros, busqueda y paginacion en listados
- sin escritura publica anonima

## SEO y sitemap

### Portada e indices

- no deben usar schema `Article` generico
- deben tener `title`, `meta_description`, canonical y breadcrumbs
- pueden usar texto editorial introductorio tomado de la categoria

### Fichas individuales

- pueden usar `Article` o `BlogPosting` si los datos visibles lo respaldan
- mostrar `published_at` y `last_verified_at` cuando existan
- Open Graph y Twitter segun el sistema actual del portal

### Sitemap

La integracion debe extender el mecanismo actual en `SitemapController`:

- incluir solo articulos `published`
- excluir borradores, archivados y futuras publicaciones
- incluir portada e indices solo si la estrategia actual del sitemap admite paginas de seccion

## UI y experiencia editorial

### Backend

Pantallas minimas:

- listado con filtros por familia, estado y fecha
- alta
- edicion
- vista previa
- accion de publicar y despublicar
- baja o archivado segun politica real del proyecto

Formulario minimo:

- familia
- titulo
- slug editable con autogeneracion
- bajada
- cuerpo
- imagen destacada
- alt
- SEO
- estado
- publicacion
- ultima verificacion
- relacionados

### Frontend

Portada:

- intro editorial del silo
- H1 visible `Enciclopedia del folklore argentino`
- accesos a familias
- listado de articulos destacados o recientes publicados

Indice de familia:

- encabezado con descripcion
- listado paginado
- breadcrumbs

Ficha:

- H1
- imagen destacada
- contenido
- metadatos editoriales
- bloques de relacionados condicionales

## Estrategia de testing

Minimo esperado:

- migraciones y modelo
- relaciones principales
- permisos de admin
- CRUD API
- validaciones de slug y campos requeridos
- no visibilidad publica de borradores
- visibilidad publica de publicados
- filtros y paginacion
- publicar y despublicar
- sitemap filtrando solo publicaciones validas
- idempotencia del seeder de taxonomias

Restricciones:

- usar `DatabaseTransactions`
- no usar `RefreshDatabase`
- no afirmar migraciones ejecutadas si no se conto con autorizacion

## Orden de implementacion

1. Relevamiento detallado final de modelos reutilizables y permisos
2. Migraciones, modelos, factories y seeder de categorias
3. Servicio, requests, policies y API
4. ABM administrativo
5. Frontend publico y SEO
6. Relaciones y bloques de relacionados
7. Tests, documentacion y cierre

## Archivos previstos por frente

### Backend base

- `database/migrations/*knowledge*`
- `database/seeders/*Knowledge*`
- `app/Models/KnowledgeArticle.php`
- `app/Models/KnowledgeCategory.php`
- pivotes y factories si se usan
- `app/Services/KnowledgeArticleService.php`
- `app/Policies/KnowledgeArticlePolicy.php`

### API

- `app/Http/Controllers/Api/KnowledgeArticleController.php`
- `app/Http/Requests/Api/StoreKnowledgeArticleRequest.php`
- `app/Http/Requests/Api/UpdateKnowledgeArticleRequest.php`
- resources JSON si se incorporan
- `routes/api.php`

### Admin

- `app/Http/Controllers/Backend/KnowledgeArticleController.php`
- requests backend del modulo
- `resources/views/backend/knowledge-articles/*`
- `routes/admin.php`

### Frontend

- `app/Http/Controllers/Frontend/KnowledgeController.php` o equivalente
- `resources/views/frontend/knowledge/*`
- `routes/web.php`
- `app/Http/Controllers/Frontend/SitemapController.php`

## Riesgos abiertos

- Confirmar modelos y tablas reales para provincias y regiones antes de cerrar pivotes.
- Confirmar si el sistema actual de media debe vincular por `featured_image_id`, `featured_image_path` o ambos.
- Confirmar si las categorias del modulo necesitan CRUD completo o si alcanza con seeder + asignacion editorial.
- Confirmar estrategia de unicidad de slug por categoria versus slug global.
- Confirmar que `/enciclopedia` no entra en conflicto con una pagina estatica, vista legacy o slug reservado ya existente en `routes/web.php`.
