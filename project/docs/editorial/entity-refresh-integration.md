# Integración editorial — Artistas, Recetas y Mitos

## Objetivo

Extender la bandeja `Contenidos` y el cargador Apps Script para soportar el mismo patrón de:
- descubrimiento;
- curación;
- ACCION_API=CREAR/ACTUALIZAR;
- ENVIAR_API como gate;
- ID_WEB obligatorio para updates;
- score/faltantes.

## Tipos

- Artista
- Receta
- Mito

## Dependencias de backend

Este cambio NO debe desplegarse antes de que estén disponibles:
- modernización de Biografías/Artistas;
- modernización de Recetas;
- modernización de Mitos.

## Artista

Mapeo:
- ARTISTA o TITULO -> interprete
- ARTIST_TYPE
- SLUG
- CUERPO -> biografia
- BAJADA -> excerpt
- META_TITLE
- META_DESCRIPTION
- IMAGE_ALT
- FEATURED_IMAGE_PATH / FEATURED_IMAGE_URL
- WEB_URL / FACEBOOK_URL / INSTAGRAM_URL / YOUTUBE_URL

## Receta

Mapeo:
- TITULO
- SLUG
- CUERPO -> receta
- BAJADA -> excerpt
- RECIPE_INGREDIENTS_JSON
- RECIPE_INSTRUCTIONS_JSON
- PREP_TIME_MINUTES
- COOK_TIME_MINUTES
- SERVINGS
- REGION
- SEO / imagen

Las listas aceptan JSON array o líneas separadas.

## Mito

Mapeo:
- TITULO
- SLUG
- CUERPO -> mito
- BAJADA -> excerpt
- CONTENT_TYPE: myth | legend | urban_legend
- REGION
- SEO / imagen

## Seguridad

- CREAR no puede tener ID_WEB.
- ACTUALIZAR exige ID_WEB.
- Updates pueden ser parciales.
- CREAR exige cuerpo suficiente.
- ENVIAR_API=S es el único permiso de envío.
- Después de éxito: RESULTADO_API y ENVIAR_API=N.
- No se ejecutan migraciones ni deploys desde Apps Script.

## Gate

Antes de habilitar estos tipos en producción:
1. backend de las tres entidades desplegado;
2. CI Apps Script verde;
3. una fila controlada por entidad;
4. POST/PUT exitosos;
5. validación en backoffice;
6. auditor de entidad antes/después.
