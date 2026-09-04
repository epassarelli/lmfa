## Why

El portal tiene jobs, scheduler, caché y dependencias de base de datos, pero no ofrece una comprobación segura y unificada de su estado operativo. Antes de ampliar módulos o habilitar directorios, el equipo necesita detectar fallos de runtime sin depender de inspección manual de logs ni exponer información sensible.

## What Changes

- Incorporar un endpoint de healthcheck público y mínimo para disponibilidad de la aplicación.
- Incorporar un diagnóstico autenticado para administración que informe estado de base, caché, scheduler y cola sin revelar secretos.
- Registrar la última ejecución conocida del scheduler y ofrecer un runbook local/staging para interpretar las señales.
- Cubrir respuestas saludables y degradadas con pruebas feature.

## Capabilities

### New Capabilities
- `operational-healthcheck`: disponibilidad pública mínima y diagnóstico administrativo seguro del runtime Laravel.

### Modified Capabilities
- Ninguna.

## Impact

- Nuevas rutas web/administrativas, servicio de diagnóstico, comando de scheduler y pruebas feature.
- Ajustes mínimos de configuración y documentación operativa.
- No modifica contenido editorial, flags públicos, producción, credenciales ni integraciones externas.
