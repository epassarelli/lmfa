## Context

El servicio `db` monta `./database_local` directamente desde Windows. MariaDB
10.8 se cae en ese volumen al ejecutar determinados DDL y la recuperacion deja
el historial de migraciones sin las entradas esperadas. El entorno es solo de
desarrollo y su contenido puede reconstruirse, pero el directorio actual debe
conservarse hasta verificar el reemplazo.

## Goals / Non-Goals

**Goals:**
- Ejecutar MariaDB local sobre un volumen administrado por Docker.
- Mantener el volumen bind mount anterior como respaldo sin borrarlo.
- Dejar un procedimiento reproducible para inicializar, migrar y comprobar la
  nueva base antes de reanudar las pruebas.

**Non-Goals:**
- Migrar, borrar o alterar datos de produccion o staging.
- Eliminar `database_local` o restaurar datos automaticamente.
- Cambiar la version de MariaDB en el mismo cambio.

## Decisions

- Usar un volumen nombrado `lmfa_db_data` en lugar del bind mount. Docker lo
  almacena dentro de su entorno Linux y evita el acceso directo al filesystem
  de Windows que interviene en los crashes. La alternativa de actualizar la
  imagen se descarta por ahora: mezclaria un cambio de version con la
  recuperacion del almacenamiento.
- Conservar la definicion del volumen antiguo comentada en la documentacion,
  no en Compose, para evitar que el servicio elija accidentalmente datos
  inconsistentes.
- Crear el nuevo volumen vacio y requerir la ejecucion manual y visible de las
  migraciones antes de cargar seeders o datos demo.

## Risks / Trade-offs

- [El volumen nuevo no contiene datos demo] -> Se conserva el directorio
  anterior y se documenta la reconstruccion con migraciones y seeders.
- [El problema puede pertenecer a MariaDB y no al bind mount] -> La nueva base
  se valida con migraciones y suite focalizada antes de declararla estable.
- [Un operador puede borrar el respaldo] -> El cambio no incluye comandos de
  eliminacion de `database_local` ni de volumenes Docker.

## Migration Plan

1. Conservar `database_local` y realizar un dump de lectura si el contenedor
   actual responde.
2. Cambiar Compose al volumen nombrado y recrear solo el servicio `db`.
3. Confirmar conectividad y ejecutar `migrate:status` antes de migrar.
4. Aplicar migraciones y seeders de desarrollo solo tras verificar el estado
   vacio y contar con autorizacion expresa ya registrada para datos locales.
5. Si la nueva configuracion falla, volver el mount de Compose al directorio
   existente sin borrar el volumen nombrado.
