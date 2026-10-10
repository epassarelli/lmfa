# Auditoría de descripción de Eventos — 2026-10-09

Pedido: verificar Backoffice, tabla, modelo, controlador y frontend ante aviso Search Console de description faltante. Inspección de código y consultas locales read-only, HTTP GET público; sin implementación, migración, modificación de contenido ni despliegue.

## Recorrido confirmado en código y BD local

1. `backend/events/form.blade.php`: Detalles del Evento es textarea body con CKEditor; Resumen es excerpt. No hay campo literal description ni input meta_description en este formulario. Formulario de evento con jornadas guarda body/excerpt comunes; EventProgramService copia defaults a jornadas salvo excepciones.
2. ShowRequest exige body como string; EventService normaliza encabezados y guarda Event::create/update. El requisito string no garantiza texto significativo si viene HTML vacío.
3. Esquema real local events: columnas body, excerpt y meta_description, sin description. Event declara esas tres en fillable. Accesores detalles/detalle devuelven body.
4. ShowsController carga Event completo; entrega show y SeoMetadata::event()['description'] como metaDescription.
5. Ficha pública muestra excerpt y body, si contienen texto; publica metaDescription y pasa ese texto a EventSchema::forEvent, que emite description en JSON-LD. Prioridad de la ficha: meta_description, excerpt, body (vía detalles), texto genérico final.
6. Cartelera muestra body abreviado (vía detalle) y genera JSON-LD por evento con body sin HTML, limitado a 160 caracteres. No usa las alternativas de la ficha para esa propiedad.

## Evidencia pública

GET de las dos páginas de `/cartelera-de-eventos-folkloricos` entregó 17 objetos Event: todos con description no vacía. Las 17 URLs de esos objetos se consultaron: todas con Event.description y meta description no vacías. En 12 fichas coincide también el prefijo de 50 caracteres de description contra el texto HTML visible normalizado; en cinco no coincide literalmente. Esa comparación parcial no demuestra ausencia del detalle, ni acredita que las descripciones SEO personalizadas se muestren literalmente. El código de la ficha renderiza body como contenido y permite description SEO distinta.

La portada consultada en el turno anterior no emitía objetos Event. No hay acceso al informe privado ni a las URLs rastreadas por Google; no se atribuye el aviso a una URL concreta ni se declara resuelto en Search Console.

## Debilidad encontrada

Si body falta o solo tiene HTML sin texto, el JSON-LD de la cartelera puede contener description vacía. En local: 324 eventos, 114 sin texto body, ninguno con excerpt alternativo; cero eventos públicos vigentes/en curso con body vacío. Esto demuestra una posibilidad de código y deuda de contenido histórica local; no demuestra la causa del aviso productivo. No se accedió a la BD productiva.

Recomendación: centralizar la descripción semántica basada en contenido real y su normalización para ficha/cartelera, con prueba Feature de body vacío/HTML vacío y excerpt disponible. Los registros sin ningún contenido requieren revisión editorial; no agregar una columna description duplicada ni inventar descripción cultural. Esta auditoría no implementa esa recomendación.

## Impacto

Sin cambios de comportamiento, consultas productivas, performance, URLs ni SEO. Evidencia local ignorada: storage/app/local-audits/event-description-readonly.php. Documentación oficial: https://developers.google.com/search/docs/appearance/structured-data/event?hl=es (description recomendado). No se ejecutaron tests porque no se modificó código; revisión de persistencia por código/esquema y emisión por HTML real.
