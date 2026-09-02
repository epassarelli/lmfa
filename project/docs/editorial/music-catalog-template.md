# Contrato editorial — Discografía, Canciones y Letras

## Objetivo

Separar claramente:
- la ficha de un álbum;
- la ficha de una canción/obra interpretada por un artista;
- la letra completa, que es contenido textual sujeto a derechos.

## Álbum

Campos recomendados:
- album
- album_type: studio | live | compilation | ep | single | other
- interprete_id
- anio
- release_date
- label
- excerpt
- seo_title
- meta_description
- image_alt
- spotify
- portada/media
- tracklist mediante relación albunes_canciones

No inventar sello, fecha exacta, tipo de edición ni tracklist.

## Canción / obra interpretada

La entidad Cancion representa la ficha de una canción asociada a un intérprete. Puede existir y ser útil aunque MFA no publique la letra.

Campos:
- cancion
- slug
- interprete_id
- excerpt
- composer
- lyricist
- is_instrumental
- youtube
- spotify
- seo_title
- meta_description
- album_ids

## Letras y derechos

La letra es opcional.

rights_status:
- unknown: hay letra histórica pero no se verificó el derecho/procedencia;
- authorized: autorización verificable;
- licensed: licencia verificable;
- public_domain: dominio público verificado;
- not_available: MFA deliberadamente no publica la letra.

lyrics_source_url debe contener una fuente o evidencia cuando corresponda.

Reglas:
- nunca generar una letra para rellenar un campo;
- nunca usar placeholders como "No disponible aun";
- una obra instrumental debe tener letra=null;
- una canción puede publicarse sin letra;
- la automatización de descubrimiento puede crear la ficha de obra, créditos, discos y enlaces, pero no incorporar letras completas sin un estado de derechos apto;
- letras legacy se preservan hasta auditoría; no se eliminan masivamente.

## SEO

Álbum:
- seo_title persistido con fallback album + año + artista;
- meta_description persistida con fallback descriptivo.

Canción:
- si hay letra disponible, el title puede usar "Letra de...";
- si no hay letra, no prometer "Letra" en title/description;
- priorizar ficha de obra, créditos, intérprete y grabaciones relacionadas.

## Content Refresh

Auditar con:

php artisan mfa:music:audit --active

Priorizar:
1. placeholders legacy;
2. letras existentes con rights_status desconocido;
3. canciones sin composer/album/excerpt;
4. álbumes sin tracklist/SEO/tipo;
5. resto por score.

El auditor es read-only.
