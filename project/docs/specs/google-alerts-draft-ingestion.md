# Google Alerts → noticias draft mediante la API existente

Fecha: 2026-11-15.
Estado: diseño y prompt preparados; autenticación y prueba pendientes. Sin cambios de aplicación, migraciones, despliegues ni automatizaciones.

## Decisiones vigentes

- Reutilizar POST https://mifolkloreargentino.com/api/v1/news.
- Enviar editorial_status="draft" y published_at=null.
- No desarrollar otro endpoint ni modificar Laravel para la primera prueba.
- Nueva tarea diaria a las 13:00, America/Argentina/Buenos_Aires.
- Hasta 3 noticias válidas por corrida; única fuente de descubrimiento: Gmail/Google Alerts.
- Sin etiqueta adicional ni marca de Google Alerts en título/bajada.
- Revisar y aprobar en el portal; no generar nuevas filas en Sheets.
- Mantener las tareas actuales hasta comprobar el circuito nuevo. Pausar luego únicamente MFA — Noticias desde alertas (6ac0893721e88191a6ea6f3d2dc0e6a8).
- Conservar activa MFA - Ingesta de contenidos nuevos (6a6e0f2ce1e481918a2c262b452ab87b), que descubre desde la web.

## Capacidad actual comprobada en dev

routes/api.php protege POST /api/v1/news con auth:sanctum y role:administrador. NewsController utiliza StoreNewsRequest y NewsService.
El contrato permite title, body y categoria_id, y opcionalmente slug, subtitle, excerpt, editorial_status, published_at, seo_title, meta_description e intérpretes comprobados.
GET /api/v1/news y GET /api/v1/news/{id} permiten verificar el alta autenticada. El listado admite editorial_status y pagina 15 noticias; no se verificaron filtros por URL, título o slug.
No se comprobó idempotencia por encabezado ni unicidad de URL fuente. No asumir esas funciones.
El administrador puede publicar mediante la API; enviar draft es una decisión del cliente. En esta versión el usuario decidió reutilizar el endpoint existente, sin imponer un nuevo desarrollo de permisos como requisito.

## Único bloqueo para el primer envío: credencial utilizable en la tarea

Hace falta una conexión o un ejecutor con almacenamiento privado persistente del Bearer Token Sanctum y capacidad de hacer HTTP autenticado durante una ejecución programada. No basta escribir un token o un curl en el prompt.

En esta sesión no existe una herramienta conectada a MFA ni un formulario de configuración de secretos de la tarea. No se solicitó ni extrajo el token de Apps Script. No guardar tokens en chat, Git, planilla o prompt.
No reutilizar la credencial limitada de publicaciones sociales: ese circuito usa permisos distintos de ingesta.

Posible opción: adaptador MCP autenticado que llame a /api/v1/news con el token guardado en el servidor del adaptador. Esto agrega una integración externa, no un endpoint del portal. Su disponibilidad y funcionamiento sin intervención desde esta cuenta y la ejecución programada NO están confirmados; validar antes de elegirlo.
Si no se puede disponer de una conexión persistente en la tarea, evaluar un ejecutor externo con credenciales privadas. Un flujo n8n o un proceso de servidor serían alternativas de arquitectura, no capacidades ya configuradas.
No imponer MCP/OAuth como condición técnica de la API existente: la API necesita Bearer; el transporte de ese secreto depende del ejecutor.

## Payload mínimo de prueba

```json
{
  "title": "Título original basado en la fuente leída",
  "slug": "slug-unico-de-la-noticia",
  "subtitle": "Bajada editorial",
  "excerpt": "Resumen breve",
  "body": "<p>Texto original con atribución al medio y enlace a la fuente verificada.</p>",
  "categoria_id": 1,
  "editorial_status": "draft",
  "published_at": null,
  "seo_title": "Título SEO",
  "meta_description": "Descripción SEO"
}
```

categoria_id=1 es ilustrativo; elegir el ID real comprobado según el tema. Omitir estado, publicar, created_by, user_id y campos de aprobación.
No enviar source_url, gmail_message_id, copy_facebook o copy_instagram como si estuvieran soportados: el request actual no los declara.
Conservar atribución y URL fuente en el cuerpo HTML y el registro operativo externo disponible.
No inventar imagen ni descargarla automáticamente si no hay un recurso y permisos verificados.

## Duplicados y resultados inciertos sin cambiar la API

Antes del POST, cotejar fuente, hecho, título y slug contra los registros existentes de la bandeja y las noticias autenticadas del portal, incluidos drafts. Utilizar un slug estable para poder localizar una creación ante un timeout; no cambiarlo al reintentar.
La consulta previa reduce duplicados, pero no garantiza exclusión mutua entre tareas ni idempotencia de servidor.
Si se pierde la respuesta de POST, buscar la noticia creada mediante GET y la fuente/título/slug esperados. Si no se puede resolver con certeza, informar INCIERTO y detener ese candidato; no repetir el POST automáticamente.
No enviar Idempotency-Key esperando una garantía que el servidor no implementa.
No exigir migraciones nuevas como condición del MVP. Un registro automático externo puede conservar resultados y errores de lectura; definir su ubicación al seleccionar el ejecutor. No afirmar que ya existe en el portal.

## Prompt preparado para instalar tras verificar la conexión

Operá una corrida de ingesta de NOTICIAS NUEVAS para Mi Folklore Argentino. Usá America/Argentina/Buenos_Aires.

