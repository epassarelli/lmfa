## Why
El responsable necesita ejemplos locales para comprender conjuntos, jornadas y artistas, y acceso más directo a los módulos culturales del Backoffice.

## What Changes
- Cargar tres ejemplos ficticios locales: un evento independiente y conjuntos de cuatro y cinco jornadas, todos con intérpretes existentes y aviso DEMO LOCAL.
- Desagregar Música y Cultura, agrupar Pasarela de Contenidos y ordenar ASC los enlaces de cada sección y submenú.
- Autorización explícita: pedido del usuario del 2026-10-08; incluye inserción de demos locales. No modificar contenido anterior.

## Capabilities
### New Capabilities
- `event-local-demos`: carga identificable, transaccional e idempotente para probar conjuntos.
- `backoffice-sidebar-order`: estructura y orden del sidebar con permisos conservados.
### Modified Capabilities
Ninguna.

## Impact
`config/adminlte.php`, script revisable `project/scripts/local/event-programs-demo.php`, test Feature del menú y documentación. Sin migraciones, nuevas dependencias, producción ni cambios de URLs. Rama activa conservada.
