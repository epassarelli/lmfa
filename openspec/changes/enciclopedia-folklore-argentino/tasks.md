# Tareas: enciclopedia-folklore-argentino

## Estado general

Implementado y validado en entorno local Docker al 2026-08-04.

## Frente 1: Relevamiento final y decisiones de integracion

- [x] Confirmar modelos, tablas y PK reales para `Interprete`, `Cancion`, `Album`, `Festival`, `Event`, `Provincia` y `Region` o equivalentes.
- [x] Confirmar convenciones actuales de imagen destacada, media y slugs en modulos comparables.
- [x] Confirmar que `/enciclopedia` no colisiona con rutas publicas, slugs reservados o vistas legacy existentes.
- [x] Confirmar la taxonomia inicial de nueve familias: `ritmos`, `danzas`, `instrumentos`, `regiones`, `provincias`, `historia`, `tradiciones`, `cancionero`, `aprender`.
- [x] Confirmar estrategia de unicidad de slug para articulos evergreen.
- [x] Listar archivos exactos a tocar antes de comenzar implementacion.

## Frente 2: Backend base y persistencia

- [x] Diseñar migraciones para `knowledge_categories`, `knowledge_articles` y pivotes necesarios.
- [x] Definir indices, foreign keys y estrategia de borrado coherentes con el proyecto.
- [x] Crear modelos Eloquent y relaciones principales.
- [x] Crear factories si ayudan a tests del modulo.
- [x] Crear seeder idempotente para familias iniciales.
- [x] Validar que el cambio no edita migraciones historicas ni duplica catalogos existentes.

## Frente 3: Dominio, permisos y API

- [x] Implementar `KnowledgeArticleService` o servicio equivalente para alta, edicion, publicacion y relaciones.
- [x] Crear `FormRequest` backend y API con reglas coherentes.
- [x] Crear `KnowledgeArticlePolicy` y registrar permisos segun el patron actual.
- [x] Crear controladores API para listado, detalle, alta, actualizacion, baja, publicacion y despublicacion.
- [x] Incorporar filtros, busqueda y paginacion al listado API.
- [x] Documentar payloads y respuestas JSON del modulo.

## Frente 4: ABM administrativo

- [x] Relevar patrones reales de vistas backend del proyecto para replicar estilo y componentes.
- [x] Crear listado paginado con filtros por familia, estado y fecha.
- [x] Crear formulario de alta y edicion con slug editable, SEO, fechas y relaciones.
- [x] Crear vista previa de articulo.
- [x] Crear acciones de publicar, despublicar y archivar o eliminar segun politica vigente.
- [x] Validar permisos del panel con usuarios admin y no admin.

## Frente 5: Frontend publico y SEO

- [x] Crear portada `/enciclopedia`.
- [x] Crear indice por familia en `/enciclopedia/{categorySlug}`.
- [x] Crear ficha publica en `/enciclopedia/{categorySlug}/{articleSlug}`.
- [x] Incorporar breadcrumbs, canonical, title y meta description con fallbacks.
- [x] Incorporar Open Graph y JSON-LD solo para fichas individuales publicadas.
- [x] Integrar el modulo al sitemap existente respetando visibilidad editorial.
- [x] Validar que no se rompen rutas publicas existentes ni slugs SEO ya posicionados.
- [x] Documentar expresamente que no hacen falta redirecciones desde `/folklore-argentino/...` porque nunca se publico esa variante.

## Frente 6: Relaciones y bloques editoriales

- [x] Implementar sincronizacion de relaciones con entidades reales existentes.
- [x] Mostrar bloques de relacionados solo cuando existan elementos asociados.
- [x] Asegurar que los enlaces internos sean HTML rastreables y con anclajes descriptivos.
- [x] Definir manejo seguro para relaciones aun no implementables por ausencia de entidad estructurada.

## Frente 7: Testing, validacion y cierre

- [x] Crear tests feature para migraciones, modelo, relaciones y seeder idempotente.
- [x] Crear tests feature para permisos y CRUD admin o API segun cobertura disponible.
- [x] Crear tests para visibilidad publica de borradores y publicaciones validas.
- [x] Crear tests para filtros, paginacion, publicacion, despublicacion y slugs duplicados.
- [x] Ejecutar `php artisan migrate:status` antes de cualquier intento de migracion y esperar confirmacion humana.
- [x] Ejecutar tests focalizados del modulo y luego una suite razonable del proyecto.
- [x] Ejecutar el formateador configurado, probablemente `./vendor/bin/pint`.
- [x] Actualizar `project/docs/00_estado_actual.md` si el modulo queda implementado.
- [x] Preparar informe final con rutas, tablas, relaciones, pruebas, riesgos y pasos de validacion local.
