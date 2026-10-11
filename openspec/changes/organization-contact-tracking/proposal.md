## Why

Hoy el contacto con artistas y festivales empieza por Instagram o Facebook, y el correo llega después. `organizations` guarda email, consentimiento y baja, pero no tiene dónde registrar el canal social, en qué etapa va el contacto ni cuándo se lo contactó, y no permite sacar la lista de contactables para una difusión. Eso obliga a llevar una planilla aparte. Esta opción es la liviana elegida por el usuario (opción A); el envío masivo dentro del portal queda para cuando haya volumen (opción C) y el CRM completo (`crm-contact-directory`) queda como está.

## What Changes

- Nuevas columnas en `organizations`: `instagram`, `facebook`, `contact_stage` (`prospect`, `contacted`, `replied`, `email_obtained`, `declined`; default `prospect`), `last_contacted_at`, `contact_notes`.
- Backoffice de Organizaciones:
  - Formulario con redes, etapa, último contacto y notas.
  - Listado con columna de etapa, accesos directos a Instagram/Facebook y filtro por etapa.
  - Botón "Registrar contacto" que fija `last_contacted_at = ahora` y pasa de `prospect` a `contacted`.
  - **Exportar CSV de contactables**: solo organizaciones activas, con consentimiento, sin baja y con email, respetando los filtros de tipo y búsqueda del listado.
- Datos demo: dos organizaciones en etapa de contacto por redes (sin email).

## Capabilities

### New Capabilities
- `contact-tracking`: seguimiento de contacto por redes y exportación de contactables.

## Impact

- Una migración reversible sobre `organizations` (5 columnas nullable o con default), sin migración de datos.
- Cambios en `OrganizationController`, `OrganizationRequest`, `Organization`, vistas del backoffice, `config/entity_management.php`, rutas de `admin.php` y el seeder demo.
- Sin cambios públicos, de rutas públicas ni SEO. `social_accounts` (Pasarela) no se toca.

## Non-goals

- Envío de correos, plantillas, campañas y link de baja (opción C, con volumen).
- Historial de interacciones por contacto y canales múltiples (CRM).
- Importación masiva desde planillas.
- Validar que un perfil de Instagram o Facebook exista.
