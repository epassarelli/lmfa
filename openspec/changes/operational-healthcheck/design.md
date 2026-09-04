## Context

Laravel ya depende de MySQL, caché, scheduler y workers para funciones editoriales y de Pasarela. Hoy los diagnósticos requieren acceso a logs o consola; no existe una señal HTTP segura ni evidencia de la última ejecución del scheduler.

## Goals / Non-Goals

**Goals:**
- Exponer disponibilidad mínima sin secretos.
- Dar al administrador un diagnóstico autenticado y accionable.
- Registrar la última ejecución del scheduler sin una migración nueva.
- Mantener coste de consulta bajo y respuestas cacheables sólo cuando sea seguro.

**Non-Goals:**
- No monitorizar proveedores externos, ejecutar jobs, cambiar flags ni sustituir observabilidad gestionada.
- No exponer versiones, credenciales, rutas internas, payloads ni detalles de excepción al público.

## Decisions

- `GET /healthz` devolverá sólo estado de aplicación y base; es apto para balanceador y no requiere autenticación.
- Un diagnóstico bajo backend requerirá `administrador` y mostrará checks discretos (`ok`, `degraded`) para DB, caché, scheduler y cola.
- La marca de scheduler se guardará en caché al inicio de `schedule:run`, evitando cambios de esquema y permitiendo alertar por antigüedad.
- Fallos se convierten en `503` con un código estable; el detalle queda en logs.

## Risks / Trade-offs

- [Un healthcheck de DB puede añadir carga] → consulta mínima y timeout corto.
- [La caché puede estar caída] → el endpoint público no depende de la marca de scheduler.
- [El scheduler podría no estar configurado en hosting] → diagnóstico degradado y runbook explícito, sin intentar arrancarlo desde HTTP.
