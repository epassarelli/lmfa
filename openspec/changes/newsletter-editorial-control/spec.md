# Spec: newsletter-editorial-control (Fase 2 del newsletter propio)

Rama: `feature/newsletter`. Depende de `newsletter-safe-base` (Fase 1). Aprobación: el usuario pidió seguir con las etapas propuestas ("continua con lo siguiente").

## Problema
Fase 1 dejó el envío semanal en manual y sin revisión: el comando elegía el contenido solo y lo mandaba. Nadie ve ni aprueba qué sale, y el armado usa la fecha de creación (una noticia publicada tarde queda afuera) y no incluye la agenda de eventos, lo más útil para esta audiencia.

## Alcance
1. **Ediciones** (`newsletter_editions`): estados `draft` → `approved` → `sent`. Cada edición tiene asunto, introducción editable, período, selección de contenido y registro de quién la aprobó y cuándo.
2. **Borrador automático:** un comando semanal arma el borrador de la semana (nunca envía). Contenido candidato: noticias publicadas en el período (por fecha de publicación), agenda de eventos de los próximos 14 días, discos, canciones y artistas nuevos y visibles. La selección inicial es sugerida y el administrador marca o desmarca cada ítem.
3. **Vista previa** en el navegador (mismo HTML que el correo) y **envío de prueba** a una dirección elegida (con «[PRUEBA]» en el asunto, sin registrar envíos).
4. **Aprobación explícita.** Al aprobar se congela una copia del contenido (título, enlace, dato breve) para que lo enviado sea exactamente lo revisado. Se puede volver a borrador mientras no se haya enviado.
5. **Envío:** «Enviar ahora» o programado (`scheduled_at`); un comando cada 15 minutos despacha solo ediciones **aprobadas** cuya hora llegó. Solo a suscriptores confirmados, sin duplicados (registro de Fase 1). Una edición enviada es inmutable y no se envía dos veces.
6. **Backoffice** (solo administradores): Newsletter → Ediciones y Suscriptores, con ayuda breve; contadores de destinatarios y enviados.
7. El comando manual `newsletter:send-weekly` de la Fase 1 se reemplaza por `newsletter:build-draft` y `newsletter:dispatch-scheduled`.

## Fuera de alcance
Imágenes y rediseño de la plantilla, aperturas/clics y rebotes (Fase 3); segmentación; editor de texto libre del cuerpo (la edición cura ítems, asunto e introducción).

## Reglas
- No se aprueba una edición sin asunto o sin al menos un ítem seleccionado.
- Un ítem que dejó de ser público antes de aprobar se omite; el contenido aprobado queda congelado.
- Solo `approved` se puede enviar o programar; `sent` no se edita ni se elimina; un borrador se puede eliminar.
- Una sola edición por período (`edition_key` único).
- Todo el módulo es solo para `administrador`.

## Criterios de aceptación
- Dado el comando semanal, cuando corre, entonces crea un borrador con candidatos y no envía nada; si ya existe el de ese período, no lo duplica.
- Dado un borrador, cuando el administrador envía una prueba, entonces solo esa dirección recibe el correo con «[PRUEBA]» y no se registran envíos.
- Dada una edición aprobada, cuando se envía, entonces la reciben únicamente los confirmados, una vez, y queda `sent` con la cantidad.
- Dada una edición en borrador o enviada, cuando se intenta enviar, entonces no sale nada.
- El correo enviado coincide con la copia congelada al aprobar, aunque el contenido original cambie después.
- Un usuario sin rol de administrador no accede a nada de esto.

## Casos borde
Sin contenido en la semana; cero destinatarios confirmados; ítem despublicado entre la aprobación y el envío; reintentos del job; aprobar dos veces; programar en el pasado (se envía en la próxima pasada).

## Impacto
Cambios de datos: tabla nueva `newsletter_editions` (migración a ejecutar por el usuario en `mfa`). Sin impacto en el front público ni en SEO. Performance: consultas acotadas a una semana; el envío usa la cola.

## Validación prevista
Tests Feature (borrador, selección, prueba, aprobación, envío, programación, permisos) en `mfa_test` y revisión manual en Mailpit.

## Resultado
Implementada en `feature/newsletter`: `NewsletterEditionTest` (13 casos) y `NewsletterSafeBaseTest` actualizado. La migración `2026_10_14_110000` se aplicó solo en `mfa_test`.
