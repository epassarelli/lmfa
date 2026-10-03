# Especificación del tablero SEO por sección — 2026-09-14

**Tarea Drive:** `BL-0010V`  
**Estado de esta entrega:** diseño operativo y reproducible; no se materializa un tablero con datos simulados.

## Propósito

Medir por sección si las superficies ya existentes ganan visibilidad útil sin confundir una subida de impresiones, contenido nuevo o tráfico de marca con una mejora SEO real. El tablero debe comparar períodos equivalentes, conservar el detalle consulta–página y señalar posibles canibalizaciones antes de que se tomen decisiones de indexación.

## Contrato de fuentes

| Fuente | Extracción mínima | Clave de unión/segmentación | Uso permitido |
| --- | --- | --- | --- |
| Google Search Console | Fecha, consulta, página, clics, impresiones, CTR y posición | Página normalizada y familia de ruta | Rendimiento orgánico y consulta–página; exportar últimos 90 días y comparar con los 90 días previos. |
| GA4 | Fecha, landing page, canal/sesión orgánica, sesiones, usuarios, engagement y conversiones disponibles | Landing page normalizada | Calidad/valor del tráfico orgánico; no reemplaza la atribución de GSC. |
| Sitemap/rutas | URL y familia | Regla de prefijo documentada | Denominador de cobertura e inventario; no inferir indexación sólo por estar en sitemap. |
| Auditorías editoriales | ID, sección, prioridad/score, faltantes y fecha | ID o URL cuando exista | Relacionar oportunidad SEO con deuda de calidad, sin sustituir datos de demanda. |

No cargar credenciales en el repositorio ni duplicar fuentes canónicas. Si se usa Google Sheets o Looker Studio, su función es de visualización/consulta; los exports originales conservan fecha, filtro y período.

## Segmentos de URL

| Segmento | Regla de ruta inicial | Objetivo de lectura |
| --- | --- | --- |
| Artistas | `/{slug}` y subrutas de artista, excluyendo rutas reservadas | Navegación de artista y recorrido hacia biografía, obras, discos y agenda. |
| Biografías | `/biografias-de-artistas-folkloricos` y subrutas | Intención biográfica, diferenciada de la ficha de artista. |
| Letras | `/letras-de-canciones-folkloricas` y subrutas de letras por artista | Consulta de obra/intérprete y CTR de resultados con posición competitiva. |
| Discografías | `/discografias-del-folklore-argentino` y `/[artista]/discografia` | Intención de catálogo, álbum y repertorio. |
| Noticias | `/noticias-del-folklore-argentino` y subrutas | Actualidad; separar de consultas informativas evergreen. |
| Eventos | `/cartelera-de-eventos-folkloricos` y subrutas | Descubrimiento por fecha, artista y territorio. |
| Festivales | `/festivales-y-fiestas-tradicionales` y subrutas | Descubrimiento territorial/temporal y fichas estables. |
| Evergreen | `/enciclopedia` y subrutas | Preguntas culturales estables y profundidad temática. |

La normalización debe conservar el path canónico, eliminar parámetros de tracking y registrar por separado cualquier URL con query que GSC reporte. Las rutas reservadas se excluyen del segmento de artista; no se resuelven mediante una regla amplia sin una lista comprobable.

## KPIs y cálculos

| KPI | Fuente | Cálculo | Lectura correcta |
| --- | --- | --- | --- |
| Clics orgánicos | GSC | Suma de clics por sección/período | Resultado de visibilidad y snippet, no calidad editorial por sí solo. |
| Impresiones | GSC | Suma de impresiones | Señal de cobertura; interpretar junto con posición y CTR. |
| CTR ponderado | GSC | `clics / impresiones`; nulo cuando impresiones = 0 | No promediar CTR de filas, porque distorsiona el peso de cada consulta. |
| Posición ponderada | GSC | Promedio ponderado por impresiones de la posición | Comparar sólo períodos de igual duración y mismo filtro. |
| Sesiones orgánicas | GA4 | Sesiones de canal orgánico por landing/segmento | Contraste de valor de tráfico; puede diferir de clics GSC por metodología. |
| Engagement/conversión | GA4 | Métrica nativa definida y documentada antes de usarla | Indicador secundario; no inventar conversiones si no hay evento confiable. |
| Cobertura útil | Sitemap + GSC | URLs con impresiones / URLs elegibles | No equivale a “indexadas”; muestra exposición medida. |
| Oportunidad de CTR | GSC | Consulta–página con impresiones altas, posición media 4–10 y CTR inferior a pares comparables | Candidata a mejorar snippet, intención y enlazado; no prueba causalidad. |
| Riesgo de canibalización | GSC | Misma consulta distribuida entre dos o más URLs de una familia | Requiere lectura humana de intención y canonical antes de actuar. |

## Vistas obligatorias

1. **Resumen ejecutivo:** período actual contra período comparable anterior, por sección, con clics, impresiones, CTR ponderado, posición y sesiones orgánicas.
2. **Oportunidades:** consulta–página con impresiones, posición, CTR y familia de URL; orden inicial por impresiones y oportunidad de CTR.
3. **Canibalización:** consulta, URLs receptoras, distribución de clics/impresiones, familia y decisión pendiente. No automatiza consolidaciones.
4. **Cobertura y calidad:** URLs con impresiones vinculadas al score/faltantes editoriales cuando la unión es segura.
5. **Control de datos:** fecha de última extracción, rango, filtros, filas, URLs sin clasificar, filas excluidas y cambios de reglas de segmentación.

## Frecuencia, responsables y gates

| Cadencia | Responsable operativo | Salida | Gate |
| --- | --- | --- | --- |
| Semanal | Agente/analista | Actualizar extracción y control de calidad; señalar cambios relevantes | No declarar tendencia con menos de 28 días comparables. |
| Mensual | Eduardo + agente/analista | Priorizar máximo tres oportunidades con evidencia consulta–página | Aprobar sólo cambios reversibles locales; noindex, canonical, consolidación o nuevas URLs requieren revisión humana. |
| Trimestral | Eduardo | Revisar segmentos, páginas ganadoras, competencia y objetivos | Alimenta auditoría `BL-0010Y`; no sustituye el trabajo de autoridad/backlinks. |

## Línea base conocida y condición de activación

La única referencia cuantitativa disponible en esta ejecución es la línea base GSC de 28 días registrada el 2026-08-10: 189 clics, 26.767 impresiones y CTR 0,71 %. Sus cuatro oportunidades conocidas pertenecen a Letras y están detalladas en `seo-keyword-intent-map-2026-09-14.md`.

Para activar el tablero se necesita un export verificable de GSC y GA4 con los campos del contrato de fuentes. Sin él, esta especificación permite construir la estructura y controlar la calidad, pero no puede producir datos reproducibles por sección ni comparaciones temporales válidas.

## Revisión independiente

- Los KPIs, segmentos y frecuencia solicitados por `BL-0010V` quedaron definidos sin métricas de vanidad y con separación explícita de marca/no marca cuando el export lo permita.
- El diseño evita consultas adicionales al frontend y no modifica rutas, sitemaps, canonicals, contenido ni producción; su impacto de performance en el portal es nulo.
- La definición completa de terminado queda pendiente de la carga de datos verificables y de una primera comparación temporal por sección.
