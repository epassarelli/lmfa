## Why

El backend hoy mezcla listados server-side nuevos, filtros parciales y grillas legacy heterogeneas. Hace falta fijar un estandar explicito para que los proximos desarrollos de ABMs no vuelvan a divergir en UX, arquitectura, performance y mantenibilidad.

## What Changes

- Definir una arquitectura canonica para listados administrativos server-side reutilizable entre modulos.
- Establecer el contrato comun de discovery (`search`, `sort`, `direction`, filtros, paginacion, `per_page`) para nuevos ABMs y refactors futuros.
- Formalizar reglas de implementacion para encabezados clickeables, whitelists de orden, busquedas por columnas visibles y desempates estables.
- Dejar explicitado que los listados legacy deben migrar a este patron y no replicar soluciones client-side heterogeneas por defecto.

## Capabilities

### New Capabilities
- `backend-server-side-listing-standard`: Estandariza como deben diseñarse e implementarse los listados administrativos server-side del proyecto para nuevos desarrollos y migraciones futuras.

### Modified Capabilities

## Impact

- Documentacion OpenSpec: nueva change con proposal, design, tasks y spec
- Reglas operativas: `project/specs/_global_rules.md`
- Areas futuras afectadas: `app/Http/Controllers/Backend`, `app/Http/Controllers/Pasarela`, `resources/views/backend`, `resources/views/pasarela`, componentes Blade y servicios/traits de listados
