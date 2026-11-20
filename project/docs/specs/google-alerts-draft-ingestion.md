# Google Alerts → noticias draft mediante la API existente

Fecha: 2026-11-15.
Estado: prueba API productiva validada el 2026-11-15; borrador 850 verificado y tarea local diaria activa a las 13:00 Argentina. Primera corrida programada pendiente; Alerts anterior permanece activa hasta su éxito. Sin cambios de aplicación, migraciones ni despliegues.

Prueba manual adicional solicitada el2026-11-15: la herramienta de automatizaciones rechazó mode=run (solo admite consulta, creación, actualización y eliminación); no se disparó el scheduler. Se ejecutó en este chat el procedimiento guardado: 10 correos leídos, 475 noticias cotejadas en32 páginas y5 fuentes intentadas. Cero POST/cero borradores nuevos: Larralde/MiloJ/Tocando al Frente ya procesados; SanFernando/Baradero descartados previamente; ElLitoral ya generado en Sheets; Duratierra inaccesible (403/timeout) y Crisis ofrecía solo presentación de podcast sin texto completo. Revalidado news850 por GET200, draft y published_at=null. Registro privado manual-run-20261115.json; Alerts anterior y web sin cambios. No confundir esta ejecución manual sin altas con éxito del disparo automático.

## Decisiones vigentes

- Reutilizar POST https://mifolkloreargentino.com/api/v1/news.
- Enviar editorial_status="draft" y published_at=null.
- No desarrollar otro endpoint ni modificar Laravel para la primera prueba.
- Nueva tarea diaria a las 13:00, America/Argentina/Buenos_Aires.
- Hasta 3 noticias válidas por corrida; única fuente de descubrimiento: Gmail/Google Alerts.
- Ampliación editorial autorizada por el usuario el2026-11-15: búsquedas web de apoyo para verificar/completar el mismo hecho (máximo2 fuentes por candidato,6 por corrida), priorizando fuentes oficiales. Google Alerts conserva el descubrimiento; no altera la ingesta web separada. Sin evidencia suficiente, omitir el dato o dejar pendiente el candidato; nunca inventar.
- Redacción original desde fuentes leídas, sin copiar ni reformular mecánicamente el artículo. SEO orientado a folklore argentino y temática concreta, sin repetición artificial de keywords. Preguntas frecuentes al final antes de fuentes:2–4 preguntas útiles y respuestas verificadas; omitir y registrar motivo si no hay evidencia para dos preguntas, sin relleno ni FAQPage automático.
- Propuestas de copy Facebook/Instagram en registro privado, con #MiFolkloreArgentino #Folklore #Folclore #FolkloreArgentino y1–3 hashtags temáticos pertinentes. No inventar @cuentas ni enlaces públicos a drafts. La API actual no recibe copys por red y el circuito social usa título+excerpt+URL: propuestas guardadas no equivalen a integración o publicación. No contaminar excerpt/body/SEO con hashtags para forzar su salida.
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

DESCUBRIMIENTO: Gmail label:mfa---alertas-de-google newer_than:7d. Etiqueta previamente verificada: Label_3357873914155608530. Revisá hasta 10 correos recientes y abrí como máximo 5 artículos candidatos no procesados. Extraé enlaces de artículos y resolvé el parámetro url de enlaces Google. Quitá seguimiento conocido sin borrar parámetros que identifiquen el artículo. Descartá enlaces administrativos, compartir y desuscripción. Se permiten búsquedas de apoyo sobre el mismo hecho (hasta2 fuentes por candidato/6 por corrida), priorizando fuentes oficiales, sin descubrir noticias ajenas a Alerts. No modifiques, archives o borres correos; no uses leído/no leído como control.

DEDUPLICACIÓN: compará URL fuente, hecho, título y slug con las noticias del portal, incluidos borradores, y con registros anteriores de la bandeja editorial (spreadsheet 1S3_Ukj9oV66SPJw-l0KPlWBg79rO14GBEcPxQl1sZzA, Contenidos e Ingesta_Alertas_Google) mediante lecturas acotadas. No escribas nuevas noticias en Sheets ni cambies los estados de las tareas anteriores. Si no podés comprobar duplicados razonablemente, no envíes a ciegas.

