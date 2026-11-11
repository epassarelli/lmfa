# Spec: mail de reactivación de usuarios (envío manual desde el backoffice)

Estado: alcance definido por el usuario en el chat (2026-11-11); **la migración requiere confirmación explícita antes de aplicarse** (ver «Base de datos»). Sin envío a usuarios reales por iniciativa del agente.

## Contexto y problema
Hay ~449 usuarios activos y muy pocos ingresan. El login con Google/Facebook hoy no funciona, de modo que quienes se registraron por esa vía no pueden entrar. Se quiere un mail de «te extrañamos» que cuente las ventajas de la comunidad (gestión de contenidos, newsletter) y ofrezca recuperar la contraseña.

## Datos relevantes (lectura de la base local, 2026-11-11)
- 442 de 449 usuarios activos tienen `last_login_at` vacío (campo que no se registraba antes o cuentas que nunca entraron) y la mayoría tampoco tiene `created_at`.
- Solo 15 tienen el email verificado; 3 tienen `google_id` y ninguno `facebook_id`.

## Reglas de negocio (decididas por el usuario)
1. **Destinatarios (opción B):** usuarios `status = 'active'` con email, que no se dieron de baja de estos mails, y que cumplan una de:
   - ingresaron por última vez hace más de 30 días (variante `lapsed`: «te extrañamos»), o
   - nunca ingresaron (`last_login_at` nulo) y su cuenta no es de los últimos 30 días (variante `never`: «tu cuenta te espera»).
2. **Frecuencia:** un mismo usuario no recibe más de un mail cada 30 días (ni los que están en cola ni los enviados; los fallidos pueden reintentarse en la siguiente tanda).
3. **Baja:** cada mail lleva un enlace firmado de baja (y los encabezados `List-Unsubscribe`/One-Click). La baja es permanente para este tipo de mail y no afecta al newsletter.
4. **Disparo manual** desde el backoffice (solo administradores), con vista previa, envío de prueba al propio administrador, confirmación antes de enviar e historial. Cuando pasaron 30 días desde el último envío (o nunca se envió) y hay destinatarios, aparece un aviso en la pantalla, en el menú lateral y en el tablero.
5. **Envío en cola** con tope por minuto (mismo criterio que el newsletter) para respetar los límites del proveedor de correo.
6. El mail incluye: botón de ingreso, botón **Recuperar contraseña** (`password.request`) con la explicación de que el ingreso con Facebook/Google no está disponible por ahora, y enlace de baja.

## Fuera de alcance
Envío automático programado, segmentación editorial, métricas de apertura/clic, arreglo del login social (es la próxima tarea).

## Base de datos (requiere confirmación)
Migración aditiva `2026_11_12_100000_add_reengagement_mail_support`:
- `users.reengagement_opt_out_at` (timestamp nulo).
- Tabla `reengagement_mail_sends` (id, batch_uuid, user_id, email, variant, status, sent_at, error, triggered_by, timestamps; índices por lote y por usuario+fecha).
`down()` revierte ambas. No toca datos existentes.

## Archivos
Migración; `config/reengagement.php`; `User` (cast y scope); `App\Services\ReengagementService`; `App\Jobs\SendReengagementMailJob`; `App\Mail\ReengagementMail` + `mail/reengagement.blade.php`; `Backend\ReengagementController` + vista; `Frontend\ReengagementUnsubscribeController` + vista; rutas web y admin; menú AdminLTE; `AdminAttentionService` (insignia y tarjeta); `VerifyCsrfToken` (baja de un clic); tests.

## Criterios de aceptación
- Dado un usuario activo que nunca ingresó y con cuenta de más de 30 días, entonces es elegible (variante `never`).
- Dado un usuario que ingresó hace 10 días, o que se dio de baja, o que recibió un envío hace menos de 30 días, entonces no es elegible.
- Cuando el administrador confirma el envío, se crea un registro por destinatario y se despacha un job por cada uno; una segunda confirmación inmediata no envía de nuevo.
- Solo los administradores acceden a la pantalla; un usuario común recibe 403.
- El enlace de baja firmado marca la baja; sin firma válida responde 403.
- Pasados 30 días del último envío con destinatarios elegibles aparece el aviso.

## Impacto
- SEO: ninguno (rutas públicas de baja con `noindex`). Performance: consultas del tablero acotadas y cacheadas con la caché existente de 30 s; el envío va en cola.
- Privacidad: se envía solo a cuentas propias del portal, con baja de un clic; el listado de destinatarios solo lo ve un administrador.

## Validación
Tests Feature con `Mail::fake()`/`Queue::fake()`; el envío real a usuarios requiere decisión humana y se prueba antes con el envío de prueba.
