## Why

Los listados del backend hoy son inconsistentes: algunos ya paginan pero no permiten buscar ni ordenar por lo que muestran en tabla, otros solo tienen filtros parciales y varios todavia cargan demasiados registros sin herramientas de exploracion. Esto ralentiza la operacion editorial y vuelve mas costoso encontrar o auditar contenido en administracion.

## What Changes

- Incorporar busqueda por texto y ordenamiento explicito en los listados backend paginados, cubriendo como minimo las columnas visibles o sus equivalentes editoriales.
- Definir criterios de orden por modulo segun el dominio real de cada tabla en lugar de imponer una grilla generica unica.
- Normalizar el transporte de parametros (`search`, `sort`, `direction`, filtros auxiliares) para que la paginacion preserve el estado actual.
- Identificar los listados que primero deben migrar a server-side/paginacion real antes de recibir buscador y orden consistente.

## Capabilities

### New Capabilities
- `backend-admin-listing-discovery`: Garantiza que los listados administrativos permitan buscar y ordenar registros segun las columnas relevantes visibles para cada modulo, manteniendo rendimiento y trazabilidad del estado de filtros.

### Modified Capabilities

## Impact

- Codigo afectado: controladores y vistas de listados en `app/Http/Controllers/Backend`, `app/Http/Controllers/Pasarela`, `resources/views/backend` y `resources/views/pasarela`
- Modulos candidatos: `news`, `events`, `festivales`, `interpretes`, `users`, `newsletter`, `knowledge_articles`, `templates`, `publication_requests`, `notifications`, y listados legacy como `albunes` o `classifieds`
- Riesgo tecnico: cambios de queries, joins/`whereHas`, paginacion server-side y whitelists de orden para evitar regresiones o SQL inseguro
