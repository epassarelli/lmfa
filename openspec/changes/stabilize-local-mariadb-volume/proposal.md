## Why

MariaDB 10.8 sobre el bind mount local de Windows/WSL se cae durante ciertos
DDL y dejó el historial de migraciones inconsistente. El entorno de desarrollo
necesita una base estable para recuperar validaciones y demo data sin afectar el
volumen actual ni ningún entorno remoto.

## What Changes

- Cambiar el almacenamiento predeterminado del servicio local `db` a un volumen
  administrado por Docker.
- Conservar `./database_local` intacto como respaldo del estado previo.
- Documentar el procedimiento de creación, restauración y verificación del
  entorno local antes de ejecutar migraciones.

## Capabilities

### New Capabilities
- `local-database-recovery`: reconstrucción reversible de la base local en un
  volumen Docker estable, sin tocar producción ni borrar el volumen anterior.

### Modified Capabilities

- Ninguna.

## Impact

- `docker-compose.yml` y la documentación operativa local.
- Volúmenes Docker locales; no cambia rutas, APIs, producción ni datos remotos.
