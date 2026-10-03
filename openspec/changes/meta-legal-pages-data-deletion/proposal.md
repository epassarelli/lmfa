## Why

La app necesita cumplir con los requisitos de Meta/Facebook para OAuth y eliminación de datos, y hoy las URLs públicas requeridas responden `404`. Además del cumplimiento externo, hace falta un flujo local seguro, auditable e idempotente para procesar solicitudes de eliminación sin romper relaciones editoriales existentes.

## What Changes

- Agregar páginas públicas de `Política de privacidad`, `Condiciones del servicio` y `Eliminación de datos` sobre el layout público existente, con SEO básico y canonical sobre el dominio `.com`.
- Incorporar compatibilidad histórica para `GET /deleteuserdata` y una página pública para consultar el estado de una solicitud de eliminación.
- Implementar el callback `POST /deleteuserdata` validando `signed_request` con `HMAC-SHA256` y `config('services.facebook.client_secret')`.
- Persistir solicitudes de eliminación con código de confirmación robusto, estado, timestamps y referencia segura a la cuenta afectada cuando exista.
- Ejecutar la desvinculación o anonimización de datos asociados a Facebook de forma transaccional e idempotente, preservando contenido editorial que no deba borrarse.
- Alinear la configuración OAuth de Facebook con el dominio canónico `.com` sin romper rutas existentes.
- Agregar pruebas automatizadas del contenido legal, seguridad del callback, persistencia, estado público e idempotencia.
- Documentar las URLs exactas que deben cargarse manualmente en Meta después del despliegue.

## Capabilities

### New Capabilities
- `legal-pages`: páginas legales públicas y estáticas mantenibles para privacidad, condiciones y eliminación de datos.
- `facebook-data-deletion`: recepción, registro, procesamiento y consulta pública de solicitudes de eliminación iniciadas por Meta/Facebook.

### Modified Capabilities
- `unify-news-flows`: sin cambios de requerimientos; solo podría compartir convenciones de SEO/canonical si la implementación reutiliza helpers existentes.

## Impact

- `routes/web.php` y posible ajuste puntual de middleware CSRF/rate limiting para el callback externo.
- Controladores frontend, vistas Blade públicas y metadata SEO de páginas legales/estado.
- `config/services.php` y `.env.example` para asegurar variables de OAuth/Meta consistentes con el dominio canónico.
- Nueva persistencia para solicitudes de eliminación y posibles ajustes sobre modelos de usuarios/cuentas sociales/sesiones según el esquema real.
- Nuevos tests Feature/Unit para callback firmado, páginas públicas y preservación de relaciones editoriales.
