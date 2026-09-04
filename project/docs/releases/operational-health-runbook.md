# Runbook — salud operativa

## Superficies

- `GET /healthz`: disponibilidad mínima pública. Devuelve `200` con aplicación/base disponibles o `503` sin detalles internos.
- `GET /admin/operational-health`: diagnóstico JSON restringido a `administrador`.

## Interpretación

- `database=degraded`: detener operaciones editoriales y verificar conectividad/base antes de reintentar.
- `cache=degraded`: revisar el driver de caché; el contenido puede funcionar con degradación según configuración, pero el scheduler no podrá aportar evidencia fiable.
- `scheduler=degraded`: no se observó `schedule:run` dentro de `OPERATIONS_SCHEDULER_MAX_AGE_SECONDS` (600 por defecto). Verificar cron o supervisor; no ejecutar jobs desde HTTP.
- `queue=degraded`: la conexión es `sync`; sirve para desarrollo, no para un worker asíncrono operativo. Confirmar configuración y worker antes de habilitar automatizaciones.

## Validación segura

1. Consultar `/healthz` y esperar `200`.
2. Como administrador, consultar `/admin/operational-health`.
3. Ejecutar `php artisan schedule:run` desde el entorno autorizado y confirmar que el latido de scheduler vuelve a `ok`.
4. Nunca incluir secretos, payloads, tokens o trazas en tickets o capturas.

## Límites

Este diagnóstico no reemplaza APM, alertas externas, backups ni monitoreo de proveedores. En producción y staging sólo se opera con acceso autorizado y runbook de despliegue.
