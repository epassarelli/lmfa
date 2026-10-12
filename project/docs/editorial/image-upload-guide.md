# Guía de tamaños y ratios para subir imágenes por entidad

Basado en `config/image_profiles.php` (fuente de verdad del sistema). El
servicio que procesa las imágenes (`ImageUploadService`) **nunca agranda una
imagen que sea más chica que una variante** — si subís una imagen angosta,
esa variante grande directamente no se genera y el sitio termina sirviendo
una versión más chica de lo ideal en pantallas grandes. Por eso conviene
subir siempre igual o por encima del tamaño mínimo recomendado.

Todas las variantes se recortan al centro (`cover`) al ratio indicado, así
que no hace falta que la foto original tenga exactamente ese ratio — pero
cuanto más cerca esté, menos se pierde de los bordes al recortar.

| Entidad | Ratio a subir | Tamaño mínimo recomendado | Variantes que genera el sistema |
|---|---|---|---|
| **Noticias** (`news_full`) | 16:9 | **1600×900** | card (320-768px), detail (768-1600px), sidebar 1:1 (120-240px) |
| **Artistas** (`artist`) | Preferir vertical o cuadrada, no panorámica | **800×1024** (mínimo 800 de ancho **y** 1024 de alto) | card 1:1 (300-800px), main 3:4 (300-768px) |
| **Hero / banners** (`hero`) | 16:9 | **1920×1080** | main (768-1920px) |
| **Álbumes / discos** (`album`) | 1:1 (cuadrada) | **800×800** | card (200-600px), main (400-800px) |
| **Recetas** (`recipe`) | 4:3 | **800×600** | card (80-160px), main (400-800px) |
| **Festivales** (`festival`) | 16:9 | **1600×900** | card (320-768px), main (768-1200px), hero (768-1600px) |
| **Mitos** (`mito`) | 4:3 | **900×675** | card (160-320px), main (600-900px) |
| **Eventos / shows** (`event`) | 16:9 | **1600×900** | card (480-1024px), main (768-1280px), hero (768-1600px), sidebar 1:1 (120-240px) |

## Por qué el caso de Artistas es distinto

Es la única entidad con una variante **vertical** (`main`, ratio 3:4, hasta
768×1024). Si subís una foto panorámica (por ejemplo 1200×800), el sistema
va a intentar recortar un 768×1024 desde ahí y **puede terminar agrandando
el alto** porque la foto no tiene suficiente altura — eso sí pierde calidad.
Para artistas, subí fotos de perfil/retrato (verticales o cuadradas), no
paisajes anchos.

## Formato de origen

No hace falta subir en WebP — el sistema convierte automáticamente
cualquier imagen a WebP calidad 85 al generar las variantes. Subí en el
formato que tengas (JPG, PNG) siempre que respete el tamaño mínimo de la
tabla.

## Fallbacks genéricos (cuando no hay foto)

Los fallbacks editoriales (`public/img/fallbacks/*-v2.webp`, usados cuando
una noticia/entidad no tiene imagen propia ni relacionada) están fijados en
**1024×576 a calidad 78** (~55 KiB cada uno). Ese tamaño ya cubre el uso más
grande que reciben (variante `detail`, hasta 1024px) sin perder nitidez, y
achicarlos más no da ahorro relevante sin arriesgar artefactos de
compresión visibles en las ilustraciones. Si en el futuro se agrega un
fallback nuevo, seguir el mismo criterio: 1024px de ancho, calidad 75-80,
formato WebP.

Si alguna vez se reemplaza el contenido de un fallback existente, **hay que
volver a sufijarlo** (ej. `-v3`) en vez de sobreescribir el archivo con el
mismo nombre — las imágenes se sirven con cache de un año (`immutable`) en
`public/.htaccess`, así que un archivo reemplazado con el mismo nombre no
se actualiza para los visitantes ni para el CDN hasta que expire ese cache.

## Ajuste automático en el formulario (obligatorio)

Los formularios del backoffice ajustan la imagen **en el navegador, antes de subirla**, con las medidas de esta tabla:

- **Ya cumple** (proporción dentro de ±3 % y no supera el ancho máximo): se sube tal cual.
- **Supera el ancho máximo** (el de la variante más grande del perfil: 1600 px en noticias, festivales, eventos y clasificados; 800 px en artistas y discos; 900 px en mitos): se **reduce sola** manteniendo la proporción. Nunca se agranda.
- **Otra proporción**: se abre un editor de recorte con la proporción fija. Hay que confirmar el recorte o elegir otra imagen; si se cancela, no se sube nada.
- **Muy pesada**: se recomprime hasta entrar en el peso máximo. El navegador exporta con la menor pérdida posible (PNG se mantiene PNG, el resto JPEG de calidad alta) porque después el servidor genera igual las variantes WebP (calidad 85).
- **Más chica que el mínimo sugerido**: se sube igual (no se agranda) con un aviso de que puede verse borrosa.

**Red de seguridad en el servidor:** `ImageUploadService` guarda el "original" acotado al mismo ancho máximo (no se conserva el archivo sin procesar) y valida tipo (JPG, PNG, WebP) y peso (5 MB; 2 MB en clasificados).

Los textos y medidas del formulario están en `config/backoffice_help.php` (el ancho máximo se deriva de `config/image_profiles.php`); un test (`BackofficeHelpTest`) verifica que los mínimos coincidan con esta guía. El editor usa Cropper.js 1.6.2 desde jsDelivr, igual que Select2.
