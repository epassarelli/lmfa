## Why

El circuito actual de backlog autónomo de MFA quedó atado a un orquestador Laravel y a `codex exec`, pero esa vía no es confiable en este host y además cerró `BL-0011F` con evidencia insuficiente. Hace falta una alternativa nativa de Windows, segura y verificable, que lea el backlog activo de Drive sin activar todavía escrituras automáticas.

## What Changes

- Incorporar un runner nativo en PowerShell para leer la pestaña `Backlog` de Google Sheets en modo estrictamente read-only.
- Preparar una tarea programada de Windows que invoque ese runner nativo, pero dejarla registrada en estado deshabilitado por defecto.
- Agregar una prueba local de sólo lectura para validar configuración, rutas, logging y comando efectivo antes de habilitar cualquier ejecución programada real.
- Crear una guía operativa nueva para el runner nativo y dejar explícito que no usa ni `codex exec` ni el orquestador Laravel.
- Crear `project/docs/FUENTES_CANONICAS.md` para fijar la precedencia real entre Git, Drive y OpenSpec y corregir referencias documentales ambiguas.
- Actualizar el estado actual del proyecto para reflejar que `BL-0011F` quedó reabierta en `Parcial` y que la nueva vía nativa está preparada sólo en modo lectura.

## Capabilities

### New Capabilities
- `native-drive-readonly-runner`: ejecución nativa en Windows para leer el backlog activo de Google Drive, generar un resumen local y registrar evidencia sin mutar Drive.
- `windows-disabled-backlog-scheduler`: definición y registro de una tarea programada nativa que deja el disparador configurado pero deshabilitado hasta aprobación humana.
- `canonical-source-index`: índice canónico del proyecto que define qué documentos del repo y qué hojas de Drive son fuente de verdad según el tipo de decisión.

### Modified Capabilities
- `<none>`: no hay specs previas archivadas en `openspec/specs/` para modificar en este cambio.

## Impact

- Nuevos scripts PowerShell en `scripts/` para lectura de Google Sheets, prueba local y registro de tarea programada.
- Nueva documentación operativa en `project/docs/ia/`.
- Nuevo índice documental en `project/docs/FUENTES_CANONICAS.md`.
- Ajustes puntuales en `project/docs/00_estado_actual.md` para reflejar el estado real del backlog y de la automatización.
