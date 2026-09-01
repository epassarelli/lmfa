# BL-0022C — Primer recorrido transversal: Festival vivo

> Estado: especificacion implementable, pendiente de aprobacion funcional y desarrollo.
> Alcance: entidades publicas activas. Radios y Peñas quedan expresamente fuera.

## Decision de producto

El primer recorrido transversal sera:

`Festival evergreen -> proxima fecha -> artista -> biografia/repertorio -> actualidad o regreso a agenda`.

El punto de entrada principal es la ficha de Festival porque ya concentra cuatro relaciones editoriales explicitas (`events`, `interpretes`, `noticias` y `knowledgeArticles`) y combina intencion cultural estable con una accion temporal concreta. El recorrido permite que una visita informativa continúe hacia la agenda y luego profundice en artistas, letras, discos y noticias sin crear una entidad nueva.

No se activaran Radios ni Peñas en este incremento. Tampoco se inferiran relaciones por coincidencia de texto: todo enlace transversal principal debe provenir de un campo o pivot persistido.

## Resultado esperado

Una persona que llega desde Google a un festival debe poder responder, en este orden:

1. que es y donde se realiza;
2. cual es su proxima fecha publica;
3. que artistas estan vinculados al festival y cuales integran la proxima fecha documentada;
4. donde conocer su trayectoria y repertorio;
5. que novedades o contexto cultural amplian la visita.

Hipotesis: exponer esta secuencia con jerarquia uniforme aumentara paginas por sesion y visitas a Cartelera y miniportales de artistas, sin degradar el valor SEO de la ficha evergreen.

### Aprobacion funcional requerida

Antes de desarrollar, aprobar en conjunto estas cuatro decisiones:

1. Festival es la puerta de entrada del primer recorrido transversal.
2. Solo relaciones editoriales persistidas alimentan los bloques principales.
3. La auditoria selecciona por trafico y cobertura hasta 5 festivales completos, con un minimo de 2, mediante allowlist.
4. La expansion exige 7 dias de estabilidad tecnica y, para la decision de producto, al menos 500 vistas elegibles o 28 dias —lo que ocurra mas tarde—, con un maximo de 56 dias.

La recomendacion es aprobar las cuatro y ejecutar los tres PRs pequenos definidos al final, sin sumar otra entidad al MVP.

## Alcance del MVP

### Superficies que cambian

| Superficie | Modulo transversal | Destinos | Maximo |
|---|---|---|---:|
| Detalle de Festival | `Proximas fechas` | Eventos publicos futuros del festival | 3 |
| Detalle de Festival | `Artistas vinculados al festival` | Miniportal o biografia del artista activo mediante `festival_interprete` | 6 |
| Detalle de Festival | `Historia y contexto` | Evergreen relacionado | 4 |
| Detalle de Festival | `Noticias relacionadas` | Noticias publicadas | 3 |
| Detalle de Evento | `Este evento forma parte de` | Festival publicado | 2 |
| Detalle de Evento | `Artistas en escena` | Miniportal del artista activo | 6 |
| Miniportal de Artista | `Donde verlo` | Eventos publicos futuros | 3 |
| Miniportal de Artista | `Festivales relacionados` | Festivales publicados vinculados | 3 |

El miniportal conserva Biografia, Letras, Discografia y Noticias como siguientes pasos existentes. El MVP no crea una nueva landing ni cambia URLs.

La continuidad principal usa siempre el primer Evento que cumpla el contrato publico, ordenado por `start_at ASC`. Sus artistas de una edicion concreta proceden exclusivamente de `Event::interpretes()`; `Festival::interpretes()` no debe rotularse como cartel de una edicion.

### Fuera de alcance

- alta, remodelacion o publicacion de Radios y Peñas;
- recomendaciones personalizadas por usuario;
- relaciones calculadas con IA o similitud semantica;
- compra de entradas dentro de MFA;
- cambios masivos de contenido o backfill automatico de pivots;
- una taxonomia transversal nueva;
- cambio de las URLs legacy `/shows` en este release.

## Contrato de visibilidad

Un contenido solo puede aparecer en el recorrido cuando cumple su regla canonica:

