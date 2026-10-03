## Context

MFA ya consolidó que el backlog operativo activo vive en Google Drive, pestaña `Backlog`, mientras que `project/docs/backlog.json` quedó como legado. La implementación anterior intentó resolver esa operación desde Laravel y con un bridge `codex exec`, pero el diagnóstico local mostró dos límites: el binario `codex.exe` falla con `Acceso denegado` en este host y el adaptador live no quedó completo para lectura/escritura real de Drive.

Además, `BL-0011F` fue cerrada automáticamente con una evidencia local demasiado débil para su definición de terminado real en Drive. La nueva solución debe bajar el riesgo operativo: leer sí, escribir no; programar sí, activar no.

## Goals / Non-Goals

**Goals:**
- Leer el backlog activo de Drive desde un script nativo de Windows sin pasar por Laravel ni `codex exec`.
- Registrar un resumen local read-only con evidencia suficiente para auditoría mínima.
- Dejar una tarea programada nativa configurada y deshabilitada, lista para activarse sólo con decisión humana.
- Fijar un índice documental que elimine ambigüedades entre archivos canónicos del repo y artefactos operativos en Drive.

**Non-Goals:**
- No automatizar escrituras sobre Drive en esta etapa.
- No desplegar, no hacer commits y no activar triggers de ejecución.
- No reemplazar todavía el flujo editorial externo por Apps Script ni resolver credenciales productivas.
- No borrar el orquestador Laravel existente; sólo dejarlo fuera de la vía recomendada.

## Decisions

### 1. Runner nativo en PowerShell con OAuth refresh token

Se implementará un script PowerShell que consume Google Sheets API en modo `spreadsheets.readonly`, usando `client_id`, `client_secret` y `refresh_token` provistos por variables de entorno del host.

Rationale:
- evita depender de PHP/Artisan
- evita `codex exec`
- no exige librerías nuevas en el repo
- es compatible con Programador de tareas de Windows

Alternativas consideradas:
- service account JSON: descartado en esta etapa porque requiere firmar JWT RS256 localmente o agregar dependencias auxiliares
- export CSV público: descartado porque la hoja operativa no debe asumirse pública
- Codex/connector bridge: descartado por el bloqueo ya diagnosticado

### 2. Modo read-only explícito y prueba local separada

El runner tendrá dos caminos:
- `self_test`: valida configuración, rutas, logging y comando efectivo sin tocar red ni Drive
- `read_backlog`: lee Drive en modo read-only y genera un resumen local

Rationale:
- permite validar wiring local aunque falten credenciales
- reduce el riesgo antes de habilitar cualquier automatización real

### 3. Registro de tarea programada deshabilitada por defecto

La tarea se registrará como una Scheduled Task nativa con acción PowerShell hacia el runner read-only y quedará deshabilitada inmediatamente después del registro.

Rationale:
- cumple el pedido de “configurar” la tarea
- evita activar ejecuciones accidentales
- deja el comando listo para inspección humana

Alternativa descartada:
- dejar sólo un script de instalación sin registrar: no alcanza si el objetivo es dejar la tarea realmente configurada en Windows

### 4. Índice canónico explícito en `FUENTES_CANONICAS.md`

Se agregará un documento corto que aclare:
- precedencia entre chat, AGENTS, OpenSpec, `project/docs` y Drive
- nombres reales de archivos canónicos del repo
- ausencia actual de `openspec/project.md`
- uso de Drive sólo para operación, seguimiento y métricas

Rationale:
- corrige una ambigüedad real detectada en la sesión
- mejora trazabilidad para agentes y humanos

## Risks / Trade-offs

- **[Faltan credenciales OAuth refresh token]** → El runner quedará listo pero la prueba real de lectura dependerá de que el host tenga las variables de entorno configuradas.
- **[La hoja puede cambiar columnas o tab names]** → El script fija `spreadsheetId`, `sheet_name` y encabezados esperados, y devuelve bloqueo estructurado si no coinciden.
- **[La tarea programada podría confundirse con un flujo activo]** → Se registra deshabilitada y la documentación repite que no realiza escrituras ni activaciones automáticas.
- **[Persisten rastros del orquestador viejo en docs]** → `00_estado_actual.md` y la guía nueva aclaran que la vía preparada en esta etapa es nativa read-only.

## Migration Plan

1. Crear la spec y la documentación canónica nueva.
2. Implementar el runner read-only, la prueba local y el registrador de Scheduled Task deshabilitada.
3. Registrar la tarea nativa sin activarla.
4. Ejecutar sólo `self_test` local.
5. Dejar pendiente la futura etapa de lectura real y, más adelante, cualquier escritura sobre Drive.

## Open Questions

- Qué refresh token y qué cuenta Google del host se usarán para la futura lectura real.
- Si la tarea programada definitiva debe correr una vez por día o en otra ventana horaria cuando se habilite.
- Si el resumen read-only futuro debe limitarse a candidatos elegibles o incluir métricas agregadas del backlog completo.
