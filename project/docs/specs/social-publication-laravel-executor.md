# Publicación en redes ejecutada por Laravel (sin n8n)

Estado: alcance aprobado por el usuario en el chat el 2026-11-20. Implementación en `feature/social-publication-laravel-executor` (desde `dev`). Sin migraciones, sin deploy, sin publicaciones reales durante el desarrollo.

## 1. Contexto y problema

Noticias se publican a diario en la Página de Facebook (11:00) y en Instagram con un workflow de n8n que corre en el Docker local del responsable. El portal ya tiene el registro (ledger) con reservas, intentos, hashes y estados inciertos, el render de placas v4 y la cola automática desde la noticia 833; n8n solo orquesta los pasos y hace las llamadas a Meta. Si la PC está apagada a la hora prevista, no se publica.

## 2. Actores

Responsable editorial/administrador, programador de tareas de Laravel (`schedule:run` en Hostinger), API Graph de Meta.

## 3. Alcance

- Ejecutor en Laravel que replica el protocolo validado de n8n (Graph v27.0): Facebook foto multipart a `/{page}/photos`; Instagram contenedor → espera de `FINISHED` → registro del contenedor → `media_publish` → verificación de permalink.
- Usa los mismos métodos del ledger (`claim`, `render`, `begin`, `instagramContainer`, `result`); los intentos quedan con `executor=portal`.
- Interruptor de ejecutor (`n8n` | `laravel`): por defecto `n8n`, así el deploy no cambia el comportamiento productivo.
- Horario configurable desde el backoffice por tipo (Noticias, Eventos) y red (Facebook, Instagram), con activación individual. Valores iniciales: Noticias FB e IG 11:00; Eventos desactivados (18:00).
- Pausa general desde el panel.
- Eventos: misma operatoria que Noticias (cola automática + envío manual). Cola: eventos públicos y vigentes/próximos, con ID mayor a `SOCIAL_PUBLICATION_EVENTS_AFTER_ID`, sin envío previo a ese destino, por fecha de inicio. Instagram de eventos requiere éxito previo en Facebook, igual que Noticias.
- Envío a demanda: elegir tipo, red (FB, IG o ambas) y contenido entre los no enviados de la cola; vista previa de placa y texto antes de confirmar.
- Log: listado de publicaciones (todas las redes y tipos) con fecha/hora, estado, intentos, link al post, ejecutor y filtros; registro de corridas (incluidas las de cola vacía) en `audit_logs`.
- Token de Meta guardado cifrado (Crypt/APP_KEY) en `storage/app/private/social-publications-meta.json`, cargado desde el panel o por artisan; nunca se muestra ni se loguea. Instagram usa su token propio o, si no hay, el de Facebook.
- Avisos por email ante fallo, resultado incierto o error de corrida, y chequeo diario del token.
- Reconciliación programada de reservas vencidas (`social-publications:reconcile`).

Fuera de alcance: Grupos de Facebook (la API `publish_to_groups` fue eliminada por Meta), exclusión de contenidos de la cola (no hay campo en el modelo), historias/reels, IA, cambios de plantillas.

## 4. Historias

- Como responsable quiero que las publicaciones diarias salgan desde el servidor sin depender de mi PC ni de n8n.
- Como responsable quiero ver qué se publicó, cuándo, en qué red y con qué resultado, con link al post.
- Como responsable quiero publicar a demanda una noticia o evento puntual de la cola, viendo antes la placa.
- Como responsable quiero elegir el horario de cada red para probar cuál rinde más.
- Como responsable quiero enterarme por email si algo falla o si el token dejó de servir.

## 5. Reglas de negocio

1. Solo se ejecuta si `SOCIAL_PUBLICATION_ENABLED=true`, ejecutor = `laravel` y sin pausa. El envío manual requiere ejecutor `laravel` (evita convivir con n8n).
2. Una corrida programada publica como máximo un contenido por tipo y red.
3. Un horario se ejecuta una vez por día, al llegar la hora (zona America/Argentina/Buenos_Aires), con tolerancia de 3 horas si el cron se demora; fuera de esa ventana se saltea.
4. Si en el mismo minuto vencen FB e IG del mismo tipo, primero FB.
5. Envío manual de un contenido no siguiente en la cola: los anteriores siguen en la cola (opción a).
6. Clasificación de resultados igual que n8n: FB éxito solo con `post_id` de la Página; rechazo 4xx (≠408) con error → fallo definitivo; resto → incierto. IG: si falla la creación del contenedor (4xx) o no queda listo, no se llamó a `media_publish` → fallo definitivo; si `media_publish` o la verificación del permalink no confirman → incierto. Un incierto nunca se reenvía sin conciliación manual.
7. Error antes de `begin` → no hubo envío; la reserva vence sola y el contenido vuelve a la cola.

## 6. Criterios de aceptación

- Dado ejecutor `n8n`, cuando corre el programador, entonces no se llama a Meta.
- Dado ejecutor `laravel` y Noticias FB a las 11:00, cuando son las 11:00, entonces se publica la primera candidata y queda `success` con ID de post; a las 11:01 no se repite.
- Dado éxito en FB y horario IG vencido, entonces se publica la misma noticia en IG con permalink verificado.
- Dado un rechazo 4xx de Meta, entonces el destino queda `failed` con reintento diferido y se envía un aviso.
- Dada una respuesta 5xx/timeout, entonces queda `uncertain`, se avisa y no se reenvía.
- Dado el envío manual de la noticia N (no la primera), entonces se publica N y las anteriores siguen en la cola.
- Dado un usuario no administrador, entonces no accede al panel.

## 7. Casos borde

Cola vacía (corrida registrada sin envío); token ausente o inválido (no hay claim, aviso); contenido modificado entre reserva y envío (el ledger lo bloquea); corrida simultánea manual + programada (lock de caché y reservas transaccionales); fuentes del renderer ausentes (error antes de `begin`).

## 8. Impacto

Técnico: sin cambios de esquema; nuevos servicios, comandos y vistas. Performance: el programador consulta un JSON por minuto; las corridas son livianas. Seguridad: token cifrado fuera de git; panel solo para `administrador`. SEO/editorial: sin cambios en rutas públicas.

## 9. Dependencias y transición

Deploy → cargar token en el panel → verificar → desactivar el workflow de n8n → pasar ejecutor a `laravel`. Requiere `schedule:run` cada minuto (ya existe) y las fuentes del renderer configuradas (ya validadas en producción).

## 10. Validación

Tests Feature con `Http::fake()` y renderer simulado (sin Meta real), revisión del panel en navegador local. Prueba real en producción solo con autorización explícita.