| Entidad | Regla obligatoria |
|---|---|
| Festival | `Festival::publishedVisible()` |
| Evento | `Event::publiclyVisible()` y `start_at >= now()->startOfDay()` |
| Artista | `estado = 1` |
| Noticia | `News::publishedVisible()` |
| Evergreen | `KnowledgeArticle::visible()` |

Reglas adicionales:

- no mostrar el contenido actual dentro de su propio bloque relacionado;
- eliminar duplicados por `id` antes de renderizar;
- ordenar eventos por `start_at ASC`, noticias por `published_at DESC`, artistas por `event_interprete.sort_order` cuando el origen sea un evento y luego por nombre;
- un bloque vacio no se renderiza y no se reemplaza con contenido no relacionado;
- las relaciones editoriales explicitas tienen prioridad sobre los fallbacks geograficos o temporales;
- un evento inactivo nunca puede aparecer aunque este editorialmente publicado;
- una fecha pasada puede permanecer en la historia del festival, pero no en `Proximas fechas`.

## Arquitectura propuesta

### Relaciones existentes que se reutilizan

- `Festival::events()` mediante `event_festival`;
- `Festival::interpretes()` mediante `festival_interprete`;
- `Festival::noticias()` mediante `festival_news`;
- `Festival::knowledgeArticles()` mediante `knowledge_article_festival`;
- `Event::festivales()` mediante `event_festival`;
- `Event::interpretes()` mediante `event_interprete`;
- `Interprete::events()` mediante `event_interprete`;
- `Interprete::festivales()` mediante `festival_interprete`.

No se requiere migracion para el MVP.

### Capa de consulta

Crear `App\Services\Product\FestivalJourneyService`. Los controladores entregan la entidad raiz y reciben un DTO/array de colecciones ya filtradas. Las vistas no ejecutan consultas ni acceden a relaciones no cargadas.

Contrato sugerido:

```php
public function forFestival(Festival $festival): FestivalJourney;
public function forEvent(Event $event): EventJourney;
public function forArtist(Interprete $artist): ArtistJourney;
```

Los objetos de salida deben contener colecciones nombradas (`upcomingEvents`, `artists`, `festivals`, `knowledgeArticles`, `news`) y nunca builders. Esto permite verificar limites y visibilidad antes de la vista.

No usar una consulta polimorfica generica en el primer incremento. Las reglas de publicacion y orden difieren por entidad; explicitar cada query reduce el riesgo de filtrar con columnas legacy.

### Queries y prevencion de N+1

Para una ficha de Festival:

```php
$festival->load([
    'events' => fn ($q) => $q->publiclyVisible()
        ->where('start_at', '>=', now()->startOfDay())
        ->with(['images', 'provincia:id,nombre', 'interpretes' => fn ($a) => $a
            ->where('estado', 1)->with('images')])
        ->orderBy('start_at')->limit(3),
    'interpretes' => fn ($q) => $q->where('estado', 1)
        ->with('images')->orderBy('interprete')->limit(6),
    'knowledgeArticles' => fn ($q) => $q->visible()
        ->with(['category:id,name,slug', 'images'])
        ->latest('published_at')->limit(4),
    'noticias' => fn ($q) => $q->publishedVisible()
        ->with(['categoria:id,nombre', 'images', 'interprete:id,interprete,slug'])
        ->latest('published_at')->limit(3),
]);
```

Para Evento y Artista se aplican los mismos scopes y limites desde sus relaciones existentes. Toda relacion que use `EditorialImageResolver` debe llegar cargada junto con `images`; el resolver no debe disparar lazy loading.

Notas de implementacion:

- seleccionar solo columnas necesarias cuando esto no rompe accessors ni el resolvedor de imagen;
- no reutilizar `festivalCardRelations()` para tarjetas: hoy carga colecciones completas de artistas y eventos aunque la tarjeta solo muestra ubicacion, mes, texto e imagen;
- para imagen fallback de una tarjeta, cargar como maximo el primer artista/evento elegible mediante una relacion dedicada o resolver previamente una referencia acotada; no cargar todas las relaciones;
- habilitar `Model::preventLazyLoading()` en tests para detectar regresiones;
- medir primero la cantidad de queries de la ficha actual con una fixture representativa;
- el recorrido no puede agregar mas de cinco queries sobre esa linea base y la cantidad total debe permanecer constante al pasar de 1 a 6 artistas y de 1 a 3 eventos;
- el presupuesto absoluto se fija en CI despues de esa medicion, nunca antes ni mediante un numero arbitrario.

