# Cambio: enciclopedia-folklore-argentino

## Objetivo

Incorporar una nueva seccion editorial evergreen llamada `Enciclopedia del folklore argentino` dentro de Mi Folklore Argentino, con modelo de datos propio, taxonomia navegable, ABM administrativo integrado, API autenticada para gestion editorial y frontend publico indexable orientado a SEO bajo la ruta canonica base `/enciclopedia`.

## Problema actual

El portal cuenta con noticias, eventos, artistas, canciones, discos, festivales, mitos y comidas, pero no tiene un silo evergreen unificado para contenidos de referencia que:

- organice conocimiento por familias tematicas
- relacione articulos con entidades ya existentes del portal
- permita navegacion tematica interna por taxonomia
- sostenga actualizacion editorial con verificacion factual
- exponga una API estable para ingestiones y automatizaciones editoriales

Sin una spec concreta, el cambio correria el riesgo de mezclar contenido evergreen con `News`, duplicar catalogos existentes, romper convenciones de URLs o implementar admin y API con patrones distintos a los modulos actuales.

## Alcance incluido

- Crear una nueva propuesta OpenSpec para el silo `Enciclopedia del folklore argentino`.
- Definir una entidad editorial propia para articulos evergreen, separada conceptualmente de `News`.
- Definir taxonomia navegable con familias iniciales y soporte para indices y fichas publicas bajo `/enciclopedia`.
- Reutilizar entidades reales existentes del proyecto para relaciones editoriales.
- Integrar CRUD backend en el panel AdminLTE existente.
- Integrar API REST autenticada bajo `/api/v1` siguiendo los patrones del proyecto.
- Integrar frontend publico con portada, indices por familia y fichas individuales.
- Integrar SEO basico, canonical, breadcrumbs y sitemap usando la infraestructura existente.
- Incluir validaciones, policies, tests y documentacion minima para implementar luego con bajo riesgo.

## Fuera de alcance

- Ejecutar implementacion sin aprobacion explicita de esta spec.
- Modificar rutas publicas ya existentes del portal fuera del nuevo silo.
- Duplicar modelos o catalogos existentes como `Interprete`, `Event`, `Festival`, `Provincia` o `Region`.
- Incorporar un nuevo editor WYSIWYG si el proyecto ya usa uno diferente.
- Inventar redireccionador historico de slugs si el repositorio no tiene infraestructura existente para eso.
- Ejecutar migraciones, seeders o SQL modificatorio sin autorizacion explicita del usuario.
- Crear integraciones externas nuevas para publicacion o sindicacion.

## Archivos afectados

- `openspec/changes/enciclopedia-folklore-argentino/proposal.md`
- `openspec/changes/enciclopedia-folklore-argentino/design.md`
- `openspec/changes/enciclopedia-folklore-argentino/tasks.md`
- `routes/web.php`
- `routes/admin.php`
- `routes/api.php`
- `app/Models/*` del nuevo modulo evergreen
- `app/Http/Controllers/Backend/*` del modulo
- `app/Http/Controllers/Frontend/*` del modulo
- `app/Http/Controllers/Api/*` del modulo
- `app/Http/Requests/*` y `app/Http/Requests/Api/*` del modulo
- `app/Policies/*` del modulo
- `app/Services/*` del modulo
- `database/migrations/*` del modulo
- `database/seeders/*` del modulo
- `resources/views/backend/*` del modulo
- `resources/views/frontend/*` del modulo
- `tests/Feature/*` del modulo
- `project/docs/00_estado_actual.md` si el cambio queda implementado

## Reglas funcionales

### Caso 1
Dado un visitante anonimo
Cuando ingresa a `/enciclopedia`
Entonces debe ver la portada de la enciclopedia con acceso a familias activas y contenidos destacados publicados.

