# Runner nativo read-only del Backlog de Drive

## Objetivo

Leer el backlog operativo de MFA directamente desde Google Sheets con un script nativo de Windows, sin usar `codex exec` ni el orquestador Laravel, y dejar evidencia local sin mutar Drive.

## Scripts involucrados

- `scripts/run_mfa_backlog_readonly.ps1`
- `scripts/test_mfa_backlog_readonly.ps1`
- `scripts/register_mfa_backlog_readonly_task.ps1`

## Variables de entorno requeridas para lectura real

El modo `read_backlog` requiere que el host Windows tenga:

- `MFA_GOOGLE_CLIENT_ID`
- `MFA_GOOGLE_CLIENT_SECRET`
- `MFA_GOOGLE_REFRESH_TOKEN`

Notas:

- el runner usa `https://www.googleapis.com/auth/spreadsheets.readonly`
- sin esas variables, el script se bloquea antes de llamar a Drive
- no hay escritura a Google Sheets en esta etapa

## Modos disponibles

### 1. Self-test local

Valida rutas, logging, spreadsheet objetivo y comando esperado de la tarea programada.

```text
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\test_mfa_backlog_readonly.ps1
```

Este modo:

- no llama a Google
- no modifica Drive
- no registra ni habilita tareas por sí solo

### 2. Lectura real del backlog

```text
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\run_mfa_backlog_readonly.ps1 -Mode read_backlog
```

Este modo:

- lee la pestaña `Backlog` del spreadsheet activo
- valida encabezados mínimos
- genera un resumen local en `project/automation/native_drive/logs`
- no escribe en Drive

## Tarea programada nativa

Registro recomendado:

```text
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\register_mfa_backlog_readonly_task.ps1
```

Comportamiento:

- registra una Scheduled Task nativa de Windows
- apunta al runner read-only
- la deja deshabilitada al finalizar

## Estado de seguridad actual

- `BL-0011F` quedó cerrada con evidencia real en el backlog activo
- la vía `codex exec` queda descartada para esta estrategia
- la vía Laravel ya puede consumir el mismo esquema OAuth mediante el driver `google_sheets`
- no hay escritura automatizada activa sobre Drive
