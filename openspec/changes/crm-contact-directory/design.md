## Context

Los datos de contacto existen sólo en `interpretes` (columnas `correo`, `telefono`, `instagram`, `facebook`, `youtube`, `twitter`, `web`) y en campos `email/phone/website` de entidades nuevas aún vacías. Un mismo contacto real (p. ej. una productora como `info@curvamusic.com`) puede corresponder a varias fichas, y una ficha puede tener varios contactos (artista, prensa, mánager). El valor de negocio está en conseguir y usar contactos para captar contenido; el CRM es un instrumento interno, nunca público.

## Goals / Non-Goals

**Goals:**
- Un registro único por contacto real, con N canales normalizados y N vínculos a fichas.
- Importación repetible sin duplicados y con trazabilidad de origen.
- Backoffice para segmentar, contactar de forma asistida y registrar historial.
- Base de datos de candidatos para enriquecimiento con aprobación humana.

**Non-Goals:**
- Envíos masivos (email/WhatsApp/Instagram), plantillas de campaña, webhooks de rebote.
- Sincronización bidireccional CRM → columnas públicas de las fichas.
- Scraping automático en producción sin aprobación; el job de IA sólo propone candidatos.

## Decisions

### Modelo de datos

```
crm_contacts
  id, kind ENUM(person, organization), name, segment VARCHAR(30)
    (artista, academia, festival, penia, radio, productora, prensa, municipio, venue, otro),
  province_id NULL, locality_id NULL, city NULL,
  status VARCHAR(20) DEFAULT 'active'  (active, do_not_contact, unreachable),
  opt_out_at NULL, owner_user_id NULL, last_contacted_at NULL, notes TEXT NULL,
  timestamps, soft deletes

crm_contact_channels
  id, contact_id FK, channel VARCHAR(20)
    (email, whatsapp, phone, instagram, facebook, tiktok, youtube, twitter, website, spotify),
  value VARCHAR(255)            -- tal cual se cargó / se mostrará
  normalized_value VARCHAR(255) -- clave de dedupe
  is_primary BOOL, status VARCHAR(20) (valid, needs_review, invalid, bounced),
  source VARCHAR(30) (import:interpretes, manual, enrichment, ...), source_url NULL,
  verified_at NULL, verified_by NULL, timestamps
  UNIQUE(channel, normalized_value)

crm_contact_links
  id, contact_id FK, linkable_type, linkable_id, role VARCHAR(20)
    (titular, prensa, manager, organizador, productora), timestamps
  UNIQUE(contact_id, linkable_type, linkable_id, role)

crm_interactions
  id, contact_id FK, channel, direction (outbound, inbound), subject NULL,
  summary TEXT NULL, outcome VARCHAR(30) NULL, user_id, happened_at, timestamps

crm_channel_candidates
  id, contact_id NULL, linkable_type NULL, linkable_id NULL, channel, value,
  normalized_value, source_url, detector (ai, text_extraction, public_form),
  confidence DECIMAL(3,2) NULL, status (pending, approved, rejected), reviewed_by NULL,
  reviewed_at NULL, timestamps
```

Alternativa descartada: usar `organizations` como hub. Mezcla perfil público/publicador con datos privados de CRM y no modela personas (mánager, prensa).

### Normalización (`ContactChannelNormalizer`)
- email: trim + lowercase; inválido → `needs_review`.
- instagram/facebook/tiktok/twitter/youtube: extraer handle desde URL (`instagram.com/x/`, `@x`, `x`) → `x` en minúsculas; valores que no parecen handle/URL (p. ej. "Damián Rosales") → `needs_review`.
- phone/whatsapp: normalizar a E.164 argentino (`+549…` para móviles cuando el número contiene `15` o tiene 10 dígitos con característica), sin éxito → conservar valor y `needs_review`. Usar `giggsey/libphonenumber-for-php` (región AR).
- website: host + path sin esquema ni `www`, sin barra final.
- Valores con longitud igual al límite legacy (30 correo, 50 redes) → `needs_review` por posible truncamiento.

### Importación (`php artisan crm:import {--source=interpretes} {--dry-run}`)
1. Por cada registro fuente con algún canal: normalizar canales.
2. Si algún `normalized_value` ya existe → reutilizar ese contacto (dedupe); si no, crear contacto `segment` según fuente, `name` = nombre de la ficha.
3. Crear vínculo `titular`; si el email es de dominio distinto al nombre del artista y contiene `prensa|produccion|info|oficina|management` → rol `prensa`/`productora` sugerido, marcado para revisión.
4. Upsert de canales por `(channel, normalized_value)`; nunca borrar ni pisar canales `manual`.
5. Reporte final: creados, reutilizados, canales `needs_review`, fichas sin contacto.

### Backoffice
- Rutas bajo el prefijo Backend existente, server-side listing con el patrón de `standardize-backend-server-side-listings`.
- Filtros: segmento, provincia, `has:<canal>`, estado de canal, `never_contacted`, `last_contacted_before`.
- Ficha: canales (con estado y fuente), vínculos (link a la ficha pública/admin), historial, notas.
- Acciones: WhatsApp (`https://wa.me/<e164 sin +>?text=`), Instagram/Facebook (abre perfil + copia mensaje), email (`mailto:`). Al ejecutar, se crea `crm_interactions` y se actualiza `last_contacted_at`.
- En fichas de Intérprete/Festival del backoffice: bloque "Contactos" con link al CRM y contador.
- Contactos con `status=do_not_contact` u `opt_out_at` no muestran acciones.

### Enriquecimiento
- `crm:extract-candidates` extrae emails/handles/teléfonos del cuerpo de `events` y `festivales` → candidatos `text_extraction`.
- El job de IA (fuera de este cambio, pero contratado aquí) sólo escribe en `crm_channel_candidates` con `source_url` obligatorio.
- Revisión en backoffice: aprobar (crea/actualiza canal `valid`, `verified_at`, `verified_by`) o rechazar.

## Risks / Trade-offs
- Datos personales (Ley 25.326): uso interno, origen trazable, opt-out respetado, sin exposición pública. Revisar con asesoría legal antes de campañas.
- Heurística de teléfonos AR imperfecta → se prefiere `needs_review` antes que inventar un E.164.
- Volumen inicial bajo (~63 contactos): el valor depende del enriquecimiento; priorizar intérpretes por `visitas`.