PRECONDICIÓN: debe haber una conexión autenticada utilizable que permita POST /api/v1/news y GET de verificación. El token debe estar configurado privadamente fuera del prompt. Si no hay acceso, detené la corrida e informá el bloqueo; no pidas secretos por chat ni alteres infraestructura o tareas.

DESCUBRIMIENTO: Gmail label:mfa---alertas-de-google newer_than:7d. Etiqueta previamente verificada: Label_3357873914155608530. Revisá hasta 10 correos recientes y abrí como máximo 5 artículos candidatos no procesados. Extraé enlaces de artículos y resolvé el parámetro url de enlaces Google. Quitá seguimiento conocido sin borrar parámetros que identifiquen el artículo. Descartá enlaces administrativos, compartir y desuscripción. No hagas búsquedas web adicionales ni recorras otros artículos. No modifiques, archives o borres correos; no uses leído/no leído como control.

DEDUPLICACIÓN: compará URL fuente, hecho, título y slug con las noticias del portal, incluidos borradores, y con registros anteriores de la bandeja editorial (spreadsheet 1S3_Ukj9oV66SPJw-l0KPlWBg79rO14GBEcPxQl1sZzA, Contenidos e Ingesta_Alertas_Google) mediante lecturas acotadas. No escribas nuevas noticias en Sheets ni cambies los estados de las tareas anteriores. Si no podés comprobar duplicados razonablemente, no envíes a ciegas.

REDACCIÓN: prepará hasta 3 noticias originales en español rioplatense basadas únicamente en la lectura completa de cada artículo. Intentá obtener al menos una cuando las fuentes lo permitan; no fuerces contenido. Incluí título, bajada, HTML sin H1, slug, SEO, categoría comprobada y atribución al medio con enlace fuente. No copies íntegramente ni inventes citas, fechas, IDs, imágenes o derechos. No agregues marcas de Google Alerts. Lanzamientos es una categoría de Noticia; no crees entidades Festival ni hagas refresh. Ante bloqueo/paywall/información insuficiente, descartá o registrá error y continuá con otro candidato.

ENVÍO: usá POST /api/v1/news existente con editorial_status=draft y published_at=null. Omití estado, publicar y campos de autoría/aprobación. No publiques en redes ni ejecutes Apps Script. No envíes campos no soportados. Conservá fuente en el HTML. Verificá cada respuesta por GET /api/v1/news/{id}: ID, título, draft y published_at vacío. Un 2xx sin ID o sin lectura posterior no constituye un alta verificada.

ERRORES: no supongas idempotencia del servidor. Ante timeout, 429 o 5xx de POST, comprobá primero si hubo alta con el mismo slug/fuente/título. Si no podés conciliar, marcá resultado incierto y no reenvíes automáticamente. Ante 422 corregí únicamente datos respaldados; no inventes. Ante 401/403 detené los envíos e informá el problema sin mostrar secretos. Persistí resultados en el registro privado del ejecutor cuando esté configurado; si falla ese registro, conciliá antes de volver a procesar. Máximo dos intentos de lectura por enlace en corridas distintas cuando exista registro persistente.

SALIDA: notificá siempre un resumen breve de correos revisados, artículos leídos, borradores creados con IDs y títulos, duplicados, descartes, errores y pendientes. No afirmes éxito sin verificación ni modifiques otras tareas programadas.

## Configuración exacta preparada, no instalada

Título: MFA — Alertas a borradores.
default_timezone: America/Argentina/Buenos_Aires.
timing_mode: exact_schedule.

```text
BEGIN:VEVENT
DTSTART;TZID=America/Argentina/Buenos_Aires:20261116T130000
RRULE:FREQ=DAILY;BYHOUR=13;BYMINUTE=0;BYSECOND=0
END:VEVENT
```

El DTSTART de ejemplo es la próxima fecha diaria después de la preparación. Al instalar, recalcular si ya pasó; preservar las 13:00 de Argentina. No crear ni activar con la conexión pendiente.
Frecuencia reemplazada por decisión del usuario: 24 horas, no 12.

## Prueba y transición

1. Configurar la conexión/ejecutor y la credencial existente mediante canal privado, sin modificar Laravel.
2. Verificar acceso autenticado de lectura y catálogo/categoría válida.
3. Procesar una noticia real de Google Alerts y crearla con POST existente.
4. Verificar por GET y en panel que es draft, sin fecha de publicación.
5. Conciliar una repetición mediante lectura, sin segundo POST ni ensayo de duplicación.
6. Comprobar que draft no aparezca como candidato de publicación social.
7. Tras la prueba manual satisfactoria, pausar únicamente la tarea vieja de Alerts e instalar la nueva diaria a las 13:00. Hacer el cambio en la misma sesión para evitar doble procesamiento.
8. Verificar la primera ejecución programada. Si falla, pausar la nueva y conciliar altas inciertas antes de reanudar la vieja. No modificar la ingesta web.

Pendientes reales: almacenamiento privado de credencial, conexión persistente, prueba manual y prueba programada.
No se realizaron altas productivas, configuración de secretos, cambios de tareas ni pruebas de ejecución en esta preparación.

## Redes sociales: revisión separada pendiente

En el workflow versionado resources/integrations/n8n/daily/news-facebook-instagram.json, n8n recibe payload.copy del portal; no lee Sheets. SocialPublicationLedgerService construye título + excerpt + URL y guarda snapshot.
El modelo News y StoreNewsRequest revisados no declaran copies editoriales separados por red.
Todavía falta inspeccionar el workflow ACTIVO del n8n local. Agregar copies independientes a base/API/panel sería otra mejora, separada del envío de noticias draft y no necesaria para su prueba inicial.