### Caso 2
Dada una familia activa de la taxonomia
Cuando un visitante ingresa a `/enciclopedia/{familia}`
Entonces debe ver un indice SEO indexable con descripcion, breadcrumbs y listado paginado de articulos publicados de esa familia.

### Caso 3
Dado un articulo evergreen publicado
Cuando un visitante ingresa a `/enciclopedia/{familia}/{slug}`
Entonces debe ver una ficha publica con canonical, metadatos, contenido, fecha de publicacion, ultima verificacion y bloques de relacionados solo si existen.

### Caso 4
Dado un administrador autenticado
Cuando crea o edita un articulo desde el panel
Entonces debe poder gestionar taxonomia, slug, extracto, cuerpo, imagen destacada, SEO, estado, fechas y relaciones con entidades existentes.

### Caso 5
Dado un token Sanctum valido con rol `administrador`
Cuando consume la API del modulo
Entonces debe poder listar, crear, consultar, actualizar, publicar, despublicar y eliminar o archivar articulos evergreen respetando validaciones, permisos y codigos HTTP coherentes.

### Caso 6
Dado un articulo en borrador, archivado o con publicacion futura
Cuando se genera el frontend publico o sitemap
Entonces ese contenido no debe ser visible ni indexable.

## Reglas tecnicas

- No implementar hasta que esta spec quede aprobada por el usuario.
- La entidad evergreen debe ser propia del modulo y no debe reutilizar la tabla `news`.
- Las relaciones con artistas, canciones, discos, festivales, eventos, provincias y regiones deben apuntar a modelos reales ya existentes del proyecto.
- El panel admin debe reutilizar AdminLTE 3, middleware `auth`, convenciones `backend.*` y policies reales del proyecto.
- El frontend publico debe seguir Blade y las convenciones reales del portal, evitando introducir nuevos stacks.
- La API debe vivir bajo `/api/v1`, protegida con `auth:sanctum`, y las rutas de escritura deben quedar restringidas a `role:administrador` como el resto de la API actual.
- Las validaciones de panel y API deben ser coherentes y preferentemente reutilizar reglas comunes.
- No editar migraciones historicas ya aplicadas.
- No ejecutar `php artisan migrate` sin antes mostrar `migrate:status` y esperar confirmacion del usuario.
- Los tests nuevos deben usar `DatabaseTransactions` y no `RefreshDatabase`.

## Validacion

- Verificar que la propuesta separa contenido evergreen de `News`.
- Verificar que las relaciones propuestas reutilizan entidades reales del repositorio.
- Verificar que las rutas nuevas no pisan URLs publicas existentes.
- Verificar que la ruta base `/enciclopedia` no colisiona con ninguna ruta publica actual.
- Verificar que el flujo cubre backend, API, frontend, SEO, sitemap y taxonomia.
- Verificar que la implementacion posterior pueda realizarse con cambios pequenos y revisables.

## Riesgos

- La relacion exacta con provincias y regiones depende de confirmar los modelos y tablas efectivamente usados en el codigo y BD local.
- El proyecto no parece tener hoy un patron unificado de API Resources para todos los modulos, por lo que habra que definir una salida consistente sin romper contratos existentes.
- La estrategia de slugs historicos puede requerir una tabla adicional o reutilizar infraestructura existente; si no existe hoy, debe acotarse el MVP para no sobredisenar.
- La base `/folklore-argentino` fue descartada antes de implementarse; por lo tanto no se requieren redirecciones desde esa propuesta previa, pero conviene preservar esta decision en la documentacion para evitar ambiguedades posteriores.
- El alcance combina backend, admin, API, frontend y SEO; debe implementarse por etapas para mantener revisabilidad.

## Criterio de aceptacion

El cambio se considera listo para implementar cuando la spec aprobada deja definidos el modelo evergreen, la taxonomia inicial, las superficies web y API, la estrategia de relaciones, la integracion admin/frontend, las restricciones de stack y el plan de tareas trazable hasta validacion final.
