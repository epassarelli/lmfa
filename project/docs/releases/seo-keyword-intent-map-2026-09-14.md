# Mapa SEO de consultas e intención por sección — 2026-09-14

**Tarea Drive:** `BL-0010O`  
**Alcance:** mapa de intención, destino existente y riesgo de canibalización. No crea URLs, modifica contenidos ni aplica cambios de indexación.

## Evidencia y límite de la lectura

- La línea base validada en Drive para Search Console (28 días, registrada el 2026-08-10) informa **189 clics**, **26.767 impresiones** y **CTR 0,71 %**; no había una exportación más reciente de GSC o GA4 disponible en esta ejecución.
- Las únicas consultas individuales verificadas en esa línea base son de Letras: `eterno amor letra` (1.075 impresiones, posición 7,17, CTR 0,09 %), `aroma de mandarina letra` (911, 4,63, 0,11 %), `dejame que me vaya letra` (897, 7,82, 0 clics) y `fabulas de amor letra` (762, 5,89, 0,26 %).
- Los destinos se comprobaron contra `routes/web.php` local. Las consultas señaladas como **clúster propuesto** describen una intención a medir, no volumen, posición ni demanda confirmados.
- La estrategia respeta `project/docs/01-funcional.md`: no se abren URLs indexables sin intención clara, contenido suficiente y canonización consistente; las páginas territoriales sólo se priorizan donde ya exista una superficie suficiente.

## Matriz priorizada

| Prioridad provisional | Familia | Intención objetivo | Consulta o clúster | Destino existente | Riesgo de canibalización | Acción segura siguiente |
| --- | --- | --- | --- | --- | --- | --- |
| P1 | Letras y canciones | Encontrar la letra y confirmar intérprete/obra | **Verificada:** `eterno amor letra`, `aroma de mandarina letra`, `dejame que me vaya letra`, `fabulas de amor letra`; **propuesto:** `[canción] letra [artista]` | `/letras-de-canciones-folkloricas/{slug}` y `/{artista}/letras/{slug}` | La ficha global y la ficha bajo artista pueden competir por la misma obra; también hay títulos homónimos o versiones distintas. | Extraer en GSC pares consulta–página de estas cuatro consultas; confirmar una URL canónica por obra y diferenciar versión/intérprete en título, H1 y enlaces. No ampliar letras hasta el gate de derechos. |
| P1 | Artistas y biografías | Resolver una búsqueda de persona y aportar perfil/biografía | **Propuesto:** `[artista]`, `[artista] biografía`, `[artista] folklore` | `/{interprete:slug}`, `/{interprete:slug}/biografia` y `/biografias-de-artistas-folkloricos` | La ficha de artista y `/biografia` pueden responder a la misma intención; los 444 pares existentes no se deben consolidar sin evidencia. | Medir en GSC consulta–página por artista. Definir por familia cuál URL lidera intención navegacional y cuál la biográfica; reforzar enlaces internos sin crear rutas nuevas. |
| P1 | Discografías y álbumes | Conocer discografía, álbum y repertorio | **Propuesto:** `[artista] discografía`, `[álbum] [artista]`, `[álbum] canciones` | `/discografias-del-folklore-argentino`, `/{artista}/discografia` y `/{artista}/discografia/{slug}` | Índice, discografía de artista y álbum pueden disputar consultas amplias; los títulos SEO no deben prometer letra cuando no existe. | Cruzar páginas ganadoras con consultas de marca/álbum y conservar una intención por URL. No automatizar ni ampliar letras/contenido protegido sin la decisión de derechos pendiente. |
| P2 | Festivales | Planificar asistencia o investigar una fiesta tradicional | **Propuesto:** `festival folklórico [provincia]`, `fiesta tradicional [provincia]`, `festival [mes] [provincia]` | `/festivales-y-fiestas-tradicionales`, `/provincia/{provinceSlug}`, `/mes/{monthSlug}`, `/provincia/{provinceSlug}/mes/{monthSlug}` y ficha | Las combinaciones provincia/mes pueden competir con la landing o generar superficies delgadas si faltan festivales/datos. | En GSC, comparar landing, filtro y fichas por consulta geográfica. Mantener sólo filtros existentes con resultados suficientes; priorizar completar provincia, localidad, mes y metadatos de los registros P1. |
| P2 | Enciclopedia Evergreen | Resolver una pregunta cultural estable y profundizar | **Propuesto:** `qué es [tema]`, `historia de [tema]`, `[tema] folklore argentino` | `/enciclopedia`, `/enciclopedia/{categorySlug}` y `/enciclopedia/{categorySlug}/{articleSlug}` | Categoría y artículo pueden solaparse en términos genéricos; una noticia no debe competir con un explicador permanente. | Separar intención informativa estable (Evergreen) de actualidad (Noticias) en títulos, enlaces y calendario editorial; validar por consulta–página antes de abrir nuevos artículos. |
| P2 | Noticias | Encontrar actualidad verificable del folklore | **Propuesto:** `noticias del folklore argentino`, `[artista] noticias`, `[evento] noticias` | `/noticias-del-folklore-argentino`, `/categoria/{slug}`, ficha y `/{artista}/noticias` | Una noticia temporal puede competir con la ficha de artista o con un Evergreen si usa títulos genéricos. | Medir consultas de actualidad versus consultas estables; usar fecha y entidad en el snippet, enlazar a la ficha/evergreen apropiado y no crear archivo adicional sin demanda. |
| P2 | Eventos / cartelera | Encontrar un show próximo por artista, provincia o período | **Propuesto:** `[artista] show [ciudad]`, `eventos folklore [provincia]`, `festival [fecha]` | `/cartelera-de-eventos-folkloricos` y `/cartelera-de-eventos-folkloricos/{provinceOrSlug}/{period?}` | Landing nacional, variante territorial y ficha de evento pueden competir en búsquedas de fecha/lugar. | Validar en GSC qué combinación artista–ciudad–fecha alcanza páginas de evento. Mantener los filtros existentes y evitar landings nuevas antes de contar con eventos vigentes suficientes. |
| P3 | Recetas | Encontrar una receta tradicional y su preparación | **Propuesto:** `receta de [plato]`, `comida típica argentina [plato]` | `/recetas-de-comidas-tipicas-argentinas`, `/letra/{slug}` y ficha | Índice alfabético y ficha pueden competir por términos muy genéricos; contenido legacy insuficiente puede limitar utilidad. | Cruzar demanda con el auditor de calidad; priorizar fichas P1 con receta, introducción, fuente y SEO antes de crear nuevas categorías o URLs. |
| P3 | Mitos y leyendas | Comprender una tradición, variante o procedencia regional | **Propuesto:** `[mito] leyenda argentina`, `leyenda de [región]`, `mitos argentinos` | `/mitos-y-leyendas-argentinas`, `/letra/{slug}` y ficha | Landing, filtro alfabético y ficha pueden competir; variantes regionales no deben tratarse como la misma tradición sin evidencia. | Medir consultas por variante/región y combinarlo con el auditor cultural. Mejorar fichas existentes antes de abrir categorías indexables nuevas. |

