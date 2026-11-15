# Google Alerts → borradores del portal

Fecha: 2026-11-15.
Estado: propuesta técnica y prompt preparados. Sin implementación, credenciales, migraciones, despliegue ni prueba productiva.
Base de revisión: rama dev. La configuración productiva y el workflow activo de n8n no se inspeccionaron en esta sesión.

## Objetivo autorizado

Crear una automatización nueva de noticias descubiertas únicamente en Gmail/Google Alerts, con envío directo al portal y revisión humana allí. Frecuencia: cada 12 horas. Hasta 3 noticias válidas por corrida. Sin etiquetas editoriales adicionales ni marcas en la bajada. No usar Sheets como bandeja de alta.

Mantener intactas las tareas existentes hasta verificar el circuito nuevo. Después de la prueba satisfactoria, pausar únicamente MFA — Noticias desde alertas (6ac0893721e88191a6ea6f3d2dc0e6a8). Conservar activa MFA - Ingesta de contenidos nuevos (6a6e0f2ce1e481918a2c262b452ab87b), que descubre desde la web. No tocar Evergreen ni Instagram.

## Hallazgos comprobados en código

- routes/api.php: POST /api/v1/news exige auth:sanctum y rol administrador.
- app/Http/Requests/Api/StoreNewsRequest.php acepta editorial_status published y campos legacy capaces de afectar publicación: estado y publicar.
- app/Services/NewsService.php crea borradores por defecto, pero un administrador puede publicar explícitamente. El prompt por sí solo no restringe esa capacidad.
- El alta actual no tiene un contrato de idempotencia por URL fuente.
- No basta agregar Authorization a una tarea: debe existir una herramienta conectada y una credencial persistente disponible durante la ejecución programada.
- resources/integrations/n8n/daily/news-facebook-instagram.json recibe payload.copy del portal. No contiene nodos de lectura de Sheets.
- app/Services/Publication/SocialPublicationLedgerService.php construye copy con título, excerpt y URL; guarda payload_snapshot_json al reservar.
- app/Models/News.php y el contrato de alta revisado no ofrecen copies editoriales separados por red.
- El repositorio documenta una activación local de Facebook + Instagram; eso no verifica el estado actual del Docker del usuario.

## Alcance de la primera implementación

1. Transporte autenticado accesible como herramienta desde ChatGPT.
2. Consulta acotada de duplicados y catálogo de categorías.
3. Alta idempotente de noticias con borrador obligatorio.
4. Registro persistente de fuente/resultado en el portal.
5. Prompt de la tarea conservado en este documento para instalarlo después de verificar acceso.

Fuera de esta etapa: modificar n8n, publicar en redes, agregar copies independientes sin cerrar su diseño, refresh, otros tipos de contenido o migrar toda la planilla.

## Autenticación: decisión pendiente de validar en ejecución real

Una conexión MCP remota con OAuth es una opción documentada para integrar herramientas con ChatGPT. Su uso sin intervención en esta tarea programada y esta cuenta todavía NO está comprobado. No tratarla como capacidad ya disponible.

Primero validar una herramienta de lectura autenticada desde el entorno de ejecución programada. Si la plataforma no conserva la conexión o requiere intervención en cada corrida, no habilitar una programación que dependa de ella: evaluar un ejecutor de servidor independiente como alternativa y acordar el cambio de arquitectura.

El token Sanctum del portal permanece en el servidor del adaptador, fuera del prompt, código versionado, hojas y logs. OAuth del conector y token Sanctum son credenciales distintas. Nunca exponer un puente público que escriba por conocer su URL. No modificar ni extraer las propiedades de Apps Script para simular almacenamiento seguro.

La API actual exige administrador para las altas. Antes de conectar un agente de ingesta, implementar una ruta específica con permisos limitados y sin ampliar el permiso de escritura general de /news. Una cuenta dedicada sin rol administrador y una habilidad específica son el diseño propuesto; todavía no existen.

## Herramientas propuestas: contrato, no endpoints existentes

- news_ingestion_categories: IDs y nombres vigentes, solo lectura.
- news_ingestion_lookup: consulta por URL fuente normalizada, título o slug; incluye borradores y publicados con resultado mínimo (ID, título, estado).
- news_ingestion_create_draft: alta estricta e idempotente.
- news_ingestion_result: conciliación por clave estable después de un timeout.

