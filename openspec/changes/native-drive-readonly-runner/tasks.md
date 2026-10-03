## 1. Trazabilidad y fuentes

- [x] 1.1 Crear la propuesta, diseño y specs del runner nativo read-only para Drive.
- [x] 1.2 Crear `project/docs/FUENTES_CANONICAS.md` con precedencia y rutas reales del repo.
- [x] 1.3 Ajustar `project/docs/00_estado_actual.md` para reflejar el estado operativo real de `BL-0011F` y de la automatización.

## 2. Runner nativo

- [x] 2.1 Implementar un script PowerShell read-only para leer Google Sheets sin Laravel ni `codex exec`.
- [x] 2.2 Implementar un modo `self_test` que valide configuración, rutas y logging sin red.
- [x] 2.3 Documentar las variables de entorno y el uso operativo del runner.

## 3. Programador de tareas

- [x] 3.1 Implementar un registrador nativo de Scheduled Task apuntando al runner read-only.
- [x] 3.2 Dejar la tarea deshabilitada por defecto y documentar ese comportamiento.
- [x] 3.3 Preparar una prueba local de sólo lectura sin activar ejecuciones automáticas con escritura.
