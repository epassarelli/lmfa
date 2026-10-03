# Cambio: event-detail-navigation-and-relations

> Estado: **aprobado Fases 1 y 2 – 2026-10-03**. Aprobación explícita del usuario (chat, 2026-10-03): Fases 1 y 2 de código. Fuera: Fase 1b (completar provincia) y cualquier cambio de datos. Decisiones: artistas siempre visibles; festival, enciclopedia y peñas fuera del allowlist con tracking `event_related_*` (peñas respeta `FEATURE_PENIA_DIRECTORY`); eventos pasados indexables con aviso; buscador compacto (provincia, mes, texto) sin datalist; noticias solo derivadas (sin pivot ni migraciones); Fase 2.3 (backend) incluida.
> Autonomía sugerida: `IA_CON_VALIDACION` (toca SEO, layout público y relaciones editoriales).

## 1. Contexto y problema

La ficha de evento (`/cartelera-de-eventos-folkloricos/{slug}`) es un callejón sin salida y muestra menos información que la tarjeta del listado.

Hallazgos confirmados en código (rama `fix/login-social`, 2026-10-03):

| # | Problema reportado | Causa confirmada | Evidencia |
|---|---|---|---|
| a | Se pierde la navegación | La ficha no ofrece filtros, ni enlaces a provincia, ni otros eventos. El breadcrumb es `Cartelera > Evento`, sin la provincia. | `resources/views/frontend/shows/show.blade.php`; `ShowsController::show()` |
| b | No hay buscador | El formulario de filtros vive incrustado en `shows/index.blade.php`; no es un parcial reutilizable. Festivales y Peñas ya usan `_filters.blade.php` en la ficha. | `frontend/festivales/show.blade.php:34` |
| c | No se puede explorar por provincia | El bloque "Explorar por provincia" existe solo en el índice. Además, en el índice muestra solo las **8 primeras provincias alfabéticas** (`Provincia::orderBy('nombre')->take(8)`), no las que tienen eventos. | `ShowsController::index()`, `$relatedProvinceLinks` |
| d | Usa ~75 % del ancho | El layout ya reserva 9/12 columnas para el contenido. La ficha agrega otro `container mx-auto px-4` y una columna `lg:w-2/3` sin columna hermana. El contenido ocupa 2/3 de 9/12, o sea **el 50 % del contenedor**, con doble padding. | `layouts/app.blade.php:55-63`; `shows/show.blade.php` |
| e | Muestra menos datos que el listado | La ficha renderiza solo imagen, H1 y `body`. **No muestra fecha, hora, lugar, dirección, provincia, precio, entradas ni artistas.** Los artistas y festivales solo aparecen si está activo el piloto `FEATURE_FESTIVAL_JOURNEY` y el festival está en el allowlist; por defecto está apagado. | `shows/show.blade.php`; `FestivalJourneyService::forEvent()`; `config/features.php` |

Otros hallazgos confirmados:

- `ShowsController::show()` ejecuta una consulta `$ultimos_shows` (10 eventos por `created_at`, incluidos los pasados) que la vista no usa.
- `$show->noticias` no es una relación de `Event`: siempre devuelve `null` y la vista tampoco lo usa.
- El JSON-LD `Event` de la ficha no tiene `eventStatus`, `eventAttendanceMode`, `endDate`, `offers` ni una dirección estructurada. `location.name` usa la ciudad. El JSON-LD del índice es más completo que el de la ficha.
- Las relaciones persistidas existen pero `Event` no las expone todas: `event_festival` (`festivales()`), `event_interprete` (`interpretes()`), `penia_profile_event` (`peniaProfiles()`) y `event_knowledge_article`, que solo tiene la relación inversa en `KnowledgeArticle::events()`. **No existe ninguna relación Evento–Noticia.**
- El formulario de backend de eventos no permite vincular festivales, artículos de enciclopedia ni peñas: hoy esas relaciones se cargan solo desde el formulario de la otra entidad. Tampoco expone `end_at` ni `excerpt`.

### Datos de la auditoría local (2026-10-03)

Fuente: `project/docs/sql/audit_eventos_cobertura.out.txt` (base local, no producción).

| Métrica | Valor | % de los públicos |
|---|---:|---:|
| Eventos públicos | 309 | 100 % |
| Públicos **futuros** | **3** | **1 %** |
| Con intérpretes | 304 | 98 % |
| Con ciudad | 286 | 93 % |
| Con dirección | 232 | 75 % |
| Con provincia | **29** | **9 %** |
| Con excerpt | 23 | 7 % |
| Con ticket_url | 19 | 6 % |
| Con imagen (destacada / media) | 11 / 8 | ~3 % |
| Con precio / gratis | 8 / 5 | 3 % / 2 % |
| Con end_at | 3 | 1 % |
| Con venue, organización, coordenadas | 0 | 0 % |
| Con festival, enciclopedia o peña | 0 | 0 % |

