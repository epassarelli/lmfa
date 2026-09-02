# 11 - Auditoría pública de autoridad, backlinks y menciones

> Tarea: `BL-0010W`.
> Fecha: 2026-08-27.
> Alcance: relevamiento público y reproducible de una muestra de enlaces y menciones. No se hicieron contactos, compras de enlaces, desautorizaciones, despliegues ni escrituras en producción.
> Límite: sin acceso a Search Console ni a una herramienta de enlaces no es posible estimar dominios de referencia, enlaces perdidos, anchors completos, toxicidad ni brechas cuantitativas frente a competidores. Este documento no extrapola la muestra a un perfil total.

---

## Método

- Consultas públicas acotadas por dominio y marca, con exclusión del propio sitio.
- Verificación manual de una muestra en fuentes abiertas.
- Clasificación por calidad de fuente y potencial de recuperación; no por métricas propietarias no disponibles.

La hoja de ruta define enlaces y menciones como indicadores de autoridad, pero exige que la medición parta de una línea base confiable. Por ello, los hallazgos siguientes son oportunidades cualitativas, no una medición de autoridad.

## Evidencia confirmada

| Fuente | Tipo | Destino o mención observada | Calidad | Lectura |
|---|---|---|---|---|
| [Wikipedia: Laura Ros](https://es.wikipedia.org/wiki/Laura_Ros) | Enlace externo | Discografía de Laura Ros en `www.mifolkloreargentino.com.ar` | Alta | Enlace editorial visible en una página de artista; revisar que el destino histórico conserve redirección canónica. |
| [Wikipedia: Roberto Rimoldi Fraga](https://es.wikipedia.org/wiki/Roberto_Rimoldi_Fraga) | Referencia + enlace externo | Biografía y discografía en el dominio histórico `.com.ar` y enlace externo en `.com` | Alta | Señal de autoridad concentrada en una ficha artística; hay coexistencia de hosts históricos. |
| [Wikipedia: Chacho Peñaloza](https://es.wikipedia.org/wiki/Chacho_Pe%C3%B1aloza) | Dos enlaces externos | “La muerte del Chacho” y “Canción de cuna del Chacho” en `www.mifolkloreargentino.com.ar` | Alta | Dos destinos temáticos relevantes; prioridad alta para comprobar que no terminan en error ni cadena larga. |
| [Aportes del folklore argentino a la lengua](https://www.igualdadycalidadcba.gov.ar/SIPEC-CBA/8CongresoILE/docs/Aportes-del-Folklore-Argentino.pdf) | Recurso educativo | Enlace al inicio histórico `http://www.mifolkloreargentino.com.ar/` | Alta | Mención institucional/educativa útil; el protocolo y host son históricos y deben conservar una resolución canónica eficiente. |
| [Repositorio UFASTA](https://dspace.ufasta.edu.ar/bitstreams/c78b44c8-fb34-4334-a9f3-4dc73faa08e1/download) | Cita académica | Referencia a `mifolkloreargentino.com.ar` en un trabajo sobre Arasy | Media | Mención contextual sin URL profunda verificable en la muestra; no contactar ni inferir aval académico. |
| [La Folk Argentina: Elvira Ceballos](https://lafolkargentina.com.ar/nota/4787/tristeza-en-el-mundo-artistico-por-la-muerte-de-elvira-ceballos) | Mención editorial | Atribuye datos biográficos al sitio `mifolkloreargentino.com` | Media | Es una mención atribuida, no se confirmó enlace HTML desde la vista pública; sirve para rastrear y preservar la ficha fuente. |

Fuentes de baja señal como directorios automáticos de “sitios similares” se observaron durante la búsqueda, pero se excluyen de la lista priorizada: no aportan contexto editorial suficiente para justificar una acción.

## Brechas y oportunidades priorizadas

| Prioridad | Acción propuesta | Evidencia | Destino | Riesgo / gate |
|---|---|---|---|---|
| P1 | Verificar en modo lectura la cadena HTTP de cada URL histórica enlazada desde Wikipedia y el recurso educativo; registrar 200/301/404 y URL canónica final. | Cuatro fuentes de alta calidad enlazan o citan hosts históricos. | Fichas de Laura Ros, Roberto Rimoldi Fraga, Chacho Peñaloza y portada. | No cambiar redirects ni rutas sin una spec SEO y revisión humana. |
| P1 | Obtener desde Search Console un export de enlaces principales y páginas destino; complementar con una herramienta de enlaces si se autoriza. | La muestra pública no permite cuantificar perfil ni pérdida de enlaces. | Matriz de dominios, URL destino, anchor, fecha y estado. | Requiere acceso/datos externos; no inventar métricas. |
| P2 | Contrastar las fichas históricamente enlazadas contra sus equivalentes actuales y priorizar únicamente las que tengan fuente, autoría y navegación relacionada verificables. | Los enlaces de mayor señal apuntan a biografías, discografías y contenido histórico. | Plantillas de Artista/Disco/Contenido cultural. | No modificar contenido ni publicar sin el contrato editorial y derechos aplicables. |
| P2 | Preparar un inventario de recursos citables propios por tema (biografías con fuentes, discografías, festivales, ritmos y archivo cultural) antes de evaluar divulgación institucional. | La muestra muestra demanda de fichas culturales, no una estrategia de outreach validada. | Activos de autoridad cultural. | No contactar, comprar enlaces ni solicitar altas durante esta tarea. |

## Revisión independiente contra la definición de terminado

- Clasificación cualitativa: satisfecha para la muestra pública, con fuentes, destinos/menciones y riesgo documentados.
- Perfil de enlaces y brechas frente a competidores completo: no satisfecho. Faltan datos autenticados de Search Console y una fuente de enlaces comparable; una búsqueda pública no sustituye ese inventario.
- Acciones seguras: se priorizaron verificaciones read-only y preparación de activos; no se propusieron desautorizaciones especulativas ni contactos.

Resultado recomendado para Drive: **Parcial / En revisión**. Próximo paso: aportar el export de enlaces de Search Console y autorizar, si corresponde, una herramienta de análisis de enlaces para completar la matriz cuantitativa.
