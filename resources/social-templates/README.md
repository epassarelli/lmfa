# Catálogo de identidad social MFA

Estado vigente: doce diseños v4 aprobados visualmente por el usuario. Renderer GD de Noticias/Agenda implementado y probado localmente; Noticias tiene borrador diario inactivo, Eventos queda manual. La aprobación visual no autoriza publicación de contenidos. Configuración y activación: `../integrations/n8n/ACTIVATION.md`. Los catálogos de muestras conservan sus metadatos históricos de revisión; el registro operativo permitido está en `config/social_publication.php`.

## Diseño aprobado v4: solapa y pruebas con API

`catalog.v4.json` y `render-catalog-v4.ps1` generan `project/artifacts/social-catalog-v4/`. La sección pasa a una solapa con fondo del color correspondiente y letra de 36 px, frente al logo de 46 px. La tinta se elige entre blanco y el casi negro común según el mayor contraste relativo; se exige 4.5:1. Con los 12 colores actuales gana la tinta oscura; también se valida la rama blanca contra una solapa oscura. Se conservan fondo casi negro, blanco, un acento y la marca multicolor aprobada.

Las 12 rutas se consultaron con GET en n8n. Nueve variantes usan datos reales documentados en `api-preview-fixtures.v4.json`, que solo contiene campos editoriales y metadatos, sin tokens ni datos privados de usuarios. Peñas, Radios y Programas respondieron con `data=[]`, `total=0` al pedir publicados; sus muestras siguen siendo simulaciones. No se declara que esos módulos no tengan borradores u otros registros.

Agenda: la primera página contiene eventos históricos; se consultó la última página para elegir la Semana de la Tradición en Salta del 10 al 14 de noviembre de 2026. No se convirtió un evento vencido en próximo ni se inventó una hora. Las notas y eventos seleccionados indican `publication_mode=portal_only`, por lo que estas pruebas locales NO autorizan difusión social. Para publicar hace falta resolver explícitamente el criterio de habilitación; no se cambió ese campo.

Pruebas: títulos largos y cortos, fichas sin bajada (se omite sin inventarla), 12 IDs y colores distintos, paleta, solapas, contraste, dimensiones 1080x1350 y revisión visual de ambas hojas. Canciones usa solo nombre e intérprete; derechos pendientes (`rights_status=null`), sin letras ni audio. Se corrigió un caso de render de bajada de una sola línea y se verifica su conservación íntegra. Datos de archivo no equivalen a curación ni verificación externa de los hechos.

```powershell
./resources/social-templates/render-catalog-v4.ps1 -ValidateOnly
./resources/social-templates/render-catalog-v4.ps1
```

V1/v2/v3 se mantienen como antecedentes. V4 está aprobado visualmente. El renderer GD usa Noticias y Agenda, fuente aprobada configurada por entorno y assets privados SHA256. Se probó n8n contra simulador, sin envío a Facebook ni activación productiva.

## Propuesta vigente para revisión: catálogo v3

El usuario aclaró que quiere fondo casi negro común, blanco y UN color protagonista distinto por sección. La única excepción multicolor es el logo aprobado, reproducido en una línea desde el navbar. Se generaron las 12 propuestas mediante `render-catalog-v3.ps1`, con el registro `catalog.v3.json` y salidas `project/artifacts/social-catalog-v3/`. La sección tiene mayor jerarquía y ajusta su tamaño únicamente si su nombre es largo.

Colores: Noticias naranja; Agenda amarillo; Artistas celeste; Festivales coral; Enciclopedia violeta; Recetas marrón claro; Mitos verde azulado; Discos verde lima; Canciones rosa; Peñas verde; Radios cian; Programas azul lavanda. Son una propuesta para revisar; ninguna plantilla está activa. V1 y Noticias v2 quedan como antecedentes.

```powershell
./resources/social-templates/render-catalog-v3.ps1 -ValidateOnly
./resources/social-templates/render-catalog-v3.ps1
```

El motor valida 12 colores únicos y, en cada SVG, exactamente las tres tintas autorizadas fuera del grupo del logo. Se mantienen dimensiones 1080x1350, rechazo de desbordes, ID explícito, marcas de simulación y bloqueo de publicación. Estos SVG contienen tipografía como texto: su representación idéntica requiere la misma fuente; el PNG es la referencia de revisión.

## Revisión Noticias v2