Futuros por provincia: 1 sin provincia, 1 en Buenos Aires y 1 en Santa Fe. Sin colisiones de slug evento/provincia.

**Lectura:**

1. La ficha de evento es, en el 99 % de los casos, la ficha de un **evento pasado**: es lo que el visitante encuentra al llegar desde Google. El caso "evento realizado" es el principal, no un caso borde.
2. El único vínculo con cobertura real es **evento → intérprete** (98 %). La continuidad tiene que apoyarse en el artista, no en la provincia ni en el festival.
3. Los bloques por provincia quedan casi vacíos mientras `province_id` cubra el 9 %. La ciudad sí está cargada (93 %), por lo que la provincia se puede completar a partir de ella.
4. Festival, enciclopedia y peñas tienen 0 vínculos: hoy esos bloques no mostrarían nada.
5. El problema de fondo de la cartelera es la **oferta**: hay 3 eventos futuros. Ninguna mejora de la ficha compensa eso.

## 2. Actores afectados

- Visitante que llega desde Google a un evento puntual (el principal tráfico orgánico de la ficha).
- Visitante que navega la cartelera y quiere comparar opciones.
- Editor o colaborador que carga eventos en el backend.
- Motores de búsqueda (rich results de `Event`, enlazado interno).

## 3. Alcance

### Fase 1: ficha usable (sin cambios de BD)

1. Ancho completo: quitar el contenedor anidado y la columna `lg:w-2/3`, y respetar el contenedor del layout como hacen Festivales y Peñas.
2. Buscador en la ficha: extraer los filtros a `frontend/shows/_filters.blade.php` y reutilizarlos en el índice y en la ficha. En la ficha va una variante compacta (provincia, mes, texto libre), plegada en mobile y sin el `datalist` de intérpretes.
3. Ficha técnica visible arriba (above the fold): fecha y hora de inicio y fin, lugar (venue o ciudad), dirección, provincia enlazada a su cartelera, precio o "Gratis", y botón "Comprar entradas / Más info" si hay `ticket_url`. Un enlace "Cómo llegar" a Google Maps si hay coordenadas o dirección, como enlace simple y no como iframe.
4. Artistas siempre visibles (los `interpretes` activos con tarjeta), sin depender del piloto Festival Vivo, igual que en el listado.
5. Estado del evento (caso principal según la auditoría): si `start_at` (o `end_at`) ya pasó, mostrar el aviso "Este evento ya se realizó". La continuidad pasa a centrarse en el artista: ficha y bio, próximas fechas del artista si las hay y, si no, otras presentaciones registradas del artista.
6. Breadcrumb `Cartelera > {Provincia} > {Evento}`.
7. Bloques de continuidad:
   - "Próximos eventos en {Provincia}": hasta 3 eventos futuros publicados, sin incluir el actual.
   - "Más de {Artista principal}": primero sus próximas fechas; si no tiene, sus presentaciones anteriores (hasta 3) y el enlace a la biografía. Es el bloque prioritario, con 98 % de cobertura.
   - "Explorar por provincia": solo provincias con eventos futuros publicados, con su cantidad y en caché. Se aplica también al índice y corrige el `take(8)` alfabético.
8. JSON-LD de la ficha completo: `eventStatus`, `eventAttendanceMode`, `endDate` si existe, `location` como `Place` con `PostalAddress` (`addressLocality`, `addressRegion`, `addressCountry: AR`), `offers` si hay `ticket_url`, `price_text` o `is_free`, y `organizer` si hay organización. No declarar precio ni disponibilidad que no estén cargados.
9. Quitar las consultas muertas (`$ultimos_shows`, `$noticiasRelacionadas`).

### Fase 1b: completar provincia (datos, requiere autorización)

- Script read-only que proponga `province_id` a partir de `city` (y `address`), con nivel de confianza. Genera un `.sql` revisable; el usuario lo aprueba y ejecuta. Sin provincia, el breadcrumb, los bloques por provincia y las landings de provincia no funcionan para el 91 % de los eventos.

### Fase 2: relaciones entre entidades (sin migraciones)

> Según la auditoría, festival, enciclopedia y peñas tienen 0 vínculos. Prioridad dentro de la fase: (1) noticias derivadas de los artistas del evento, porque el 98 % tiene artistas; (2) selectores del backend, para que los vínculos empiecen a existir; (3) los bloques de festival, enciclopedia y peñas, que se implementan livianos y solo se renderizan si hay ítems.

