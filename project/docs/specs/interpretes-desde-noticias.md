# Ampliación editorial de intérpretes desde noticias

Fecha: 2026-11-17. Estado: blocked por conexión con API productiva.

## Objetivo y aprobación

El usuario autorizó recorrer todas las noticias de más reciente a más antigua, detectar intérpretes ausentes, investigar su biografía y crear fichas inactivas mediante la API existente. Incluye solistas, grupos y géneros cercanos cuando tengan relación documentada con el folklore. Deben aparecer en el Centro de Moderación para aprobación humana. No autoriza publicación ni modificación de fichas existentes.

## Contrato comprobado en código local

- GET /api/v1/artists: catálogo completo por defecto, incluidos inactivos; páginas de 50, orden alfabético. No usar active_only para deduplicación.
- GET /api/v1/news: páginas de 15, orden published_at descendente y luego created_at descendente. Incluye borradores según permisos. Descargar el conjunto y verificar el orden antes de recorrerlo; no asumir orden por ID ni filtros sort inexistentes.
- POST /api/v1/artists: interprete y biografia obligatorios; slug se genera si falta y exige unicidad. Enviar estado=false explícitamente y artist_type soloist/group comprobado. user_id está prohibido y lo asigna el servidor.
- Campos opcionales: excerpt, seo_title, meta_description, image_alt, foto/featured_image_url y contactos/redes admitidos por StoreArtistRequest.
- ModerationQueue::interpretes filtra estado=0 y ModerationController::index usa esa consulta. Esto acredita el comportamiento local esperado, no el despliegue ni una alta productiva.

## Procedimiento y criterios editoriales

1. Consultar todas las páginas de artistas, incluidos inactivos, antes de cualquier alta. Cotejar nombre, slug, alias e identidad; no fusionar homónimos ni equiparar grupo con sus integrantes.
2. Consultar y leer el cuerpo completo de cada noticia de más reciente a más antigua; no limitarse al intérprete relacionado o al título. Registrar ID y fecha, candidatos y decisión.
3. Investigar candidatos ausentes en fuentes leídas, priorizando oficiales e institucionales. La mención en una noticia no basta para completar una biografía. Identidad ambigua o evidencia insuficiente: dejar pendiente, sin alta.
4. Redactar HTML original sin H1 y con fuentes enlazadas, trayectoria y datos respaldados. Completar bajada y SEO específicos; omitir datos desconocidos, contactos privados e imágenes sin procedencia/derechos verificables. No inventar fechas, integrantes ni discografía.
5. Reconsultar coincidencias antes del POST. Crear una primera ficha real con estado=false; verificar ID, contenido y estado por GET y presencia efectiva en el Centro de Moderación antes de ampliar el lote.
6. Registrar respuesta e ID de cada alta. Ante timeout, conciliar por catálogo antes de reintentar; no asumir idempotencia de la API. Actualizar catálogo en memoria después de cada alta.
7. Persistir avance sin secretos: noticias revisadas, candidatos, fuentes, decisiones, IDs creados y pendientes. No marcar una noticia completada si quedan candidatos sin decisión registrada. Reportar cobertura total y punto de reanudación, sin confundir lote con recorrido completo.

## Fuera de alcance

Cambios Laravel, SQL directo, migraciones, publicación, automatizaciones periódicas, actualización de artistas existentes, cambios en noticias, despliegues o modificaciones de credenciales.

## Validación y performance

Usar API existente de forma secuencial y paginada, evitando consultas repetidas por mención. No ejecutar tests que escriban en la base local para esta operación editorial. Validación operativa: GET de catálogo/noticias, POST201 de primera ficha, GET de comprobación y revisión del panel. Las fichas deben permanecer inactivas; no se acredita impacto SEO ni performance productiva sin medición.

## Evidencia y bloqueo actual

Rama dev, con cambios preexistentes preservados. Lecturas locales: StoreArtistRequest, ArtistController, NewsController, ModerationQueue y ModerationController. En esta sesión el acceso HTTPS al dominio devuelve «Host desconocido». El entorno de ejecución declara red restringida; no se ha establecido si el fallo procede de esa restricción o del DNS del equipo. No hay evidencia de caída del portal. No se realizaron consultas autenticadas exitosas, descargas del catálogo, lectura de noticias ni POST. Altas: 0. El siguiente paso es comprobar GET autenticado desde una sesión con acceso de red habilitado, reutilizando la configuración existente sin imprimir secretos.