## Reglas de decisión

1. Una consulta se asigna a una URL ya existente sólo después de revisar el par **consulta–página** en GSC. Si dos URLs reciben la misma consulta, se decide una intención principal antes de modificar title, H1, enlaces o canonical.
2. La prioridad P1 de Letras procede de impresiones, posición y CTR verificables; las demás prioridades son hipótesis operativas basadas en intención, rutas existentes, deuda editorial y la hoja de ruta, no una afirmación de volumen.
3. No crear una página territorial, temática o de filtro como respuesta automática a un clúster. Primero se exige demanda comprobable, corpus propio suficiente, ruta canónica y una diferencia semántica clara frente a la landing/ficha existente.
4. No usar este mapa para decisiones de `noindex`, consolidación, borrado o cambios de canonicals: esas decisiones siguen bloqueadas en `BL-0010J` hasta revisión humana y contraste de Search Console.

## Próxima medición reproducible

Para convertir las prioridades provisionales en priorización por datos, exportar para los últimos 90 días de Search Console los campos `consulta`, `página`, `clics`, `impresiones`, `CTR` y `posición`, y para GA4 las landing pages orgánicas con sesiones y engagement. La revisión debe:

1. agrupar por familia de URL de esta matriz;
2. señalar consultas con impresiones altas, posición 4–10 y CTR inferior al esperado frente a pares comparables;
3. detectar consultas repartidas entre más de una URL canónica candidata;
4. decidir sólo mejoras sobre la URL existente ganadora; y
5. registrar el antes/después en el futuro tablero `BL-0010V`.

## Revisión independiente

- Cobertura: las ocho familias exigidas por `BL-0010O` (artistas, biografías, letras, discos, noticias, eventos, festivales y Evergreen) tienen intención, destino y riesgo documentados. Recetas y mitos se incluyeron como superficies editoriales complementarias, sin ampliar el alcance técnico.
- Trazabilidad: rutas locales y cifras GSC disponibles están diferenciadas de los clústeres propuestos.
- Seguridad SEO: el mapa no añade URLs, no ejecuta cambios de indexación ni modifica contenido; conserva la regla de contenido suficiente y canonización previa.
- Limitación: falta un export vigente consulta–página de GSC/GA4 para confirmar volúmenes y canibalización real fuera de las cuatro consultas de Letras. Esto no invalida el mapa de intención, pero sí bloquea decisiones de indexación o consolidación.