1. `Event::knowledgeArticles()` como relación inversa del pivot `event_knowledge_article`, que ya existe.
2. Bloques en la ficha, cada uno visible solo si tiene contenido:
   - "Forma parte de": festivales relacionados (`event_festival`).
   - "Artistas en escena": ya incluido en la fase 1.
   - "Historia y contexto": artículos de enciclopedia (`event_knowledge_article`).
   - "Peñas": `penia_profile_event`, solo si `FEATURE_PENIA_DIRECTORY=true`.
   - "Noticias relacionadas", **derivadas**: noticias publicadas del festival del evento (`festival_news`) y de sus artistas (`interprete_noticia` / `news.interprete_id`). Hasta 3, ordenadas por `published_at`, sin duplicados.
3. Backend de eventos: selects Select2 para festivales, artículos de enciclopedia y peñas, más los campos `end_at` y `excerpt`, con sincronización de los pivots existentes.
4. Instrumentación: reutilizar `x-content-journey.section` con nombres de módulo nuevos (`event_related_*`), sin datos personales.

### Fuera de alcance

- Crear un pivot `event_news` o cualquier migración. Solo se evaluará si las noticias derivadas resultan insuficientes.
- Una página propia de Venue o Sala.
- Exportar al calendario (.ics).
- Cambiar URLs, slugs o el patrón de rutas de la cartelera.
- Modificar el piloto Festival Vivo en Festival y Artista.
- Cargar o curar datos de eventos.

## 4. Historias de usuario

- **HU1.** Como visitante que llega a un evento, quiero ver de un vistazo cuándo, dónde, cuánto cuesta y quién toca, para decidir si voy.
- **HU2.** Como visitante, quiero buscar otro evento o cambiar de provincia sin volver al listado.
- **HU3.** Como visitante, quiero descubrir eventos cercanos o del mismo artista cuando el evento ya pasó o no me sirve.
- **HU4.** Como visitante, quiero saltar al festival, a los artistas, a la historia y a las noticias vinculadas al evento.
- **HU5.** Como editor, quiero vincular desde el formulario del evento su festival, artículos y peñas.

## 5. Reglas de negocio

- Solo se listan entidades públicas según sus scopes canónicos: `Event::publiclyVisible()`, `publishedVisible()`, `visible()` e intérpretes con `estado = 1`.
- Los bloques de continuidad solo incluyen eventos futuros (`start_at >= hoy`), excluyen el actual y no se duplican entre sí.
- Un bloque sin ítems no se renderiza: nada de títulos vacíos.
- "Gratis" solo se muestra si `is_free = true`. El precio solo se muestra si hay `price_text`. Nunca se infieren.
- El enlace de entradas es externo: `target="_blank" rel="noopener nofollow"`.
- Las peñas respetan el flag `FEATURE_PENIA_DIRECTORY`.

## 6. Criterios de aceptación

- **CA1.** Dado un evento publicado con fecha, ciudad, provincia, precio y `ticket_url`, cuando abro su ficha, entonces veo esos datos antes del cuerpo y un botón de entradas.
- **CA2.** Dado cualquier evento, cuando abro su ficha en desktop, entonces el contenido ocupa todo el ancho de la columna principal del layout.
- **CA3.** Dado la ficha de un evento, cuando uso el buscador y envío provincia o texto, entonces llego a `cartelera.index` con esos filtros y la URL canónica de provincia si corresponde.
- **CA4.** Dado un evento con artistas activos y el flag Festival Vivo apagado, cuando abro la ficha, entonces veo las tarjetas de los artistas.
- **CA5.** Dado un evento ya realizado, cuando abro su ficha, entonces responde 200, muestra "Este evento ya se realizó" y lista próximos eventos de la provincia o del artista si existen.
- **CA6.** Dado un evento con provincia, cuando abro la ficha, entonces el breadcrumb es `Cartelera > Provincia > Evento` y el de provincia enlaza a su cartelera.
- **CA7.** Dado provincias con y sin eventos futuros, cuando veo "Explorar por provincia" en el índice o en la ficha, entonces solo aparecen las que tienen eventos, con su cantidad.
- **CA8.** Dado un evento vinculado a un festival, a un artículo y a noticias de sus artistas, cuando abro la ficha, entonces veo los bloques "Forma parte de", "Historia y contexto" y "Noticias relacionadas" (fase 2).
- **CA9.** Dado contenido relacionado no publicado, cuando abro la ficha, entonces no aparece.
- **CA10.** Dado la ficha, cuando valido el JSON-LD, entonces es un `Event` válido con `eventStatus`, `eventAttendanceMode` y `location.address`, y sin `offers` si no hay datos de precio ni entradas.

