## Context

El backend mezcla tres patrones distintos:

1. Listados server-side ya paginados con orden fijo y sin buscador (`news`, `events`, `festivales`, `interpretes`, `users`).
2. Listados con filtros parciales pero sin esquema uniforme de orden (`knowledge_articles`, `newsletter`, parte de pasarela).
3. Listados legacy que todavia usan `get()` o DataTables custom y necesitan una capa de server-side consistente antes de sumar orden/busqueda (`albunes`, `classifieds`, `contributions`, algunos CRUD heredados).

El usuario pidio que todos los listados del backend tengan buscador y posibilidad de ordenar "segun el caso", por lo que la solucion debe contemplar un contrato comun pero con columnas permitidas por modulo.

## Goals

- Agregar buscador por texto sobre, al menos, los campos visibles o equivalentes de cada tabla.
- Agregar ordenamiento por criterios permitidos y contextualizados por modulo.
- Mantener paginacion con `withQueryString()` y evitar N+1 o `ORDER BY` sobre columnas no indexadas sin justificar.
- Dejar un camino incremental para convertir listados legacy a server-side antes de expandir filtros.

## Non-Goals

- No reemplazar todo el backend por DataTables JS ni una solucion SPA.
- No agregar orden sobre cualquier columna arbitraria enviada por request.
- No mezclar esta iniciativa con cambios de formulario, permisos o SEO.

## Approach

1. Introducir un patron comun por controlador para resolver:
   - `search`: texto libre saneado
   - `sort`: clave logica permitida por modulo
   - `direction`: `asc|desc`
   - filtros existentes del modulo
2. Mantener una whitelist de columnas/joins ordenables por cada listado para evitar SQL inseguro y ordenar por aliases semanticos (`published_at`, `title`, `status`, `author`, etc.).
3. En vistas index, agregar una barra superior liviana con buscador, selects de orden y direccion, submit GET y accion de limpieza.
4. Priorizar una primera tanda de listados ya paginados:
   - `news`
   - `events`
   - `festivales`
   - `interpretes`
   - `users`
   - `newsletter`
   - `knowledge_articles`
   - listados paginados de `pasarela`
5. Tratar aparte los listados legacy:
   - `albunes`: pasar de `get()` a paginacion server-side y luego sumar buscador/orden
   - `classifieds`: unificar las tres bandejas o definir discovery por estado sin traer colecciones completas
   - `contributions` y otros `get()` administrativos: evaluar migracion a indice paginado
6. Acompañar con tests feature focalizados sobre los indices mas sensibles para verificar persistencia de query string, orden permitido y restriccion por rol cuando corresponda.

## Risks

- Riesgo medio de performance si se agregan busquedas con `orWhereHas` o joins en columnas no indexadas; hay que limitar la primera tanda a criterios razonables y reportar indices faltantes si aparecen cuellos de botella.
- Riesgo funcional bajo-medio en listados con alcance por rol (`created_by`, `user_id`): los tests deben cubrir que la busqueda/orden no rompa el scoping actual.
- Riesgo de UX si cada vista implementa nombres distintos; conviene un partial compartido o convencion visual uniforme aunque la logica interna cambie por modulo.
