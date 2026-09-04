# Radios y Peñas — especificación de directorios evergreen

## Decisión

Radios y Peñas son módulos previstos en la visión de Mi Folklore Argentino, pero no deben activarse reparando directamente las superficies legacy. Se reconstruirán como directorios evergreen verificables, con contratos editoriales y técnicos equivalentes a los de Festivales, Biografías, Recetas y Mitos.

Hasta que exista un release dedicado:

- no habilitar detalles públicos nuevos;
- no reutilizar Noticias como destino de sus enlaces;
- no cargar registros masivamente;
- no tratar las tablas legacy como contrato definitivo;
- conservar los datos existentes para su auditoría y futura migración.

## Objetivos comunes

Cada ficha debe responder:

- qué es la radio o peña;
- dónde funciona;
- cuál es su relación demostrable con el folklore argentino;
- cómo acceder, escuchar, asistir o contactar;
- cuándo fue verificada por última vez;
- qué fuentes respaldan la información.

Los dos directorios deben contar con:

- listado, detalle y filtros territoriales;
- backoffice y API `GET/POST/PUT`;
- estado editorial y publicación programable;
- SEO persistido, canonical, sitemap y schema.org;
- imagen destacada, texto alternativo y procedencia;
- fuentes y fecha de última verificación;
- auditor read-only y score de calidad;
- Content Refresh con `CREAR/ACTUALIZAR`, `ID_WEB` y `ENVIAR_API`;
- smoke, tests y release gate independientes.

## Contrato editorial común propuesto

| Campo | Uso |
|---|---|
| `title` | Nombre canónico visible. |
| `slug` | URL estable y única. |
| `excerpt` | Bajada editorial. |
| `body` | Descripción evergreen en HTML sin H1. |
| `province_id` | Provincia normalizada. |
| `city` | Localidad. |
| `address` | Dirección cuando sea pública y verificable. |
| `latitude`, `longitude` | Coordenadas opcionales verificadas. |
| `phone`, `email`, `website` | Contacto oficial. |
| `facebook_url`, `instagram_url`, `youtube_url` | Canales oficiales comprobados. |
| `featured_image_id`, `featured_image_path`, `image_alt` | Media editorial y accesibilidad. |
| `seo_title`, `meta_description` | SEO persistido. |
| `editorial_status` | `draft`, `approved`, `published`, `archived`. |
| `published_at` | Fecha de publicación. |
| `source_urls` | Fuentes consultadas, estructuradas. |
| `last_verified_at` | Última comprobación de vigencia. |
| `verification_status` | Pendiente, verificada o desactualizada. |
| `visits` | Demanda y priorización editorial. |

El cuerpo debe utilizar H2/H3, párrafos y preguntas frecuentes cuando aporten valor; nunca debe contener H1.

## Campos específicos de Radio

- `station_type`: tradicional, online o mixta;
- `stream_url`;
- `frequency` y `band` cuando exista emisión terrestre;
- `coverage_area`;
- `programming_summary`;
- `folklore_programs` o programación destacada verificable;
- `listen_status`: operativo, temporalmente caído o no verificado.

La URL de streaming debe validarse periódicamente. No debe afirmarse que una radio transmite folklore las 24 horas salvo fuente oficial y verificación reciente.

Schema sugerido: `RadioStation`, con `Organization` como alternativa cuando los datos disponibles no permitan una representación específica confiable.

## Campos específicos de Peña

- `venue_type`: peña, centro cultural, espacio gastronómico-cultural u otro;
- `opening_hours`;
- `reservation_url` o canal de reservas;
- `capacity` cuando esté verificada;
- `accessibility_notes`;
- `regular_events_summary`;
- `admission_notes`;
- relación opcional con `Venue` y con Eventos publicados.

Schema sugerido: `MusicVenue` o `LocalBusiness`, según el funcionamiento real. No asignar un tipo comercial cuando se trate de una organización cultural sin local permanente.

## Navegación y descubrimiento

- landings nacionales separadas para Radios y Peñas;
- filtros por provincia y localidad;
- filtros específicos de radio por tipo/banda y de peña por modalidad;
- relaciones con Eventos, Festivales, Artistas y Enciclopedia;
- bloque común “Seguí explorando”;
- búsqueda global únicamente para fichas publicadas y verificadas;
- señal visible de última verificación cuando afecte horarios, streaming o contacto.

## Auditoría de calidad

El score debe considerar como mínimo:

- identidad y slug;
- bajada y cuerpo suficiente;
- ubicación;
- contacto o canal oficial;
- datos específicos del tipo;
- SEO;
- imagen no fallback y alt;
- fuentes;
- verificación reciente;
- relaciones con otras entidades.

Orden recomendado: P1/P2/P3, menor score, mayores visitas y luego ID.

## Plan de implementación

1. Auditar tablas legacy, registros y tráfico sin modificar datos.
2. Aprobar el modelo común y los campos específicos.
3. Crear migraciones compatibles y estrategia de backfill.
4. Implementar modelos, requests, policies, servicios y API.
5. Implementar backoffice y auditor.
6. Implementar frontend, filtros, canonical, sitemap y schema.
7. Integrar Content Refresh y pruebas controladas. **Completado en DEV:** tipos `Peña`, `Radio` y `ProgramaRadio`, con `CREAR/ACTUALIZAR`, preflight offline y altas forzadas a `draft/pending`. **Pendiente de release:** ejecutar los seis casos contra staging HTTPS, registrar `ID_WEB`, confirmar updates parciales y dejar los flags apagados al finalizar.
8. Migrar un lote piloto y verificar vigencia.
9. Activar cada directorio como release independiente.

## Gate de publicación

No activar un directorio hasta contar con:

- migración probada sobre datos legacy;
- CRUD y API protegidos;
- listado/detalle/filtros con tests;
- SEO/schema/canonical/sitemap validados;
- auditor y cola P1;
- al menos diez fichas verificadas con fuentes y media autorizada;
- Content Refresh controlado;
- smoke productivo, monitoreo y rollback.