## 7. Casos borde

- Evento sin provincia: no hay breadcrumb intermedio ni bloque de provincia, y se usa "Explorar por provincia" como fallback.
- Evento sin artistas, sin imagen (fallback editorial actual) o sin `body`.
- Evento con varios festivales (hoy se limita a 2) o varios artistas (se limita a 6).
- Evento de varios días (`end_at` en otro día) y evento en curso.
- Slug de evento que coincide con el slug de una provincia: hoy `resolve()` prioriza la provincia. No se cambia, pero se agrega un test que lo documente.
- `ticket_url` inválida o sin esquema: no se muestra el botón.

## 8. Impacto técnico, editorial, SEO y de performance

- **Técnico:** `ShowsController`, `Event`, las vistas `shows/*`, el nuevo parcial `_filters` y un posible componente `x-event-facts`. En la fase 2, también `Backend\EventController`, el form request y `backend/events/form.blade.php`.
- **SEO:** sin cambios de URL ni de canonical. Mejoran el enlazado interno (provincia, artistas, festival, enciclopedia), el contenido útil visible y el rich result de `Event`. Riesgo bajo.
- **Performance:**
  - Consultas acotadas con `limit` en SQL y eager loading.
  - Provincias con conteo cacheadas y no `Provincia::all()` por cada request (hoy `findProvinciaBySlug()` carga todas las provincias en cada visita a una ficha).
  - Sin `datalist` de ~445 intérpretes en la ficha.
  - Sin iframes de mapa.
  - La imagen hero sigue `eager` + `fetchpriority=high`; las tarjetas, `lazy`.
  - Se debe respetar el presupuesto de HTML de 350 KB.
- **Editorial:** el valor de la fase 2 depende de que existan relaciones cargadas. Conviene una auditoría read-only de cobertura antes de priorizar.

## 9. Preguntas abiertas

0. Con 3 eventos futuros, ¿se activa en paralelo la carga de eventos (`project/docs/ia/agente_carga_eventos.md`) como iniciativa propia? Es la palanca principal de la cartelera.
1. ¿Los bloques de relaciones de la ficha (festival, artistas) se muestran para todos los eventos, o siguen bajo el flag y allowlist de Festival Vivo mientras dure el piloto? **Recomendación:** artistas siempre visibles (fase 1). Festival y el resto, fuera del allowlist pero con el mismo tracking, para medir.
2. ¿Los eventos pasados (306) siguen indexables con el aviso? Recomendación: sí por ahora, con el bloque del artista como valor. Revisar en GSC qué fichas pasadas reciben impresiones antes de cualquier `noindex` masivo.
3. ¿Alcanza con noticias derivadas, o se quiere vinculación editorial directa (pivot nuevo, fuera de alcance)?
4. ¿Alcanza con un buscador compacto en la ficha (provincia, mes y texto), o tiene que incluir también intérprete?
5. ¿La fase 2.3 (backend) entra en este cambio o va en otro?

## 10. Validación prevista

- Tests Feature (`DatabaseTransactions`) en `tests/Feature/Events/EventDetailTest.php`:
  - Datos clave.
  - Artistas sin el flag.
  - Evento pasado.
  - Breadcrumb.
  - Bloques vacíos no renderizados.
  - Contenido no publicado excluido.
  - JSON-LD.
  - Provincias con eventos.
  - Colisión slug/provincia.
- Ejecutar `PublicEventVisibilityTest` y la suite pública de calidad (presupuesto HTML y estructura semántica).
- QA visual desktop/mobile de la ficha y del índice, y Rich Results Test sobre una ficha local o de staging.
- Revisión de cantidad de consultas en la ficha, antes y después.

## 11. Ajustes post-QA (2026-10-03, aprobados por el usuario)

- **A.** El buscador de la ficha es el mismo del índice de la sección (parcial `_filters` completo, con intérprete y fecha). Se descarta la variante compacta. Supera la decisión anterior de "buscador compacto sin datalist": suma ~445 opciones de `datalist` al HTML de la ficha, dentro del presupuesto de 350 KB.
- **B.** Las imágenes de evento, presentaciones anteriores y biografía caen al fallback editorial si la entidad no tiene imagen o si el archivo referenciado en la base no existe en el disco. El cambio es en `EditorialImageResolver` y alcanza a todas las secciones que lo usan. Solo se verifica en discos locales; las URLs absolutas externas se respetan.
