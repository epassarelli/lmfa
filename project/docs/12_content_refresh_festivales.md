# MFA — Content Refresh de Festivales

## Objetivo

Convertir Festivales en el primer módulo MFA que combina descubrimiento de contenido nuevo con curación sistemática de contenido legacy de baja calidad.

El flujo operativo usa la pestaña `Contenidos` como bandeja común.

## Tipos

`TIPO=Festival` representa la ficha evergreen estable del festival.

No debe confundirse con:
- `TIPO=Noticia` + categoría Festivales: cobertura de actualidad.
- `TIPO=Evento`: una edición concreta, fecha, grilla, entradas o programación.

## Acciones API

### CREAR

- `ACCION_API=CREAR`
- `ID_WEB` vacío.
- cuando la investigación está completa puede usar `ENVIAR_API=S`.
- el loader usa `POST /api/v1/festivals`.
- la API crea el Festival en draft.

### ACTUALIZAR

- `ACCION_API=ACTUALIZAR`
- `ID_WEB` obligatorio.
- el auditor genera `SCORE_CALIDAD` y `FALTANTES`.
- durante investigación: `ENVIAR_API=N`.
- sólo se habilita `ENVIAR_API=S` cuando los faltantes críticos fueron resueltos.
- el loader usa `PUT /api/v1/festivals/{ID_WEB}`.

Un update nunca debe reconstruir a ciegas todo el registro: preservar datos válidos existentes y modificar únicamente campos investigados.

## Campos Festival en Contenidos

Editorial:
- TITULO
- SLUG
- BAJADA
- CUERPO
- META_TITLE
- META_DESCRIPTION
- IMAGE_ALT

Ubicación:
- PROVINCE_ID
- LOCALITY_ID
- MES_ID
- PROVINCIA
- LOCALIDAD

Media:
- FEATURED_IMAGE_PATH
- FEATURED_IMAGE_URL
- IMAGE_SOURCE_TYPE
- IMAGE_RIGHTS_STATUS

Relaciones:
- NEWS_IDS
- EVENT_IDS
- INTERPRETE_IDS
- KNOWLEDGE_ARTICLE_IDS

Operación:
- ACCION_API
- SCORE_CALIDAD
- FALTANTES
- ENVIAR_API
- ID_WEB
- RESULTADO_API
- ERROR_API
- FECHA_ENVIO_API

## Priorización de curación

Orden:
1. P1 con menor SCORE_CALIDAD.
2. resto de P1.
3. P2.
4. P3.

El agente debe resolver primero:
1. provincia;
2. localidad;
3. mes habitual;
4. bajada;
5. seo_title;
6. meta_description;
7. cuerpo pobre;
8. relaciones;
9. image_alt / media cuando corresponda.

## Reglas editoriales

- body HTML semántico sin H1.
- mínimo operativo para Festival: 300 palabras visibles cuando las fuentes permiten desarrollarlo.
- H1 pertenece al frontend y deriva del título.
- no convertir programación anual en información estable.
- fechas, horarios, grilla y entradas de una edición concreta pertenecen a Evento.
- no inventar localidad, mes, relaciones, derechos de imagen ni datos históricos.
- priorizar fuentes oficiales, gubernamentales, municipales, organizadores y archivos institucionales.

## Descubrimiento de nuevos Festivales

Antes de crear una fila:
1. comprobar nombre y slug en MFA;
2. comprobar `Festivales — Cola editorial`;
3. descartar duplicados o variantes del mismo Festival;
4. completar el contrato moderno;
5. usar `ACCION_API=CREAR`.

La cola histórica no se copia ciegamente porque el campo `YA_PUBLICADO` puede estar desactualizado.

## Curación legacy inicial

La auditoría de producción del 31/08/2026 detectó:
- 30 Festivales publicados;
- 22 P1;
- 6 P2;
- 2 P3;
- 18 sin provincia;
- 30 sin localidad;
- 16 sin mes;
- 27 sin seo_title;
- 26 sin meta_description;
- 2 con fallback;
- 29 sin relaciones.

Se sembraron inicialmente en `Contenidos` los 20 P1 visibles en la salida del auditor, como `ACCION_API=ACTUALIZAR` y `ENVIAR_API=N`.

## Seguridad

- ninguna fila ACTUALIZAR se envía mientras `ENVIAR_API=N`.
- ACTUALIZAR sin `ID_WEB` es error de validación.
- CREAR con `ID_WEB` es error de validación.
- relaciones vacías no deben borrar relaciones existentes durante un update.
- después de un envío correcto, `ENVIAR_API` vuelve a N.
