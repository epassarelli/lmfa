# Cambio: contact-form-overhaul

Estado: APROBADA por el usuario en el chat (2026-10-12/13: "revises, arregles y tal vez hasta mejores el formulario de contacto ... que quede visible desde el Front"). Implementada en local; la migracion se ejecuta con confirmacion.

## Diagnostico (confirmado en codigo y base)
1. **La tabla `contact_messages` no existe** en la base local ni en la de tests (ninguna migracion la crea; el modelo supone una tabla heredada): enviar el formulario da error 500. En produccion puede existir como tabla heredada con columnas `id, nombre, email, asunto, mensaje, fecha_envio` (a verificar con `DESCRIBE`).
2. Errores de envio de correo **tragados** (`catch` vacio): nadie se entera si falla.
3. Captcha estatico "3 + 4" (visible en el HTML), sin honeypot, sin limite de envios.
4. El autorrespondedor envia correo a **cualquier direccion** escrita en el formulario (vector de spam a terceros).
5. Los mensajes se guardan pero **no hay donde leerlos**; el correo del equipo esta fijo en el codigo; el correo no tiene Reply-To.
6. Formulario con clases de **Bootstrap** en un front Tailwind (contaminacion segun AGENTS.md); campos sin label; sin consentimiento ni enlace a privacidad.
7. **Sin ningun enlace** al formulario en el menu ni en el footer (solo en el sitemap y una publicidad lateral).
8. Codigo heredado sin uso: `ContactRequest` y `Contact` (tabla `contacts`, tampoco existe). No se tocan (observacion).

## Alcance
**Datos:** migracion defensiva `contact_messages`: crea la tabla si falta; si existe, agrega solo las columnas que falten (`status`, `user_id`, `ip_hash`, `read_at`, `answered_at`, `spam_reason`).

**Formulario publico (`/contacto`):**
- Un solo campo "Nombre y apellido", email, asunto (lista cerrada), mensaje (10 a 5000), **consentimiento** con enlace a la politica de privacidad; labels y atributos de accesibilidad; HTML en Tailwind; metadatos SEO; prellenado si hay sesion.
- Anti-spam sin captcha visible: **honeypot** (se descarta en silencio), **trampa de tiempo** (token firmado: minimo 4 s y maximo 2 h, con mensaje claro), **limite por IP** (5 envios cada 10 min), **limite por email** (3 por hora), **deteccion de duplicados** (mismo email y texto en 10 min) y **filtro de enlaces** (mas de 3 URL => se guarda como `spam`, sin correos).
- Se guarda siempre primero; los correos son best-effort y **los fallos se registran en el log** (el mensaje queda en el backoffice, nada se pierde). El usuario ve una confirmacion en pantalla.
- Correo al equipo: destinatario configurable (`CONTACT_NOTIFY_TO`, por defecto el actual), con **Reply-To** del remitente y enlace al mensaje en el backoffice. Confirmacion al remitente configurable (`CONTACT_SEND_CONFIRMATION`) y protegida por los limites anteriores.
- Se guarda un **hash** de la IP (HMAC con la clave de la app), nunca la IP.

**Backoffice (solo administrador):** "Mensajes de contacto": listado con filtros por estado y busqueda, detalle (marca como leido al abrirse), cambio de estado (nuevo, leido, respondido, spam), **responder por correo** (enlace `mailto:` con "Re: asunto") y **eliminar** (derecho de supresion). Contador de mensajes nuevos en el dashboard y en el listado. Item de sidebar solo para administradores.

**Visibilidad en el front:** enlace "Contacto" en el menu (Mas) y en el footer, mas boton "Escribinos" en el bloque Contacto del footer. `/contacto` ya figuraba en el sitemap y esta excluida de la cache de pagina.

## Fuera de alcance
Captcha de terceros (Turnstile/hCaptcha: requiere claves y dependencia; se puede sumar), envio por cola (no hay evidencia de worker en produccion), respuesta desde el backoffice (se responde desde el correo), adjuntos, retiro del codigo heredado `Contact*`.

## Archivos
database/migrations/*_create_or_extend_contact_messages_table.php, config/contact.php, app/Models/ContactMessage.php, app/Services/ContactFormGuard.php, app/Http/Requests/Frontend/ContactMessageRequest.php, app/Http/Controllers/Frontend/ContactoController.php, resources/views/frontend/contacto/contacto.blade.php, app/Mail/Contact*Email.php + vistas de correo, app/Http/Controllers/Backend/ContactMessageController.php + vistas, routes, config/adminlte.php, Dashboard, footer y navbar, .env.example, tests/Feature/ContactFormTest.php.

## Rendimiento y SEO
`/contacto` sin cache de pagina (formulario con token); sin JS propio. Titulo, descripcion y canonical definidos. Cambio de clases Tailwind: se recompila `public/build` (flujo del proyecto).

## Validacion
`ContactFormTest` y suite completa en `mfa_test`.

## Riesgos
- Produccion: la tabla puede existir con otro esquema; la migracion solo agrega columnas faltantes y **no modifica ni borra** lo existente. Verificar con `DESCRIBE contact_messages` antes de desplegar (checklist). Desplegar con la migracion ejecutada antes del codigo nuevo (el dashboard consulta la tabla).
- Sin captcha de terceros puede pasar spam sofisticado: queda el panel para marcarlo y un limite por IP/email; se puede sumar Turnstile si hace falta.

## Resultado (implementado en local, 2026-10-13)
- Migracion defensiva aplicada en `mfa_test`; probada la ruta de tabla heredada (rollback deja las 6 columnas originales con un mensaje; al volver a migrar se conserva y se agregan las 6 nuevas, los mensajes previos quedan como `new`). **Pendiente en `mfa` (base real local):** confirmacion del usuario.
- `ContactFormTest` (25 casos). Suite en base limpia: 616 pasan, 0 fallan. Tres tests de `SeoInfrastructureTest` usaban `/contacto` como pagina de muestra del canonical por defecto del layout; ahora usan `/avisos-clasificados` (el contacto declara un canonical limpio, sin parametros).
- `public/build` recompilado (3 CSS + manifest); `npm run build:verify` y `test:build-artifact` pasan.
- Observaciones: codigo heredado sin uso `ContactRequest`/`Contact` (tabla `contacts` inexistente); el envio de correo es sincrono (sin worker de cola confirmado).
