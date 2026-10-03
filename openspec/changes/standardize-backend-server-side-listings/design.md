## Context

El proyecto ya confirmo que el criterio deseado para backend no es replicar sin control los DataTables legacy, sino consolidar una experiencia moderna server-side con busqueda, orden clickeable, filtros por modulo y paginacion estable. Tambien quedo claro que la heterogeneidad actual complica onboarding, mantenimiento y performance.

## Goals

- Unificar el criterio de todos los listados backend/pasarela futuros bajo un mismo patron server-side.
- Separar la definicion funcional de cada listado de la mecanica repetida de request, orden, filtros y paginacion.
- Mejorar escalabilidad y performance evitando client-side pesado y queries arbitrarias.
- Dejar una regla documental explicita para que futuros desarrollos respeten el estandar.

## Non-Goals

- No implementar en esta change la migracion completa de todos los listados legacy.
- No imponer DataTables como patron por defecto.
- No cambiar la experiencia de `enciclopedia` en esta definicion.

## Proposed Architecture

### 1. Listing criteria canonico

Cada listado server-side debe aceptar como base:

- `search`
- `sort`
- `direction`
- filtros auxiliares del modulo
- `page`
- `per_page` cuando aplique

Estos valores deben normalizarse antes de tocar la query.

### 2. Definition por modulo

Cada ABM debe declarar de forma explicita:

- columnas visibles
- columnas buscables
- columnas ordenables
- filtros permitidos
- orden por defecto
- desempate estable
- relaciones/eager loads requeridos
- counts requeridos

Esto evita controladores con logica repetida y desordenada.

### 3. Aplicador reusable

La mecanica comun debe vivir en un servicio, builder o trait reutilizable que:

- sanee `search`, `sort` y `direction`
- haga cumplir whitelists
- aplique filtros
- aplique orden valido
- conserve `withQueryString()`
- pagine de forma consistente

### 4. UI reusable

Los listados futuros deben compartir:

- toolbar de filtros
- encabezados clickeables para ordenar
- indicadores visuales de orden activo
- boton limpiar
- empty state
- paginacion consistente

### 5. Migracion de legacy por tandas

Los listados heredados deben migrarse con este orden:

1. convertir `get()` / client-side pesado a query paginada server-side
2. definir criterios permitidos
3. agregar discovery comun
4. medir impacto e indices necesarios

## Performance Principles

- Nunca aceptar `sort` libre desde request.
- Siempre usar desempate estable (`id` o timestamp + `id`).
- Limitar `with()` a relaciones realmente visibles.
- Preferir `withCount()` sobre calculos en Blade.
- Medir `whereHas()` y joins antes de expandir busquedas relacionales.
- Agregar indices solo cuando una busqueda/orden real lo justifique.
- Evitar mezclar tablas client-side grandes con paginacion server-side inconsistente.

## Recommended Rollout

### Grupo A: listados ya listos para el estandar
- `news`
- `events`
- `festivales`
- `interpretes`
- `users`
- `newsletter`
- `pasarela.templates`
- `pasarela.publication_requests`
- `pasarela.notifications`

### Grupo B: requieren migracion previa
- `albunes`
- `classifieds`
- `contributions`
- `canciones`

## Risks

- Si el estandar no queda documentado, los proximos ABMs volveran a divergir.
- Si se implementa busqueda relacional sin control, el costo de query puede crecer demasiado.
- Si se mezclan patrones viejos y nuevos sin gobernanza, la UX del backend quedara inconsistente.