### Componentes de presentacion

Crear componentes semanticos y reutilizar las tarjetas existentes dentro de ellos:

- `x-content-journey.section`: titulo H2, bajada opcional, coleccion y CTA;
- `x-content-journey.link`: enlace con atributos de analitica;
- `x-show-card`, `x-biografia-card`, `x-noticia-card` y `x-festival-card`: mantener como tarjetas de entidad, corrigiendo solo los datos que consumen si fuese necesario.

No construir URLs por comparacion del texto del encabezado, como hace actualmente el detalle de Festival. Cada item debe entregar su URL canonica (`getUrl()` o route explicita por tipo) desde un presentador/DTO.

## Reglas editoriales

- La ficha de Festival sigue siendo evergreen: historia, identidad, lugar, mes habitual y fuentes verificables.
- Una edicion o fecha concreta pertenece a Evento; no sobrescribir el cuerpo estable del Festival con una cartelera anual.
- Artistas vinculados deben corresponder al festival de forma documentada. Si el dato corresponde a una edicion, debe existir tambien el Evento de esa edicion cuando sea publicable.
- Noticias son actualidad; no se usan para completar automaticamente la historia estable.
- El cuerpo editorial no contiene H1. La plantilla publica mantiene un unico H1.
- No publicar FAQ ni `FAQPage` salvo preguntas y respuestas editoriales reales y visibles.
- No mostrar una imagen heredada como si fuera propia sin texto alternativo coherente con la entidad visible.
- El CTA `Comprar entradas` solo aparece cuando `ticket_url` esta presente en el Evento; debe ser externo, identificable y medido.

### Gate editorial minimo para entrar al recorrido

| Entidad | Minimo |
|---|---|
| Festival | titulo, slug, excerpt, cuerpo estable, provincia o localidad, `seo_title`, `meta_description` y estado publicado |
| Evento | titulo, fecha futura, estado activo/publicado, lugar o ciudad y al menos una relacion con Festival o Artista |
| Artista | nombre, slug, estado activo y biografia de al menos 150 palabras visibles |
| Noticia | titulo, cuerpo, categoria, publicacion visible y relacion explicita |
| Evergreen | titulo, cuerpo, categoria, publicacion visible y `last_verified_at` cuando el articulo describa datos sujetos a cambio |

La auditoria debera reportar relaciones invalidas y destinos que no superan el gate; no debe repararlos silenciosamente.

## SEO y datos estructurados

- conservar canonical autorreferencial en Festival, Evento y Artista;
- mantener un solo H1 por pagina y usar H2 para los modulos transversales;
- usar anchors descriptivos: `Proxima fecha de Festival X`, `Biografia de Artista Y`, no `Ver mas`;
- Festival evergreen conserva `Article`; cuando haya fechas no debe declarar una fecha inventada como si todo el festival fuera un evento unico;
- Evento usa `Event`, con `performer` como array de artistas publicos y `superEvent` apuntando al Festival solo si la relacion esta persistida;
- Artista usa `Person` o `MusicGroup` segun `artist_type` y puede exponer `subjectOf` solo para URLs publicas realmente vinculadas;
- el JSON-LD debe generarse con `@json`, no con interpolacion manual, para evitar JSON invalido;
- los enlaces del recorrido son HTML server-side rastreable; JavaScript solo agrega medicion;
- no agregar combinaciones nuevas al sitemap: el MVP mejora enlazado interno de URLs ya canonicas.

## Analitica

El sitio ya inicializa `dataLayer` antes de cargar GA4 de forma diferida. Agregar atributos a cada enlace:

```html
data-journey-link
data-source-type="festival"
data-source-id="123"
data-destination-type="event"
data-destination-id="456"
data-module="upcoming_events"
data-position="1"
```

