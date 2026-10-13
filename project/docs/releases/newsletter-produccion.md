# Newsletter — puesta en producción

Estado: código integrado en `dev` (Fases 1 a 3). Esta guía cubre lo que **no** se puede resolver desde el código: correo saliente, DNS, cola y primeras pruebas. Nada de esto se ejecutó en producción.

## 0. Antes de integrar la rama

- [x] Arreglo de seguridad del listado de suscriptores: **ya está en `dev`** (rutas bajo `role:administrador`, con test). Producción lo recibe al desplegar; hasta entonces cualquier usuario con sesión ve los correos de los suscriptores.
- [ ] Migraciones nuevas del newsletter (en este orden, todas aditivas; `migrate:status` primero):
  `2026_10_14_100000_add_double_optin_to_newsletter_subscribers`, `2026_10_14_100100_create_newsletter_deliveries_table`, `2026_10_14_110000_create_newsletter_editions_table`, `2026_10_14_120000_create_newsletter_links_table`.
  La primera cambia el enum `status` (agrega `pending`) y no toca filas; los suscriptores existentes quedan activos **sin confirmar**, o sea no reciben nada hasta confirmar.
- [ ] Copia de respaldo de `newsletter_subscribers` antes de migrar (`mysqldump` de esa tabla).
- [ ] `.env`: `NEWSLETTER_MAX_PER_MINUTE` (por defecto 60; ver sección 3), `MAIL_*`, `QUEUE_CONNECTION=database`.

## 1. Correo saliente

1. Definir **qué servicio envía**: el SMTP del hosting, una cuenta de correo del dominio o un servicio de envío (SES, Brevo, Mailgun, etc.). Para menos de 1.000 suscriptores alcanza cualquiera que permita unos 60 correos por minuto y 500–1.000 por día; **conviene confirmar el límite del plan** (los hostings compartidos suelen limitar por hora).
2. `.env` de producción:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=...        MAIL_PORT=587        MAIL_ENCRYPTION=tls
   MAIL_USERNAME=...    MAIL_PASSWORD=...
   MAIL_FROM_ADDRESS="newsletter@mifolkloreargentino.com"   # una casilla real del dominio
   MAIL_FROM_NAME="Mi Folklore Argentino"
   ```
   El valor por defecto (`hello@example.com`) **no sirve**: los proveedores rechazan o mandan a spam.
3. Las respuestas de los lectores llegan a la casilla de `MAIL_FROM_ADDRESS`: que alguien la lea.

## 2. DNS del dominio remitente (SPF, DKIM, DMARC)

Sin esto, Gmail y Outlook mandan el newsletter a spam o lo rechazan. Se configura en el panel del dominio (DNS):

| Registro | Qué es | Cómo se obtiene |
|---|---|---|
| **SPF** (TXT en el dominio) | Qué servidores pueden enviar en nombre del dominio | Lo indica el proveedor de correo, ej. `v=spf1 include:<proveedor> ~all`. Debe haber **un solo** registro SPF. |
| **DKIM** (TXT con un *selector*) | Firma digital de cada correo | El proveedor lo genera y te da el registro a pegar. |
| **DMARC** (TXT en `_dmarc.<dominio>`) | Qué hacer si falla SPF/DKIM y a dónde reportar | Empezar en modo observación: `v=DMARC1; p=none; rua=mailto:<casilla>`. Endurecer más adelante. |

**Cómo verificarlo:** enviar una prueba (Newsletter → Ediciones → enviar prueba) a una cuenta de Gmail, abrir «Mostrar original» y confirmar `SPF: PASS`, `DKIM: PASS` y `DMARC: PASS`. También sirve enviar a mail-tester.com y ver la nota. Repetir con una cuenta de Outlook.

## 3. Cola y programador

- Hay que tener `QUEUE_CONNECTION=database` (las tablas `jobs` y `failed_jobs` ya existen en la base).
- **Cron del servidor** (una vez por minuto): `* * * * * cd /ruta/del/proyecto && php artisan schedule:run >> /dev/null 2>&1`. El programador ya ejecuta `queue:work --stop-when-empty` cada 5 minutos, así que con el cron **no hace falta** un worker aparte; si se prefiere uno permanente (Supervisor), también funciona.
- El programador ejecuta además: `newsletter:build-draft` (lunes 07:00, arma el borrador, **no envía**) y `newsletter:dispatch-scheduled` (cada 15 min, envía solo ediciones **aprobadas** con hora cumplida).
- **Ritmo:** `NEWSLETTER_MAX_PER_MINUTE` limita los envíos por minuto; lo que excede vuelve a la cola y sale después. Con 60 por minuto, 1.000 suscriptores tardan unos 17 minutos. Bajarlo si el proveedor es estricto, subirlo si lo permite. Con el programador cada 5 minutos, los envíos salen en tandas.
- Para ver fallos: el tablero del administrador muestra una alerta si hay trabajos fallidos (`failed_jobs`).

## 4. Prueba de punta a punta (antes del primer envío real)

1. Suscribirse con un correo propio desde el formulario del sitio → llega el correo de confirmación → confirmar → aparece como **Activo** en Suscriptores.
2. Newsletter → Ediciones → «Armar borrador», elegir contenido, **vista previa**, **enviar una prueba** a tu correo.
3. Aprobar y enviar la edición a un grupo mínimo (solo vos confirmado) y comprobar que llega una sola vez, con el botón de baja y que los enlaces suman clics.
4. Probar «Darme de baja» desde el correo (botón y encabezado de baja de Gmail).
5. Recién entonces, invitar a los suscriptores heredados (ver sección 5).

## 5. Suscriptores heredados (los 40 actuales)

Siguen **activos sin confirmar**: no reciben nada. En Newsletter → Suscriptores filtrá «Sin confirmar»; para los que parezcan reales usá «Pedir confirmación» (uno por uno) y eliminá el spam. No hay envío masivo a propósito: escribir a direcciones basura daña la reputación del remitente.

## 6. Privacidad

La política (`/privacidad`) ya incluye la sección 9 «Newsletter» (doble confirmación, baja, medición agregada de clics, hash de IP) y la autoridad de control de la Ley 25.326. **Revisión legal del titular pendiente** antes de publicar: es un texto jurídico redactado a partir de lo que hace el sistema.

## 7. Reversa

| Problema | Qué hacer |
|---|---|
| Se quiere frenar todo envío | No hay nada que pausar solo: nada se envía sin aprobar una edición. Dejar las ediciones en borrador. |
| Un envío sale mal | Las ediciones enviadas no se pueden reenviar; corregir y armar otra. Los envíos pendientes se vacían quitando los trabajos de `jobs`. |
| Rebota mucho / cae en spam | Revisar SPF/DKIM/DMARC y bajar `NEWSLETTER_MAX_PER_MINUTE`. |
| Revertir migraciones | Los `down()` existen; el de la primera quita `pending` (los pasa a `unsubscribed`). Preferible restaurar el respaldo de la tabla. |
