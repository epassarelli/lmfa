## Why

El portal necesita comunicarse con artistas, academias, festivales, peñas, radios, productoras y prensa para conseguir contenido fresco (grillas, fechas, novedades) y reclamos de fichas. Hoy los datos de contacto están dispersos en columnas legacy de `interpretes` y no existe un lugar único para gestionarlos. El relevamiento de la base (oct-2026, local = producción) muestra que el problema principal es la **falta** de datos, no su dispersión:

- `interpretes`: 445 registros, sólo **63 (14 %)** con algún dato de contacto (47 correo, 41 facebook, 34 instagram, 32 teléfono, 17 youtube, 16 twitter, 0 web). Instagram en tres formatos (URL, `@handle`, handle suelto), 2 correos inválidos, ~8 valores posiblemente truncados por los límites legacy (30/50 caracteres), y contactos de terceros (productoras, prensa).
- `festivales` (47): sin campos de contacto. `events` (309): 12 mencionan redes y 4 teléfonos en el cuerpo.
- `penia_profiles`, `radio_signals`, `organizations`, `venues`, `classifieds`: vacíos. Academias de danza: sin entidad.

## What Changes

- Crear un directorio de contactos (mini CRM) en el backoffice, independiente de las entidades públicas: contactos, canales de contacto normalizados, vínculos polimórficos con fichas del portal e historial de interacciones.
- Importación idempotente desde `interpretes` (y, cuando existan datos, `penia_profiles`, `radio_signals`, `organizations`, `venues`, `users` publicadores, `classifieds`), con normalización, deduplicación y marcado de valores dudosos.
- Listado administrativo con filtros (segmento, provincia, canal disponible, estado, sin contacto / último contacto) y ficha con vínculos, canales e historial.
- Acciones asistidas por contacto: abrir `wa.me` con mensaje prearmado, abrir perfil de Instagram/Facebook copiando el mensaje, `mailto:`; cada acción registra una interacción.
- Cola de enriquecimiento: candidatos de canales (detectados por IA o por extracción de texto de eventos/festivales) quedan como **pendientes** con URL de fuente y requieren aprobación humana antes de pasar a `valid`.
- Alta manual de contactos sin ficha pública (academias, radios, prensa, municipios).

## Capabilities

### New Capabilities
- `contact-directory`: Directorio de contactos, canales, vínculos, interacciones, importación y backoffice.
- `contact-enrichment`: Cola de candidatos de contacto con fuente, revisión y aprobación.

### Modified Capabilities
- Ninguna. Las columnas legacy de `interpretes` se leen, no se modifican.

## Impact

- Nuevas tablas `crm_contacts`, `crm_contact_channels`, `crm_contact_links`, `crm_interactions`, `crm_channel_candidates`.
- Nuevos modelos, servicio de normalización, comando `crm:import`, controladores y vistas Backend, permisos (`crm.view`, `crm.manage`).
- No se reutilizan `contacts` (modelo legacy sin migración) ni `social_accounts` (tokens OAuth de publicación).
- Non-goals de este cambio: campañas de email masivas, envíos automáticos por WhatsApp/Instagram, formulario público de reclamo de ficha, exposición pública de datos del CRM. Quedan para cambios posteriores.