La revisión del usuario pide mayor diferenciación entre secciones, marrón/naranja, marca idéntica a la tipografía multicolor del sitio y sección más grande. Se preparó únicamente `render-news-v2.ps1`, que genera `project/artifacts/social-preview/noticias-v2.png` y `.svg` usando la noticia de la primera muestra. V1 y las otras secciones se conservan como antecedentes, no como diseño aprobado. La marca replica los cinco segmentos de `navbar.blade.php`: Mi Folk celeste, lor blanco, e amarillo, Arg blanco y entino celeste, en una línea continua. Esta propuesta todavía no integra el catálogo activo ni n8n.

## Qué es una plantilla

El PNG es el resultado final. La plantilla es un diseño parametrizado con ID estable, reglas y versión. `catalog.v1.json` conserva la identidad compartida y el registro de 12 secciones. No se identifica una plantilla por inspección de una imagen. El proceso selecciona su ID antes de renderizar.

Las fuentes se guardan en `resources/social-templates/`, dentro del repositorio y fuera de la web pública. Los resultados y simulaciones se guardan en `project/artifacts/social-catalog/` (carpeta de artefactos ignorada por Git). No guardar tokens aquí. El catálogo es local y no fue desplegado.

## Reproducción local

Desde la raíz del repositorio, en PowerShell Windows:

```powershell
./resources/social-templates/render-catalog.ps1 -ValidateOnly
./resources/social-templates/render-catalog.ps1
```

Usa System.Drawing y Segoe UI ya presentes en Windows. No requiere Canva, IA ni descargas. Este generador prepara muestras de catálogo; no constituye todavía un servicio renderizador para el Docker Linux de n8n. El motor definitivo y una tipografía de licencia apta y disponibilidad reproducible en ese entorno deben validarse antes de automatizar.

## Selección propuesta para n8n

1. Recibir contenido publicado y elegible de un recurso conocido de la API.
2. Normalizar sus datos a `headline`, `details` y `note`. Estos son campos internos de diseño, no columnas afirmadas de la API; los adaptadores por recurso están pendientes.
3. Resolver `api_resource` a un ID del catálogo: por ejemplo `news` → `mfa-noticias-v1`, `events` → `mfa-agenda-v1`.
4. Verificar que el ID esté en una lista explícita de versiones aprobadas. Hoy `active_templates` está vacío y la publicación está deshabilitada.
5. Renderizar con el diseño fijo. Bloquear tipos desconocidos, datos obligatorios incompletos o títulos que no entren. No inventar resúmenes ni cortar palabras.
6. Registrar tipo, ID del contenido, `template_id`, versión de catálogo, hash de contenido, archivo generado y resultado por canal. El registro no está implementado todavía.

Ningún agente elige colores, fuente o composición en cada ejecución. Los mismos datos y la misma versión del renderizador, fuentes y catálogo deben producir el mismo resultado. Cambios de identidad requieren revisar las muestras y emitir una versión nueva; los envíos anteriores conservan su versión.

## Reglas visuales

- 1080 × 1350, fondo azul oscuro, marca tipográfica de dos líneas y dominio fijo.
- Celeste y blanco como base, amarillo como acento de algunas secciones.
- Sección y categoría visibles; título en bloque con ajuste entre 76 y 52 px. Si no entra, queda pendiente; no se trunca.
- Dos líneas de detalles, hasta tres de bajada y llamada a la acción propia de la sección.
- Muestras marcadas `SIMULACIÓN · DATOS DE EJEMPLO · NO PUBLICAR`. El material de demostración nunca entra a la cola real. La implementación futura requerirá payloads reales y un modo de salida distinto; este generador siempre conserva la marca de simulación.

## Reglas editoriales

- Solo hechos presentes en la fuente; no inventar fechas, ciudades, créditos, reseñas, tiempos de preparación o supuestas efemérides.
- Agenda: además de los campos de diseño, validar fecha futura, zona horaria, lugar, cancelaciones y URL pública.
- Festivales: distinguir ficha permanente de una edición fechada; no anunciar una fecha por inferencia.
- Canciones: metadatos y créditos; no letras ni audio sin decisión humana específica sobre derechos.
- Peñas y Radios/Programas: las muestras no acreditan validación operativa ni habilitan difusión de sus registros.
- Una imagen editorial mantiene la marca. La selección de contenidos, tono del copy y periodicidad necesitan sus propias reglas; la uniformidad visual no garantiza calidad editorial.

## Validación

El comando valida 12 IDs únicos, modo borrador, resolución de ID, títulos breves/largos, rechazo de títulos vacíos/excesivos y palabras indivisibles, y dimensiones de 12 PNG. SVG y HTML escapan textos. Se revisan visualmente las hojas de contacto para legibilidad y desbordes. No se modifica Laravel ni BD: no corresponde un test Feature; estos chequeos verifican el renderizador standalone. La revisión de cada futura pieza real y las pruebas del motor Docker quedan pendientes.
