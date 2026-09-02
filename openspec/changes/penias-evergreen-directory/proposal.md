## Why

Las Peñas son una necesidad de consulta recurrente y territorial que el portal puede resolver con alto valor cultural, SEO local y una futura oferta B2B. La superficie legacy actual no es apta para ello: mezcla datos editoriales antiguos, no ofrece una ficha pública confiable y no distingue un espacio permanente de su programación temporal.

El módulo debe reconstruirse ahora como un directorio evergreen verificable, aprovechando los patrones consolidados de Festivales sin confundir una Peña con un Evento o una Noticia.

## What Changes

- Crear un directorio público canónico de Peñas con landing nacional, filtros por provincia, localidad y tipo de espacio, y fichas individuales indexables.
- Definir una entidad evergreen de Peña con identidad, ubicación, contacto, reservas, horarios, accesibilidad, propuesta cultural, fuentes y fecha de última verificación.
- Relacionar las Peñas con Eventos futuros publicados, Artistas, Festivales y contenido evergreen solo cuando exista una relación editorial persistida y verificable.
- Separar el ciclo de vida de la Peña permanente de las fechas temporales: la ficha no publicará agenda ni horarios no verificados; los eventos conservarán su propio estado y expiración.
- Incorporar backoffice, API autenticada, auditor de calidad y flujo editorial compatible con Content Refresh, inicialmente bajo un release gate y lote piloto controlado.
- Integrar SEO técnico, canonical, breadcrumbs, schema.org, sitemap y redirecciones desde la URL legacy únicamente cuando el relevamiento confirme una equivalencia segura.
- Auditar y preservar los registros legacy antes de cualquier migración o publicación masiva.

## Capabilities

### New Capabilities

- `penias-evergreen-directory`: Directorio canónico y verificable de Peñas, con administración, API, visibilidad pública, relaciones editoriales, auditoría y garantías SEO.

### Modified Capabilities

- Ninguna.

## Impact

- Afecta las superficies públicas, administrativas y API del dominio Peñas, además de sitemap, navegación relacionada y documentación operativa.
- Requiere una nueva persistencia canónica o una migración compatible desde `penias` legacy; no se modificarán migraciones históricas ni se ejecutarán cambios de base de datos sin autorización expresa.
- Reutiliza las entidades existentes de Provincia, Localidad, Evento, Artista/Intérprete, Festival y contenido evergreen cuando el relevamiento confirme sus contratos.
- La ruta legacy `/penias-folkloricas-de-argentina` y sus datos se mantendrán bajo auditoría hasta definir una estrategia de transición y redirección segura.
