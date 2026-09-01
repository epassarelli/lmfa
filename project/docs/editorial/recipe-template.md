# Plantilla editorial — Recetas de comidas típicas argentinas

## Objetivo
Crear o mejorar recetas útiles, verificables y legibles, preservando tradición regional sin inventar cantidades, tiempos ni variantes.

## Reglas
- El body nunca contiene <h1>.
- Mantener receta como cuerpo editorial completo.
- Usar HTML semántico con H2/H3.
- Ingredientes y preparación se guardan también de forma estructurada cuando la fuente permite identificarlos.
- No inferir cantidades, tiempos, porciones ni temperatura.
- Variantes regionales, notas y FAQs pueden vivir en el body.
- Recipe schema sólo si existen ingredientes y pasos estructurados reales.
- Priorizar fuentes gastronómicas oficiales, instituciones culturales, recetarios reconocidos o medios especializados.
- Conservar la procedencia regional cuando esté documentada.
- Objetivo habitual: 500–1.000 palabras cuando la receta lo justifica.
- Mínimo operativo para nuevas/reemplazos completos: 300 palabras visibles.

## Estructura recomendada
Párrafo inicial: qué plato es, región/tradición y contexto.

### H2 — Ingredientes
La lista visible debe coincidir con ingredients.

### H2 — Preparación
Los pasos visibles deben coincidir con instructions.

### H2 — Consejos y puntos clave
Sólo información verificable.

### H2 — Variantes regionales
No presentar una variante como versión universal.

### H2 — Historia y tradición
Cuando existan fuentes suficientes.

### H2 — Preguntas frecuentes
Sólo respuestas respaldadas por fuentes/contenido.

### H2 — Fuentes consultadas
Cuando corresponda.

## Campos estructurados
- TITULO
- SLUG
- BAJADA / excerpt
- CUERPO / receta
- ingredients[]
- instructions[]
- prep_time_minutes
- cook_time_minutes
- servings
- region
- META_TITLE
- META_DESCRIPTION
- IMAGE_ALT
- FEATURED_IMAGE_URL / FEATURED_IMAGE_PATH
- ACCION_API
- ID_WEB
- SCORE_CALIDAD
- FALTANTES

## Curación
- Preservar información válida existente.
- Si el body ya es bueno, completar estructura/SEO sin reescribirlo por completo.
- Si el body es pobre, investigar y reemplazarlo con una versión completa.
- ENVIAR_API=N durante investigación.