Un listener delegado envia el evento recomendado de GA4 `select_content`:

```js
gtag('event', 'select_content', {
  content_type: 'festival_to_event',
  item_id: '456',
  journey_source_type: 'festival',
  journey_source_id: '123',
  journey_module: 'upcoming_events',
  journey_position: 1
});
```

Cada modulo renderizado emite una sola impresion `view_item_list`, con `items` que incluyan al menos el `item_id` de cada destino. Los `item_list_id` son `upcoming_events`, `festival_artists`, `festival_context`, `festival_news`, `event_festivals`, `event_artists`, `artist_events` y `artist_festivals`. Esta impresion es el denominador del CTR; no se calcula CTR usando todas las vistas de pagina cuando el bloque no existe.

No enviar titulo libre, correo, usuario ni otros datos personales. Los identificadores son IDs internos de contenido.

Metricas de decision:

1. CTR de cada modulo transversal (`select_content / impresiones view_item_list del modulo`);
2. porcentaje de sesiones con una segunda pagina de otra entidad;
3. avance Festival -> Evento -> Artista dentro de la misma sesion;
4. clic saliente a `ticket_url`, separado de los enlaces editoriales;
5. engagement y paginas por sesion de las fichas con recorrido frente a la linea base;
6. 404 de destinos relacionados y bloques vacios.

La decision se toma al alcanzar al menos 500 vistas elegibles de modulos o 28 dias, lo que ocurra mas tarde, con un maximo de 56 dias. Se compara la allowlist contra una cohorte no habilitada de trafico y cobertura editorial semejantes, controlando estacionalidad. Exito inicial: `+15%` en sesiones con segunda entidad desde las fichas incluidas, sin caida mayor al `5%` en engagement y con cero enlaces a contenido no publico.

## Rollout

1. **Preparacion:** auditoria read-only de pivots para medir cobertura y destinos invalidos. Capturar linea base y seleccionar por trafico/cobertura hasta cinco festivales con Evento futuro publico, artistas activos y relaciones suficientes. La auditoria debe demostrar la cantidad elegible antes de aprobar la allowlist.
2. **Backend oculto:** servicio, DTOs y tests bajo `config('features.festival_journey') = false`.
3. **Piloto:** habilitar por lista configurable los festivales realmente elegibles, con un objetivo de cinco y un minimo de dos. No crear relaciones artificiales ni usar porcentaje aleatorio. Si no existen dos casos elegibles, el gate falla y se evalua el recorrido alternativo `Artista -> repertorio -> agenda`.
4. **Validacion de 7 dias:** revisar errores, queries, CTR por modulo y enlaces externos.
5. **Expansion:** solo despues de cumplir la decision de producto de 500 vistas elegibles/28–56 dias, ampliar a 25 festivales y luego a todos los que superen el gate editorial.
6. **Cierre:** retirar allowlist solo cuando CI, auditoria editorial y metricas cumplan los umbrales.

Rollback: apagar el feature flag. No se eliminan pivots ni contenido, por lo que el rollback no requiere migracion.

La allowlist almacena IDs de Festival y gobierna las tres superficies de forma transitiva:

- Festival habilitado: su ID esta en la allowlist;
- Evento habilitado: pertenece mediante `event_festival` a por lo menos un Festival incluido;
- Artista habilitado: esta vinculado directamente al Festival incluido o participa mediante `event_interprete` en uno de sus Eventos elegibles.

Un Evento o Artista que no cumpla esa regla conserva la superficie previa aunque tenga otras relaciones. Esto mantiene separada la cohorte de control.

## Plan de implementacion en PRs pequenos

### PR 1 — Consulta y seguridad

- `FestivalJourneyService` y DTOs;
- relaciones inversas faltantes solo si son necesarias para las queries;
- scopes canonicos en todas las colecciones;
- feature flag y allowlist;
- tests unitarios/feature de visibilidad, orden, limites y queries.

### PR 2 — Festival y Evento