REDACCIÓN: prepará hasta3 noticias originales en español rioplatense a partir de fuentes completas leídas y apoyos verificados, nunca copiando ni reformulando mecánicamente. Intentá obtener al menos una cuando exista evidencia; no fuerces contenido. Incluí título, bajada, HTML sin H1, slug, SEO para folklore argentino y temática concreta, categoría comprobada y atribución/enlaces. NO INVENTES citas, fechas, cifras, precios, lugares, créditos, datos culturales, imágenes, derechos, IDs o confirmaciones; no sustituyas evidencia por memoria o inferencias. Ante contradicción omití el dato o dejá pendiente. Incorporá al final antes de fuentes2–4 preguntas frecuentes útiles con respuestas respaldadas; si no alcanza la evidencia, omitilas y registrá motivo. Sin relleno, keyword stuffing ni FAQPage automático. Prepará copys por red con los hashtags definidos en decisiones vigentes y guardalos en registro privado: la API actual no los admite ni el workflow social los consume. No introduzcas hashtags en excerpt/body/SEO para suplir esa limitación. No agregues marcas de Google Alerts. Lanzamientos es Noticia; no crees entidades Festival ni hagas refresh. Ante bloqueo/paywall usá apoyo accesible del mismo hecho sin eludir barreras; si sigue siendo insuficiente, descartá o dejá pendiente.

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

## Continuación autorizada en Codex local — 2026-11-15

El usuario autorizó usar MFA_API_TOKEN del .env sin mostrarlo, probar creación de noticias draft y preparar la ingesta diaria de Alerts a las 13:00 de Argentina. La pausa de Alerts anterior depende de que la nueva funcione; la ingesta web se conserva.

### Ejecutor previsto y secreto

La ejecución debe ser local en C:\\proyectos\\lmfa, con acceso al .env original y a Gmail/Drive conectados. Leer exclusivamente MFA_API_TOKEN en memoria; no imprimir .env, headers Authorization, token, transcripciones HTTP ni respuestas que puedan reflejar secretos. Enviar el Bearer únicamente a https://mifolkloreargentino.com, con validación TLS y sin seguir redirecciones autenticadas a otro destino. No copiar el .env a un worktree ni subirlo a servicios externos. Si falta la variable, detener envíos. No usar la credencial social.

Usar el prompt de esta spec agregando estas condiciones locales. Mantener un registro operativo ignorado por Git en storage/app/private/alerts-ingestion/ con URL fuente, slug estable, estado, ID devuelto y resultado GET; no guardar credenciales ni datos personales de correos. Registrar intención antes del POST y conciliar antes de repetir cualquier candidato pendiente o incierto. Releer API y Sheets contra duplicados antes de escribir. La existencia de este diseño no acredita un registro implementado ni una ejecución programada validada.

El horario solicitado es diario 13:00 America/Argentina/Buenos_Aires. La tarea local requiere computadora y aplicación disponibles. No configurar una ejecución cloud que dependa del .env de esta PC. No activar una tarea que no haya demostrado acceso autenticado y alta verificada. Tras validar la prueba manual, instalar la nueva y hacer la transición de Alerts; verificar después la primera corrida programada y conciliar antes de cualquier recuperación. Conservar intacta la ingesta web.

### Evidencia de esta continuación

- PR #17 abierto como draft, base dev, head feature/google-alerts-draft-ingestion-plan, revisión 73176d84e061f2bfc734ead1644abbe41903c1ac. Checkout actual main, sin cambio de rama ni commit/push.
- Revisión del contrato local StoreNewsRequest, NewsController y NewsService: draft explícito y published_at=null soportados; no se modificó Laravel.
- .env guardado: MFA_API_TOKEN ausente en dos comprobaciones sin mostrar valores. Se pidió agregar/guardar la variable o indicar otro .env, nunca pegar el secreto en chat. No se efectuó HTTP autenticado ni POST.
- UI de tareas: MFA — Noticias desde alertas activa, con acción Pausar y próxima ejecución informada; su destino sigue siendo Sheets. MFA - Ingesta de contenidos nuevos sigue en la lista activa. Ninguna tarea fue creada, editada o pausada.
- Gmail: 10 alertas recientes localizadas; leído el correo del 15/11. Crisis no se pudo abrir con la herramienta web; se leyó completo el artículo de Canal 26 del 14/11 sobre José Larralde y su etapa metalúrgica en Lanús. No se siguieron enlaces de otros artículos ni se modificó Gmail.
- Lecturas acotadas de Contenidos e Ingesta_Alertas_Google: la noticia previa sobre el cumpleaños de Larralde tiene ID_WEB 779; no se encontró el enlace del artículo metalúrgico. Esto NO reemplaza la consulta autenticada al portal, pendiente.
- Categoría Actualidad=1 presente en una fila histórica de Sheets; debe comprobarse vigente en API/panel antes del alta. No se envió el payload ilustrativo.

