# Diseño técnico: mfa-google-drive-autonomous-orchestrator

## Resumen

La solución incorpora un ejecutor local orientado a MFA que toma una tarea desde Google Drive, la reclama con exclusión mutua, reúne contexto local, ejecuta sólo su alcance, valida, solicita una revisión independiente del diff y actualiza el estado remoto con evidencia.

El diseño separa cinco piezas:

1. `runner`: punto de entrada y control de límites
2. `drive backlog adapter`: lectura, elegibilidad, reclamo y actualización de filas
3. `execution pipeline`: contexto, implementación, validación y cierre
4. `reviewer`: segunda revisión independiente del diff y del criterio de terminado
5. `governance`: locking, ventanas horarias, `dry-run` y logging estructurado

## Decisiones clave

### 1. Google Drive es la única fuente de backlog activo

La selección de tarea no debe depender de `project/backlog.json`. El ejecutor sólo puede usar Drive para:

- detectar prioridad
- verificar estado ejecutable
- validar dependencias resueltas
- distinguir `IA_AUTONOMA` e `IA_CON_VALIDACION`
- obtener notas, evidencia y próximo hito

`project/backlog.json` queda como legado documental y no participa en la selección.

### 2. Un solo WIP por repositorio

Antes de reclamar una tarea, el ejecutor debe comprobar dos bloqueos:

- lock local del repositorio para impedir dos corridas simultáneas sobre MFA
- lock de la tarea en Drive para impedir doble reclamo de la misma fila

La estrategia recomendada es:

- lock local por archivo con expiración y metadatos de agente/pid/hora
- reclamo remoto optimista verificando que la fila siga elegible antes de escribir `En curso`

Si cualquiera de los dos locks falla, la corrida registra el motivo y termina sin avanzar a implementación.

### 3. Selección determinística y auditable

El selector debe ordenar sólo tareas ejecutables:

- estado pendiente o equivalente definido en la planilla
- sin bloqueos activos
- dependencias resueltas
- rol `IA_AUTONOMA` o `IA_CON_VALIDACION`
- prioridad más alta primero
- desempate estable por fecha objetivo y orden de fila

Toda exclusión debe quedar registrada con motivo estructurado para auditoría.

### 4. Reclamo seguro y reversible

El reclamo remoto debe escribir:

- estado `En curso`
- agente
- timestamp
- identificador de corrida

Si la corrida falla antes de cambiar código o queda bloqueada por autorización humana, el mismo pipeline debe actualizar la fila a:

- `Bloqueada` con causa y acción requerida, o
- estado previo si la reclamación no llegó a consolidarse

### 5. Pipeline explícito por etapas

Cada corrida debe seguir etapas fijas:

1. cargar configuración y verificar horario/límites
2. leer backlog Drive
3. seleccionar tarea elegible
4. adquirir locks y reclamar fila
5. reunir contexto de Drive y `project/docs`
6. ejecutar sólo el alcance permitido
7. correr pruebas, lint y controles configurados
8. lanzar revisión independiente del diff y del criterio de terminado
9. actualizar Drive con resultado y evidencia
10. registrar salida estructurada y decidir si continúa

Esto permite `dry-run`, reintentos y auditoría por etapa.

### 6. Revisión independiente obligatoria

Antes de cerrar una tarea como `Hecha` o `En revisión`, un revisor separado del ejecutor debe inspeccionar:

- diff generado
- evidencia de tests
- cumplimiento del criterio de terminado
- señales de riesgo o alcance excedido

No hace falta un proceso aislado del sistema operativo, pero sí una abstracción independiente del flujo principal y un resultado explícito:

- `approved`
- `needs_human_validation`
- `blocked`

### 7. `dry-run` primero como circuito canónico

El modo `dry-run` debe recorrer el flujo completo sin:

- editar código
- reclamar permanentemente la fila en Drive
- marcar resultados finales remotos
- disparar acciones no reversibles

Sí debe producir:

- selección de tarea candidata
- validación de elegibilidad
- simulación de reclamo
- plan de archivos/contexto a tocar
- controles que correrían
- salida estructurada

Esto permite probar el circuito antes de un primer uso real.

### 8. Configuración declarativa

La configuración debe vivir fuera del código principal y cubrir:

- proyecto/repositorio
- spreadsheet id/url, pestaña y columnas
- ventanas horarias
- duración máxima
- máximo de tareas por corrida
- comandos de validación
- comportamiento de `dry-run`
- política de continuación automática

El diseño debe tolerar que la planilla evolucione, por lo que conviene mapear columnas por nombre lógico en vez de hardcodear índices dispersos.

### 9. Logging estructurado y evidencias

Cada corrida debe generar un registro estructurado local con:

- inicio y fin
- tarea seleccionada
- filtros de elegibilidad
- lock local/remoto
- archivos afectados
- comandos de validación y resultados
- decisión del revisor
- actualización final en Drive
- errores y stack resumido

Formato sugerido: JSONL por corrida más un resumen legible.

## Estructura sugerida

- `project/automation/orchestrator/`
- `project/automation/orchestrator/config/`
- `project/automation/orchestrator/logs/`
- `project/automation/orchestrator/bin/`
- `tests/Feature/Automation/` o `tests/Unit/Automation/` según corresponda
- `docs` o `project/docs/ia/` para instrucciones operativas

La ubicación exacta debe ajustarse al estilo del repo después del relevamiento local.

## Integración con el backlog de Drive

La planilla real observada contiene al menos una pestaña de iniciativas y referencias a tareas operativas como `BL-0011A/B/C`. Por eso el adaptador debe soportar:

- descubrir o configurar la pestaña operativa correcta
- leer encabezados reales y mapear columnas obligatorias
- fallar de forma clara si faltan campos mínimos

No se debe inventar la estructura final de la planilla: la primera implementación debe parametrizarla y validarla al inicio.

## Integración con `codex exec`

Se comprobó que `codex.exe` está presente en el entorno pero `codex exec --help` devuelve `Acceso denegado`. El diseño debe contemplar dos cosas:

- documentar la forma prevista de ejecución con `codex exec`
- detectar y reportar cuando ese comando no sea ejecutable en el host actual

El script del Programador de tareas debe dejar preparado el comando, pero no activarlo.

## No objetivos

- desplegar a producción
- ejecutar migraciones o SQL destructivo
- resolver secretos o credenciales automáticamente
- tomar decisiones estratégicas sobre priorización fuera de lo que indique Drive
- soportar múltiples repositorios a la vez en esta primera versión

## Riesgos abiertos

- la pestaña operativa exacta de Drive para `BL-0011*` todavía debe confirmarse durante el relevamiento de implementación
- la ejecución real de `codex exec` puede requerir una vía distinta en WindowsApps
- el mecanismo de escritura concurrente sobre Google Sheets debe diseñarse con cuidado para evitar pisadas entre agentes
- existe riesgo de acoplar demasiado el selector a nombres de columna que cambien sin aviso

## Orden de implementación sugerido

1. Relevar estructura real de la pestaña operativa de Drive y decidir mapeo de columnas
2. Implementar configuración, lectura y selección elegible con `dry-run`
3. Implementar locks local/remoto y reclamo seguro
4. Implementar pipeline de ejecución y resultados estructurados
5. Implementar revisor independiente
6. Agregar pruebas automatizadas
7. Documentar `codex exec`, dry-run y Programador de tareas
8. Marcar `backlog.json` como legado