- componentes `content-journey`;
- detalle de Festival con jerarquia definida;
- detalle de Evento con Festival y Artistas;
- detalle de Evento con fecha y hora, sede/direccion, artistas enlazados y CTA externo de entradas cuando exista `ticket_url`;
- JSON-LD de Evento corregido para multiples performers y `superEvent` real;
- responsive y accesibilidad.

### PR 3 — Artista y medicion

- Festivales relacionados en miniportal;
- instrumentacion `view_item_list`, `select_content` y clic de entradas;
- dashboard/exploracion GA4 documentada;
- piloto por allowlist.

## Matriz de tests obligatorios

### Feature

- Festival muestra solo eventos activos, publicados, sin publicacion programada a futuro y con fecha vigente;
- Evento muestra solo festivales publicados y artistas activos;
- Artista muestra solo eventos publicos futuros y festivales publicados;
- borrador, publicacion futura, evento inactivo y artista inactivo nunca aparecen;
- orden y limites son deterministas;
- bloque vacio no deja titulo ni CTA huerfano;
- todos los enlaces resuelven `200` y usan URL canonica;
- el flag apagado conserva exactamente la superficie previa en Festival, Evento y Artista por separado;
- allowlist habilita el Festival configurado y solo sus Eventos y Artistas transitivamente elegibles.

### Rendimiento

- lazy loading prohibido en la prueba del recorrido;
- cantidad de queries constante al multiplicar items relacionados;
- presupuesto constante y no mayor a linea base mas cinco queries para el detalle de Festival con todos los bloques;
- cada coleccion llega limitada desde SQL, no mediante `take()` posterior en Blade.

### SEO y accesibilidad

- un unico H1 y encabezados de bloque H2;
- canonical correcto y sin nuevas URLs indexables;
- JSON-LD parseable, performers multiples y sin entidades no publicas;
- anchors con nombre accesible y foco visible;
- tarjetas sin imagen propia conservan dimensiones para evitar CLS;
- atributos de analitica no reemplazan `href` ni dependen de JavaScript.

### Analitica

- cada enlace incluye fuente, destino, modulo y posicion;
- cada modulo visible emite una sola impresion `view_item_list`, que actua como denominador del CTR;
- un clic emite un solo `select_content`;
- clic de entradas se distingue con `journey_module=ticket`;
- no se incluyen parametros con datos personales o cuerpo editorial.

## Hallazgos del codigo actual que condicionan el desarrollo

1. El detalle de Festival usa `publishedVisible()` para Eventos, pero no `publiclyVisible()`; puede exponer un evento `status=inactive`.
2. El miniportal del Artista tambien usa `publishedVisible()` en `eventos`; debe aplicar la regla publica completa.
3. El detalle de Evento no carga ni muestra `festivales`; tampoco expone los artistas como enlaces visibles, aunque las relaciones existen.
4. `noticiasRelacionadas = $show->noticias ?? collect()` no tiene una relacion `Event::noticias()` definida y no debe formar parte del MVP sin contrato de datos.
5. El detalle de Festival limita relaciones con `take()` en Blade despues de cargarlas completas; los limites deben bajar a SQL.
6. `festivalCardRelations()` carga artistas y eventos completos para cada tarjeta, aun cuando el componente no los muestra directamente; esto aumenta memoria y queries de media.
7. La URL de relacionados en Festival se decide comparando el texto del encabezado; debe reemplazarse por DTOs tipados.
8. El JSON-LD de Evento representa un unico `performer`, interpola strings manualmente y omite URL/canonical del evento; debe corregirse en el PR de presentacion.

Estos hallazgos no justifican activar Radios o Peñas ni ampliar el alcance. Son deuda directamente ligada al recorrido elegido.

## Criterio de listo de BL-0022C

La tarea de producto queda especificada cuando este documento es aprobado. La implementacion solo puede marcarse hecha cuando:

- los tres PRs estan fusionados y con CI verde;
- el feature flag esta habilitado al menos para el piloto;
- la auditoria no encuentra destinos no publicos;
- existe validacion tecnica estable de 7 dias;
- la decision de producto alcanza 500 vistas elegibles o 28 dias —lo que ocurra mas tarde—, con un maximo de 56 dias;
- se documenta la decision de expandir, ajustar o revertir.
