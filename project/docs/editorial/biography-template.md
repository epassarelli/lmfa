# Plantilla editorial — Biografías de intérpretes

## Objetivo

Construir fichas biográficas útiles, verificables y evergreen para solistas y grupos del folklore argentino.

La página no debe ser una sucesión de datos sueltos ni una cronología inventada. Debe explicar quién es el artista, de dónde viene su propuesta, cómo se desarrolló su trayectoria y qué vínculos tiene con obras, discos, festivales y escenas musicales.

## Reglas transversales

- El body nunca contiene `<h1>`; Laravel reserva el único H1 para el título.
- Usar HTML semántico con párrafos, H2/H3, listas sólo cuando aporten claridad.
- No inventar fecha/lugar de nacimiento, integrantes, parentescos, premios, discografía ni influencias.
- Cuando una fuente contradice a otra, explicitar la discrepancia o dejar el dato fuera.
- Priorizar sitio oficial, organismo público, universidad, sello, entrevista directa o medio confiable.
- Evitar copiar biografías promocionales.
- Mantener separados hechos permanentes de noticias coyunturales.
- La ausencia de una sección no habilita a rellenarla con generalidades.
- Objetivo habitual: 600–1.200 palabras cuando existan fuentes suficientes.
- Mínimo operativo para una biografía nueva o reemplazo completo: 300 palabras visibles.
- Una actualización parcial puede modificar sólo campos estructurales/SEO sin reescribir un body ya válido.

## Estructura recomendada

Párrafo inicial:
- quién es el artista o grupo;
- región/escena con la que se lo identifica si está documentada;
- rasgo principal de su trayectoria.

### H2 — Origen y primeros años
Sólo cuando exista información verificable.
Para grupos: formación, ciudad/región, primeros integrantes si es relevante.
Para solistas: primeros vínculos con la música y contexto de formación.

### H2 — Trayectoria artística
Recorrido por etapas, no una lista indiscriminada de fechas.
Incluir hitos, cambios de formación, festivales importantes, giras o proyectos cuando estén documentados.

### H2 — Música, estilo y repertorio
Explicar géneros, instrumentos, cruces estéticos y repertorio sin etiquetar arbitrariamente.
Distinguir descripciones de fuentes de inferencias editoriales.

### H2 — Discos y obras destacadas
Relacionar con discografía existente en MFA cuando corresponda.
No inventar “discos más importantes”; explicar por qué una obra es relevante cuando una fuente lo sostiene.

### H2 — Festivales, reconocimientos y proyección
Sólo con evidencia.
Los premios deben incluir nombre y, cuando sea posible, año/fuente.

### H2 — Actualidad / vigencia
Breve y evergreen.
Evitar convertir la biografía en una noticia fechada.

### H2 — Preguntas frecuentes
Sólo preguntas cuya respuesta esté respaldada por la biografía/fuentes.

Formato:
```html
<h2>Preguntas frecuentes</h2>
<p><strong>¿Quién es ...?</strong></p>
<p>Respuesta verificable.</p>
```

No generar schema FAQ automáticamente salvo que exista una extracción estructurada y verificable de preguntas reales.

### H2 — Fuentes consultadas
Incluir fuentes principales mediante enlaces cuando sea editorialmente apropiado.

## Campos estructurados

- ARTIST_TYPE: soloist | group
- TITULO / ARTISTA
- SLUG
- BAJADA
- CUERPO
- META_TITLE
- META_DESCRIPTION
- IMAGE_ALT
- WEB_URL
- FACEBOOK_URL
- INSTAGRAM_URL
- YOUTUBE_URL
- FEATURED_IMAGE_URL / FEATURED_IMAGE_PATH
- ACCION_API: CREAR | ACTUALIZAR
- ID_WEB obligatorio para ACTUALIZAR
- SCORE_CALIDAD / FALTANTES para curación

## Descubrimiento

Antes de crear:
1. consultar /api/v1/artists por nombre/slug;
2. revisar la cola editorial;
3. resolver homónimos;
4. confirmar que se trata de un intérprete/grupo relevante para el alcance editorial;
5. recolectar fuentes suficientes.

Nuevo:
- TIPO=Artista
- ACCION_API=CREAR
- ID_WEB vacío
- estado API por defecto inactivo para revisión.

## Curación

Para artistas existentes:
- TIPO=Artista
- ACCION_API=ACTUALIZAR
- ID_WEB obligatorio
- preservar información válida;
- completar primero FALTANTES;
- no reemplazar un body bueno únicamente para subir score;
- ENVIAR_API=N durante investigación;
- habilitar envío sólo cuando los faltantes críticos estén resueltos.

Prioridad:
1. P1 por score ascendente;
2. P2;
3. P3;
4. dentro del mismo score, priorizar páginas con visitas/tráfico cuando esa señal esté disponible.
