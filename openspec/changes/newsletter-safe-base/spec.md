# Spec: newsletter-safe-base (Fase 1 del newsletter propio)

Rama: `feature/newsletter` (desde `dev`). Aprobación: explícita del usuario ("avancemos en las etapas que propones y arranquemos con lo de alta prioridad").
Fases siguientes (otras specs): 2 control editorial (ediciones en borrador, vista previa, envío de prueba, aprobación); 3 métricas, rebotes y plantilla.

## Contexto y problema (confirmado en el código)
- `POST /newsletter/subscribe` deja `active` a cualquier email, sin confirmar; además reactiva a quien se dio de baja, sin que el dueño del correo lo pida; revela si un email ya estaba suscripto; sin límite de intentos ni campo trampa.
- La baja es un GET que actúa de inmediato (los filtros de correo la pueden disparar) y el mail no lleva `List-Unsubscribe`.
- `newsletter:send-weekly` está programado cada lunes 10:00, sin revisión humana ni registro de envíos (si corre dos veces, duplica).
- 40 suscriptores locales (todos `active`, origen sidebar), muchos spam; ninguno confirmó.
- Se desconoce si producción envía hoy (el usuario cree que no).

## Alcance
1. **Doble confirmación (double opt-in).** Alta nueva → `pending` + mail con enlace firmado de 7 días; recién al confirmar pasa a `active` y se registra `confirmed_at`. Aplica al formulario público y a «Mis datos» (el email de la cuenta no está verificado).
2. **Sin reactivación ajena.** Quien se dio de baja solo vuelve a estar activo confirmando desde su correo. Mensaje neutro igual para todos los casos (no revela si el email existe).
3. **Constancia de consentimiento:** `consented_at`, `confirmed_at`, `consent_ip_hash` (hash, no IP en claro), `source`.
4. **Control de abuso:** límite de intentos por IP, campo trampa (compatible con la caché de página completa) y un solo mail de confirmación por dirección cada 10 minutos.
5. **Baja correcta:** GET muestra una página con botón; el POST baja. El POST sin token CSRF atiende el «un clic» de los clientes de correo (RFC 8058: encabezados `List-Unsubscribe` y `List-Unsubscribe-Post`).
6. **Solo se envía a confirmados.** `deliverable()` = `active` + `confirmed_at` no nulo. Los 40 heredados quedan fuera hasta confirmar.
7. **Registro de envíos** (`newsletter_deliveries`, único por suscriptor y edición): idempotencia y base de las métricas de la Fase 3.
8. **Envío automático retirado del scheduler.** El comando `newsletter:send-weekly` queda para uso manual, con `--dry-run`; la Fase 2 reincorpora el envío programado solo para ediciones aprobadas.
9. **Backoffice (Suscriptores):** estado «Sin confirmar» (pendientes y heredados), acción por fila «Pedir confirmación» y «Eliminar» (spam), filtro por estado, ayuda breve. Sin envío masivo de reconfirmación, para no escribir a direcciones basura.

## Fuera de alcance
Ediciones/aprobación (Fase 2); aperturas, clics, rebotes y rediseño de plantilla (Fase 3); proveedor externo de envío; verificación de SPF/DKIM/DMARC (se deja en el checklist).

## Cambios de datos (requieren que el usuario ejecute la migración en `mfa`)
- `newsletter_subscribers`: estado `pending` en el enum (default pasa a `pending`), `consented_at`, `confirmed_at`, `consent_ip_hash`, `last_confirmation_sent_at`. No toca filas existentes.
- Tabla nueva `newsletter_deliveries`.

## Criterios de aceptación
- Dado un email nuevo, cuando se suscribe, entonces queda `pending`, no recibe el newsletter y recibe un mail con enlace firmado; al confirmar queda `active` con fecha.
- Dado un email dado de baja, cuando alguien lo ingresa en el formulario, entonces sigue dado de baja hasta confirmar desde su correo.
- La respuesta del formulario es la misma exista o no el email.
- Con el campo trampa completo o superado el límite no se crea ni se envía nada.
- El mail semanal lleva `List-Unsubscribe` y `List-Unsubscribe-Post`; el POST de baja funciona sin CSRF; el GET no da de baja.
- El comando no envía a `pending`, `unsubscribed`, `bounced` ni heredados sin confirmar, ni dos veces la misma edición a la misma persona.
- Administrador puede pedir confirmación y eliminar un suscriptor.

## Casos borde
Enlace vencido o alterado; confirmar dos veces; email con mayúsculas; usuario logueado cuyo email ya figura; fallo de SMTP al enviar la confirmación (se registra, la respuesta sigue siendo neutra); suscriptor eliminado con enlace viejo.

## Impacto
Performance: sin cambios en el front (el formulario sigue sirviéndose desde la caché). SEO: sin impacto (las páginas de confirmar/bajar son `noindex`). Seguridad/legal: reduce suscripciones de terceros y deja constancia de consentimiento.

## Validación prevista
Tests Feature (alta, confirmación, baja, abuso, entregables, comando, backoffice) en `mfa_test`; revisión manual en Mailpit.

## Resultado
Implementada en `feature/newsletter`. `NewsletterSafeBaseTest` (17 casos); mfa_test: 695 passed, 2 skipped. Hallazgo adicional corregido: `/admin/newsletter-subscribers` y sus acciones solo estaban detrás de `auth` (cualquier usuario con sesión veía los correos); ahora `role:administrador`. Las migraciones se aplicaron únicamente en `mfa_test`; `mfa` queda a cargo del usuario.
