# Spec: newsletter-clicks-and-bounces (Fase 3 del newsletter propio)

Rama: `feature/newsletter`. Depende de Fases 1 y 2. Aprobación: explícita del usuario (elige "Clics por enlace" y "Rebotes"; descarta aperturas y plantilla con imágenes por ahora).

## Problema
No se sabe qué contenido interesa a los lectores ni se protege la reputación del remitente: una dirección inexistente se sigue escribiendo cada semana.

## Alcance
### Clics por enlace
- Al **aprobar** una edición se crea un registro por cada enlace del contenido congelado (`newsletter_links`: edición, URL, título, sección, contador de clics). El correo enviado usa un enlace propio `/newsletter/c/{edición}/{enlace}` que suma un clic y redirige (302) a la URL guardada.
- **Privacidad:** solo contadores agregados por enlace; no se registra quién hizo clic ni su IP. Vista previa y pruebas usan los enlaces directos (no cuentan).
- La redirección solo apunta a URLs guardadas por el sistema al aprobar (no hay parámetro libre: sin *open redirect*). No se cachea ni se indexa. Las solicitudes HEAD no cuentan.
- El administrador ve, en cada edición aprobada/enviada, los clics por enlace y el total.
- El formulario de suscripción avisa que se mide qué enlaces se abren, sin identificar a la persona.

### Rebotes
- Al enviar, si el servidor de correo rechaza **esa dirección** de forma definitiva (550/551/553 o 5.1.x con "usuario inexistente", "buzón no existe", "destinatario inválido"), la dirección pasa a `bounced` y no se reintenta ni se vuelve a escribir. Los rechazos de política (554, 5.7.x, "spam", "blocked") y los errores temporales (4xx, timeouts) **no** marcan nada y se reintentan: nunca se da de baja a toda la lista por un problema del remitente.
- El administrador puede marcar a mano una dirección como rebotada (p. ej. tras recibir un aviso de rechazo) y ve la cantidad de rebotados.

## Fuera de alcance
Aperturas (píxel); rebotes asíncronos leídos desde un buzón (DSN); quejas de spam (FBL); plantilla con imágenes; uniques por persona.

## Reglas
- Un enlace solo existe para ediciones aprobadas; volver a borrador elimina sus enlaces.
- Un `bounced` no recibe envíos ni confirmaciones y solo lo reactiva un administrador eliminando el registro.
- El contador nunca decrece; las ediciones enviadas no cambian su contenido.

## Criterios de aceptación
- Dada una edición aprobada, cuando se abre un enlace del correo, entonces se suma un clic y se redirige a la URL original; un identificador inexistente o de otra edición da 404.
- Dado un HEAD, no suma. Dada una edición en borrador, el correo de prueba usa enlaces directos.
- Dado un rechazo "user unknown" (550 5.1.1) al enviar, entonces la dirección queda `bounced` y no se reintenta; dado un 554 de política o un 421 temporal, no se marca y el job falla para reintentarse.
- El administrador ve los clics por enlace y puede marcar un rebotado.

## Casos borde
Escáneres de correo que abren enlaces (los clics pueden inflarse; se documenta); URL con parámetros; edición eliminada con enlaces (cascada); mismo enlace dos veces en una edición (dos filas).

## Impacto
Datos: tabla nueva `newsletter_links` (migración a ejecutar por el usuario en `mfa`). Rendimiento: una escritura atómica por clic. SEO: sin impacto (noindex, fuera de caché). Legal: avisar el rastreo en el texto de suscripción; **actualizar la política de privacidad queda a revisión del titular**.

## Validación prevista
Tests Feature: aprobación crea enlaces, redirección y conteo, HEAD, 404, borrador sin tracking, detector de rebotes (duros y blandos), marcado manual, permisos.

## Resultado
Implementada en `feature/newsletter`: `NewsletterClicksAndBouncesTest` (12 casos). Migración `2026_10_14_120000` aplicada en `mfa_test`.
