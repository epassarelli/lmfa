## Why

El auditor de Festivales calcula deuda editorial, pero omite `visitas`; por eso los registros con igual prioridad y score se ordenan de forma provisional y no reflejan demanda real. El campo ya existe y se incrementa en el frontend, así que puede incorporarse sin migraciones ni escrituras.

## What Changes

- Leer y normalizar `festivales.visitas` en cada fila auditada.
- Ordenar por prioridad, score ascendente, visitas descendentes e ID ascendente.
- Mostrar visitas en consola e incluirlas en el CSV.
- Cubrir orden, exportación y comportamiento read-only con pruebas.

## Capabilities

### New Capabilities

- `festival-audit-demand-priority`: priorización reproducible del auditor de Festivales usando demanda real.

### Modified Capabilities

Ninguna.

## Impact

- `app/Console/Commands/AuditFestivals.php`
- `tests/Feature/Festivals/FestivalAuditCommandTest.php`
- documentación operativa y backlog
- sin cambios de esquema, rutas, API ni datos