El servidor debe:
- Exigir autenticación y permiso dedicado en cada operación, denegando por defecto.
- Exponer únicamente operaciones de ingesta; no ofrecer publicar, borrar, actualizar contenidos existentes ni acceder a secretos.
- Crear siempre editorial_status=draft, published_at=null, estado=0 y publication_mode=portal_only.
- Asignar created_by al operador, approved_by=null y approved_at=null.
- Rechazar campos de publicación/autoría del cliente, incluidos estado, publicar, approved_by, approved_at, created_by y user_id; no confiar solo en editorial_status.
- Aplicar allowlist al payload y validaciones de NewsService, con HTML seguro y sin H1.
- Limitar longitudes, tamaño del cuerpo, frecuencia y número de altas. Una URL fuente es metadata, no autorización para fetch arbitrario desde el servidor.
- No descargar imágenes externas en el MVP; dejar imagen vacía cuando no haya recurso seguro y comprobado. No bloquear noticias por inventar una imagen.

Campos propuestos de entrada:
title, slug, subtitle, excerpt, body, categoria_id, seo_title, meta_description, source_name, source_url, source_published_at opcional, gmail_message_id y clave idempotente estable.
Los nombres source_* son parte del diseño nuevo; no afirmar que /api/v1/news ya los acepta.
Los IDs de intérpretes son opcionales y solo se envían si están comprobados.

Respuesta propuesta:
HTTP 201 en alta nueva; ID, título, slug y estado real. Para repetición de la misma solicitud, devolver el ID original y una marca de replay. Si el contenido ya fue publicado por una persona, devolver su estado actual sin revertirlo a draft. Una misma clave con un payload diferente produce conflicto; nunca sobrescribe.

## Duplicados, concurrencia y trazabilidad

Persistir URL fuente original y normalizada, huella única, clave idempotente, huella del payload, ID de noticia, ID de correo, fecha y resultado. Definir migración y nombre de tabla en implementación, sin reutilizar el ledger de publicación social para otra finalidad.

Normalizar enlaces Google leyendo su parámetro url; quitar únicamente parámetros de seguimiento conocidos y fragmento. Conservar parámetros que identifican artículos y no convertir rutas a minúsculas. Mantener URL original para auditoría. La forma normalizada del servidor es la autoridad.

La restricción única por fuente y la transacción de alta son necesarias para concurrencia y timeout. La consulta previa por sí sola no impide duplicados. Una colisión o solicitud concurrente debe recuperar la noticia ya creada o devolver conflicto conciliable, nunca un alta adicional.

Si la misma URL reaparece con texto cambiado, no refrescar ni actualizar automáticamente. Registrar conflicto para revisión humana.
Deduplicar también por título/slug/hecho frente al portal y, durante la transición, frente a Contenidos e Ingesta_Alertas_Google. Esas consultas son de lectura; no generar nuevas filas.
La ingesta web existente aún puede producir el mismo hecho con otra URL: la unicidad de fuente no resuelve por sí sola duplicados semánticos entre procesos.

No usar leído/no leído ni modificar, archivar o borrar correos.
No registrar cuerpos completos, tokens ni encabezados de autenticación en logs.

## Prompt listo para instalar después del gate

Operá una corrida de ingesta de NOTICIAS NUEVAS para Mi Folklore Argentino. Usá America/Argentina/Buenos_Aires.

PRECONDICIÓN: deben existir herramientas conectadas y autenticadas de consulta, alta exclusiva de borradores y conciliación idempotente. Si no están disponibles, detené la corrida con un bloqueo concreto; no solicites tokens por chat ni uses otra vía de escritura. No implementes ni cambies infraestructura durante la corrida.

DESCUBRIMIENTO: buscá Gmail con label:mfa---alertas-de-google newer_than:7d. Etiqueta previamente verificada: Label_3357873914155608530. Revisá hasta 10 correos recientes, extraé enlaces de artículos y resolvé redirecciones Google. Eliminá enlaces administrativos, de compartir y desuscripción. Abrí como máximo 5 artículos no procesados. No hagas búsquedas web adicionales ni recorras otros artículos.

DEDUPLICACIÓN: consultá registro persistente y noticias del portal, incluidos borradores y publicados, por URL normalizada, título, slug y hecho. Durante la transición consultá puntualmente los registros existentes de la bandeja editorial (spreadsheet 1S3_Ukj9oV66SPJw-l0KPlWBg79rO14GBEcPxQl1sZzA, Contenidos e Ingesta_Alertas_Google), sin escribir. Si no es posible comprobar duplicados, no envíes a ciegas.

