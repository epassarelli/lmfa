## Why

El módulo actual de Radios es un listado legacy mínimo: no distingue una emisora de una señal digital, no puede describir cómo escucharla ni publicar una programación útil. El portal necesita un directorio evergreen que ayude a descubrir radios y programas folklóricos por territorio, formato y plataforma, sin excluir producciones independientes que sólo emiten en streaming.

## What Changes

- Crear un directorio canónico de radios y señales folklóricas, separado de la tabla y rutas legacy.
- Permitir registrar radios por aire, web, streaming o híbridas, con datos de cobertura, frecuencia, ubicación, canales de escucha, plataformas y verificación editorial.
- Incorporar programas con horario, días, conductores, descripción y enlaces de escucha; un programa puede pertenecer a una radio o ser independiente en YouTube, Facebook u otra plataforma permitida.
- Mostrar fichas públicas con opciones de escucha, datos territoriales, programación vigente y relaciones editoriales explícitas.
- Proveer backoffice, API autenticada, auditor de calidad y datos demo para DEV antes de cualquier piloto editorial real.
- **BREAKING**: retirar el legado de Radios sólo después de confirmar que su tabla está vacía o de aprobar un plan de migración y redirecciones; no se asumirá equivalencia automática.

## Capabilities

### New Capabilities
- `radio-directory`: Directorio verificable de radios, señales y canales de escucha por aire y digitales.
- `radio-programming`: Gestión y descubrimiento de programación de radios y programas independientes de streaming.

### Modified Capabilities
- Ninguna.

## Impact

- Afecta el futuro modelo canónico de radios, programación, frontend, backoffice, API, sitemap, navegación y auditoría editorial.
- Reutiliza Provincia, Locality, media, estados editoriales, permisos, eventos y patrones de Peñas/Festivales cuando correspondan.
- No introduce reproducción, scraping ni integración automática con YouTube, Facebook, proveedores de streaming o servicios de mapas en el MVP.