### Pendientes y criterio de cierre

1. Validado: MFA_API_TOKEN presente en el .env de main; GET autenticado exitoso, sin revelar el secreto.
2. Completar deduplicación en todas las páginas pertinentes de la API y verificar categoría vigente.
3. Crear una noticia real mediante POST y verificar por GET ID, título, draft y published_at vacío; comprobar panel y exclusión social sin publicar.
4. Instalar tarea local diaria a las 13:00 con acceso a ese archivo, Gmail/Drive y registro persistente. Pausar únicamente Alerts anterior cuando el circuito nuevo esté probado. Conservar ingesta web.
5. Validar primera corrida programada. No confundir alta manual exitosa con validación del scheduler.

Performance/SEO: sin cambios de runtime ni visibilidad pública. Lecturas acotadas de Gmail/Sheets y fuentes; no se hicieron pruebas de carga ni mediciones de producción. No se ejecutaron tests con escrituras ni migraciones. Continuación preparada localmente; no integrada ni desplegada.

### Actualización tras confirmar el .env — 2026-11-15

La nueva lectura confirmó una única variable MFA_API_TOKEN no vacía en el .env de main. La conclusión previa de ausencia queda superada por esta comprobación. No se imprimió, copió ni versionó el token. La conectividad inicial falló por resolución del host dentro del entorno restringido; la conexión autorizada al mismo dominio funcionó con TLS normal.

- GET autenticado HTTP200; 474 noticias en 32 páginas, incluidos 26 drafts. La única coincidencia relacionada con Larralde fue news779, sobre su cumpleaños; no había coincidencia con la fuente metalúrgica, el slug ni el hecho seleccionado. Actualidad1 confirmada activa en la respuesta API.
- Un único POST a la API existente respondió HTTP201: noticia850, «José Larralde: los oficios de Lanús que acompañaron sus primeras canciones», slug jose-larralde-oficios-lanus-primeras-canciones. Sin imágenes ni letras/citas de canciones; texto propio con atribución y enlace a Canal26. Sin Sheets nuevos ni Apps Script.
- GET por ID respondió HTTP200 con título/slug correctos, editorial_status=draft y published_at=null. Journal privado storage/app/private/alerts-ingestion/pilot-larralde.json registra intención previa, HTTP201, ID y verified_draft; directorio comprobado ignorado por Git. No se repitió POST.
- Panel administrativo: búsqueda exacta devuelve una fila con enlace /admin/news/850/edit, Inactivo y fecha «-». URL pública /noticias-del-folklore-argentino/jose-larralde-oficios-lanus-primeras-canciones devuelve404. Panel de redes indica selección exclusiva de noticias publicadas y visibles; news850 no figura en sus registros. No se invocó claim/publicación ni se alteró el ledger. No se verificó el listado de candidatos mediante PAT social.
- Tarea nativa local mfa-alertas-a-borradores creada ACTIVE, tipo heartbeat, vinculada a este chat local. Frecuencia diaria13:00:00; sistema Windows Argentina Standard Time, UTC-3. Configuración persistida y releída, sin secreto en prompt. Acceso al .env original por ruta absoluta; requiere PC y aplicación disponibles.
- Primera corrida programada pendiente. Se conserva Alerts anterior activa por condición expresa del usuario. El prompt autoriza pausarla únicamente después de crear y verificar al menos un borrador en una corrida programada exitosa; comprobar pausa efectiva y reportar si no es posible. Ingesta web6a6e0f2ce1e481918a2c262b452ab87b queda intacta. La prueba manual no acredita éxito del scheduler.

Pendiente operativo: primera corrida13:00, verificar acceso Gmail/Drive/archivo/red, alta y journal, y pausa efectiva de Alerts6ac0893721e88191a6ea6f3d2dc0e6a8. No se cambió rama, hizo commit/push ni se actualizó el contenido remoto del PR17. Documentación preparada en el checkout actual para revisión.

Performance/SEO: 32 lecturas paginadas secuenciales para control completo de duplicados, un POST y GET de verificación. Borrador no público (404); sin cambios de frontend ni assets. No son mediciones de rendimiento productivo. No se ejecutó suite ni SQL/migraciones; validación operativa por API y panel.

