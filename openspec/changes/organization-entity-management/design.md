## Contexto

Tres capas que no deben mezclarse:

| Capa | Dónde vive | Visibilidad |
|---|---|---|
| Perfil público (redes, web, teléfono de la ficha) | En la entidad (intérprete, festival), sin cambios | Público |
| Administración (quién puede editar) | `organization_members` + `organization_entities` | Interna |
| Contacto promocional/comercial | Campos de `organizations` (email, phone, consentimiento) | Interna |

Los datos públicos de la ficha y los de la organización **pueden diferir** (una productora con 3 artistas no comparte los mails de cada ficha). Por eso no se migra nada de `interpretes`.

## Decisiones

### D1. Pivote polimórfica `organization_entities`
- Columnas: `id`, `organization_id` (FK, cascade), `entity_type` (FQCN, mismo criterio que `social_accounts.owner_type`), `entity_id` (unsigned bigint, sin FK por ser polimórfica), `is_primary` (bool, default false), timestamps.
- `UNIQUE (organization_id, entity_type, entity_id)` e índice `(entity_type, entity_id)` para resolver "¿quién administra a X?" y "¿qué administra el usuario?" con joins indexados.
- Una entidad puede tener varias organizaciones; una organización, varias entidades.

### D2. Columnas nuevas en `organizations`
`contact_consent_at`, `contact_consent_purpose` (string 100), `unsubscribed_at`, `source` (string 50: `manual`, `email_reply`, `claim`, `import`), `verified_at`, `verification_method` (string 30: `email_match`, `code`, `social_proof`, `manual`), `verified_by` (FK users, nullOnDelete). `is_verified` ya existe y se mantiene como bandera derivada.

### D3. Regla de autorización (`User::canManage($entity)`)
Devuelve verdadero si se cumple alguna:
1. Rol `administrador`.
2. Acceso legacy: la entidad tiene `user_id == $user->id`. Se conserva exactamente el comportamiento previo de las policies (el default `user_id = 1` corresponde al administrador, que ya tiene acceso total).
3. Membresía en `organization_members` con `status = active` y `role IN (owner, editor)` de una organización con `status = active` vinculada a la entidad vía pivote.

`role = member` es solo lectura; `pending` e `inactive` no otorgan nada.

### D4. Listados
Scope `managedOrLegacyBy($user)`: entidades con `user_id` del usuario más las administradas por membresía (`whereIn` con subconsulta indexada sobre pivote y membresía, sin N+1).
- **Festivales:** el listado ya limita a `colaborador`/`prensa` por `user_id`; se reemplaza por el scope, que es **aditivo** (suma las administradas). No requiere flag.
- **Intérpretes:** hoy todos ven todo; el recorte es nuevo y queda detrás del flag `entity_management.scope_listings` (default `false`).

No se cachea nada autenticado.

### D5. Creación por colaboradores
Se mantiene el permiso `create` por rol. Al crear una entidad un no administrador, se guarda `user_id` (acceso legacy) y se registra la organización con el aprovisionador, para que el vínculo nuevo exista desde el primer día.

### D6. Aprovisionador (`OrganizationProvisioner`)
`forEntity($entity, ?User $owner, array $attrs)`: crea una organización `type` según la entidad (`artista`, `festival`, `peña`, `radio`), `name` = nombre de la entidad, `slug` único, y la vincula con `is_primary = true`; si hay `$owner`, crea membresía `owner`. Idempotente: si la entidad ya tiene organización primaria, la devuelve.

### D7. Sin migración masiva
Los 445 intérpretes y 47 festivales no reciben organización hasta que haga falta (importación de contactos, reclamo de perfil, difusión). "Toda entidad tiene al menos una organización" es una **regla de aplicación en los flujos nuevos**, no una restricción de base.

## Riesgos

- `organizations` nunca se probó en producción: validar con un caso real antes de apoyar flujos de usuario sobre ella.
- Cambiar `update` de las policies afecta a 423 colaboradores: se preserva el acceso legacy y se agregan tests que fijan el comportamiento previo.
- Restringir listados puede sorprender a colaboradores que hoy ven todo: confirmar con el usuario antes de activar en producción (se puede controlar con un flag de config `entity_management.scope_listings`, default `false`, activado primero en local).
- Colisión con la rama de eventos: se evita tocar `Event*`, `AuthServiceProvider`, `routes/*` y vistas de eventos en este slice. Las policies se registran con el mapeo existente (ya apuntan a `InterpretePolicy`/`FestivalPolicy`).

## Performance y SEO

- Sin impacto en rutas públicas ni sitemap.
- La verificación de permiso hace una consulta indexada por request; se memoiza en el usuario para evitar repetirla por ítem de un listado.

## Preguntas abiertas

1. **Relación con `crm-contact-directory`.** Esa spec define tablas propias `crm_*` (contactos, canales, interacciones) como CRM interno para leads aún no registrados, y dice no reutilizar `social_accounts`. La decisión del usuario fue concentrar el contacto en `organizations`. Opciones: (a) CRM solo para seguimiento de leads/interacciones y `organizations` para partes ya identificadas; (b) descartar el CRM y registrar interacciones sobre `organizations`; (c) mantener ambos como están. Pendiente de definición humana; este cambio no depende de ella.
2. Contactos adicionales por organización (prensa vs comercial): hoy hay un solo `email`/`phone`. Se resuelve más adelante.
3. ¿Se activa el recorte de listados para colaboradores de inmediato o después de probar el flujo de reclamo?
