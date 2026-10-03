## Why

Mi Folklore Argentino necesita un orquestador local que ejecute trabajo autónomo de forma segura y trazable usando Google Drive como único backlog activo. Hoy el repo no tiene un ejecutor que:

- lea y priorice tareas desde la planilla operativa real
- reclame una sola tarea en curso por repositorio con bloqueo entre agentes
- ejecute el circuito completo de implementación, validación, revisión independiente y actualización de estado en Drive
- permita correr el mismo flujo en `dry-run`, con límites de tiempo, ventanas horarias y registro estructurado

Además, `project/backlog.json` dejó de ser la fuente activa y debe quedar explícitamente como legado sin eliminarse.

## What Changes

- Incorporar un ejecutor local del orquestador autónomo para MFA, configurable por proyecto, ventanas horarias y límites operativos.
- Integrar lectura/escritura del backlog de Google Drive para seleccionar tareas ejecutables, reclamarlas de forma segura y actualizar su resultado (`Hecha`, `En revisión`, `Bloqueada`).
- Implementar bloqueo de exclusión mutua para evitar que dos agentes tomen la misma tarea o que exista más de un WIP por repositorio.
- Incorporar un revisor independiente del diff y del criterio de terminado antes de actualizar Drive.
- Registrar ejecuciones, errores, decisiones, evidencias y resultados en un log estructurado local.
- Soportar modo `dry-run` para ensayar el circuito sin modificar código ni estados remotos.
- Agregar pruebas automatizadas del selector de tareas, locking, transiciones de estado, límites y revisión independiente.
- Documentar cómo ejecutarlo con `codex exec` y generar un script de instalación para Programador de tareas de Windows sin activarlo.
- Marcar `project/backlog.json` como legado, manteniéndolo sólo como referencia histórica.

## Capabilities

### New Capabilities
- `drive-backed-autonomous-orchestration`: orquestación local que usa Google Drive como backlog operativo único, con selección, reclamo, ejecución, revisión y cierre de tareas.
- `autonomous-run-governance`: límites por tiempo, cantidad máxima, horario, `dry-run`, locking y registros estructurados por corrida.

### Modified Capabilities
- `project-backlog-governance`: `project/backlog.json` permanece en el repo pero pasa a estado de legado explícito y deja de representar el backlog activo.

## Impact

- Nuevo módulo o carpeta operativa para el ejecutor, configuración, locking, logs y revisión independiente.
- Pruebas automatizadas focalizadas sobre el circuito autónomo y sus invariantes de seguridad.
- Documentación operativa nueva para ejecución manual con `codex exec` y para instalación en Programador de tareas de Windows.
- Actualización documental mínima para dejar asentado que el backlog activo está en Google Drive y `backlog.json` es legado.