REDACCIÓN: generá hasta 3 noticias válidas y originales en español rioplatense, basadas únicamente en el artículo completo. Intentá lograr una cuando las fuentes lo permitan; no fuerces contenido. Incluí título, bajada, HTML sin H1, slug, SEO, categoría comprobada, nombre del medio y URL fuente con atribución clara. No uses snippets como sustituto del artículo, no copies íntegramente ni inventes citas, imágenes, derechos, fechas o IDs. No marques Google Alerts en título o bajada. Lanzamientos es una categoría de Noticia; no crees entidades Festival ni hagas refresh.

ENVÍO: usá exclusivamente la herramienta de alta de borradores con una clave idempotente estable por fuente normalizada. No escribas la noticia en Sheets ni llames Apps Script. No publiques en el portal o redes. Verificá cada alta recuperando ID y estado draft con published_at vacío; reportá una discrepancia real sin ocultarla. Una respuesta sin ID o un estado distinto de draft no es un alta verificada.

ERRORES: ante timeout, 429 o 5xx, conciliá por clave/fuente antes de reenviar. Nunca generes una clave nueva para sortear un resultado incierto. Ante validación 422 corregí solo datos respaldados por la fuente; no inventes ni fuerces. Ante 401/403 detené los envíos e informá el bloqueo sin mostrar secretos. Registrá descartes o fallos de lectura y permití como máximo dos intentos en corridas distintas; conservá pendientes sin consumirlos por un error general del conector.

SALIDA: informá siempre un resumen breve de correos revisados, artículos leídos, borradores creados con ID/título, duplicados, descartes, errores y pendientes. No afirmes éxito sin readback. No modifiques otras tareas programadas.

## Programación preparada, todavía no instalada

Título: MFA — Alertas a borradores.
Zona: America/Argentina/Buenos_Aires.
timing_mode: exact_schedule.
schedule: BEGIN:VEVENT / RRULE:FREQ=HOURLY;INTERVAL=12 / END:VEVENT (serializar con saltos de línea).
Al activar, fijar DTSTART futuro en la zona indicada; el usuario aprobó la cadencia, no una hora específica.
No crear una automatización operativa con la conexión pendiente. El prompt de arriba queda versionado hasta cumplir los gates.

## Validaciones obligatorias antes de habilitar

- Sin autenticación: denegado; sin habilidad: denegado.
- Cuenta de ingesta no puede acceder a rutas generales de escritura, publicar, actualizar ni borrar.
- Alta válida crea un único draft, sin published_at ni aprobación, con autor correcto y fuente persistida.
- Intentos de enviar published, estado=1, publicar, autor o aprobación ajenos: denegados.
- Repetición exacta devuelve el mismo ID; misma clave con otro payload: conflicto sin cambios.
- Dos solicitudes concurrentes por la misma fuente no crean dos noticias.
- Timeout después del commit se concilia sin segunda alta.
- Un replay de una noticia luego publicada devuelve su estado actual y nunca la despublica.
- Fuente con parámetros significativos se conserva; parámetros de seguimiento no multiplican altas.
- Lectura de duplicados incluye drafts.
- Error 422 no deja noticia huérfana ni marca procesada.
- Contenido HTML malicioso no se ejecuta en panel ni frontend.
- Lectura OAuth funciona desde una ejecución programada real sin intervención.
- Prueba manual de UNA noticia de Alerts con verificación en portal; repetir idempotentemente sin duplicar.
- Verificar que draft quede fuera de candidatos de publicación social.

No se ejecutaron estas pruebas en esta preparación: este entorno no tiene PHP/Composer, dependencias ni DB configurada.

## Activación y vuelta atrás

1. Implementar y revisar código, migración y pruebas en rama separada; no fusionar ni desplegar automáticamente.
2. Desplegar por el flujo habitual, configurar operador/credenciales mediante canal privado y conectar herramienta.
3. Completar validaciones y una prueba productiva de borrador autorizada.
4. Crear la automatización nueva con el prompt probado y cadencia de 12 horas; verificar una ejecución programada.
5. Pausar exclusivamente la tarea vieja de Alerts; conservar instrucciones e historial. No dejar doble procesamiento durante el período de pruebas.
6. Si falla el circuito nuevo, pausarlo primero y reanudar la tarea vieja cuando corresponda, conciliando altas inciertas antes de cualquier reintento.

No prometer seguimiento o activación autónomos después de esta sesión: los pasos pendientes se retoman con evidencia y accesos disponibles.

## Copies sociales: siguiente etapa separada

Propuesta aún no implementada: copies opcionales por Facebook/Instagram en base, API y panel, con fallback al texto actual cuando estén vacíos. Antes de cambiar el consumo de n8n, inspeccionar el workflow activo local y confirmar qué versión y endpoint utiliza. Eliminar Excel de la ingesta no demuestra por sí solo que todos los flujos locales estén desacoplados.
