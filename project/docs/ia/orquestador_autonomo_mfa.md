# Orquestador autónomo de MFA

## Objetivo

Ejecutar trabajo autónomo sobre Mi Folklore Argentino usando Google Drive como backlog activo y `project/docs/backlog.json` sólo como legado histórico.

## Comando local

```text
php artisan mfa:orchestrate-backlog --project=mfa --dry-run
```

Ejecución con snapshot local:

```text
php artisan mfa:orchestrate-backlog --project=mfa --driver=fixture
```

Ejecución live por Google Sheets API:

```text
php artisan mfa:orchestrate-backlog --project=mfa --driver=google_sheets --dry-run
```

## Ejecución prevista con codex exec

El bridge live previsto es:

```text
codex exec --cwd "C:\proyectos\lmfa" -- "php artisan mfa:orchestrate-backlog --project=mfa --driver=codex_exec"
```

Estado actual del host:

- `codex.exe` está presente
- `codex exec --help` devuelve `Acceso denegado`
- por eso el driver `codex_exec` hoy debe considerarse bloqueado en este entorno
- el camino recomendado para live es `google_sheets`, no `codex_exec`
- faltan en el host Windows `MFA_GOOGLE_CLIENT_ID`, `MFA_GOOGLE_CLIENT_SECRET` y `MFA_GOOGLE_REFRESH_TOKEN` para ejecutar contra Drive real

## Reglas operativas

- Un solo WIP por repositorio
- Prioridad y elegibilidad definidas desde la pestaña `Backlog` del Google Sheet
- Selección limitada a tareas `IA_AUTONOMA` o `IA_CON_VALIDACION`
- `IA_CON_VALIDACION` pasa a `En revisión` después de la ejecución y revisión independiente
- `IA_AUTONOMA` puede cerrar en `Hecha` si toda la evidencia y validación está completa
- Nunca desplegar, operar producción, tocar secretos ni ejecutar acciones destructivas sin autorización humana

## Logs

Los logs estructurados se escriben en:

```text
storage/app/automation/orchestrator/logs
```

## Fixture de prueba

El snapshot local usado para pruebas vive en:

```text
project/automation/orchestrator/fixtures/mfa_backlog_snapshot.json
```
