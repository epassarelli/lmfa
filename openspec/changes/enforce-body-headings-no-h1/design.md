# Diseño técnico: enforce-body-headings-no-h1

## Resumen

El cambio extiende el patrón ya validado en Enciclopedia/Evergreen a todas las entidades editoriales con cuerpo enriquecido. La solución se apoya en dos capas:

1. restricción de UI en los editores para no ofrecer `h1`
2. normalización backend/API para convertir cualquier `h1` residual en `h2`

Esto preserva consistencia semántica y SEO sin depender únicamente del editor.

## Decisiones clave

### 1. Política transversal: `h1` nunca se persiste en el body

Toda entidad con campo body-equivalente debe asumir:

- la página pública ya tiene un `h1` principal fuera del cuerpo
- el cuerpo editorial sólo puede usar párrafo, `h2` y `h3` como headings semánticos
- si entra `h1`, se normaliza a `h2`

Se elige normalizar en lugar de rechazar porque:

- reduce fricción editorial
- mantiene el contenido utilizable
- replica el criterio ya aceptado en Evergreen

### 2. Utilidad compartida de saneo HTML

Se debe usar una utilidad compartida, idealmente el sanitizador ya incorporado para Evergreen o una versión transversal derivada de ese patrón.

Responsabilidad:

- recibir HTML crudo
- reemplazar etiquetas de apertura/cierre `h1` por `h2`
- dejar intactos `h2`, `h3` y el resto del HTML

Esto evita reimplementar regex o DOM logic en múltiples requests/controladores.

### 3. Dos familias de superficie editorial

#### CKEditor

Entidades con partial global de CKEditor:

- Noticias
- Festivales
- Enciclopedia
- Intérpretes
- Mitos
- Comidas

Estrategia:

- extender el partial global para aceptar perfiles por `data-*`
- asignar a cada textarea editorial un perfil compartido de “body sin h1”
- definir `heading.options` con:
  - `paragraph`
  - `heading2` => `h2`
  - `heading3` => `h3`

#### Summernote

Entidad detectada:

- Eventos

Estrategia:

- revisar la inicialización actual de Summernote
- si existe toolbar configurable, remover `h1` del dropdown de estilos/headings
- aun si se oculta en UI, reforzar con normalización backend porque el payload puede llegar por otras vías

### 4. Requests como primer punto de normalización

Para backend web y API, la normalización debe ocurrir lo más cerca posible de la entrada:

- `FormRequest` backend
- `FormRequest` API

Campos a normalizar:

- `body`
- `noticia`
- `biografia`
- `mito`
- `receta`

Se recomienda resolverlo con uno o más traits reutilizables por tipo de campo para no contaminar cada request con lógica inline repetida.

### 5. Servicios/controladores como segunda red de seguridad

Cuando una entidad ya tenga un servicio de dominio que persiste datos editoriales, conviene repetir la defensa allí como “belt and suspenders”.

Objetivo:

- evitar que futuros flujos que omitan el request dejen pasar `h1`
- mantener el patrón ya usado en `KnowledgeArticleService`

No hace falta duplicar esta capa en todas las entidades si la persistencia es trivial y queda completamente cubierta por requests, pero sí en los módulos donde el flujo actual ya centraliza normalización o mapeos de body.

## Entidades alcanzadas

### Noticias

- Campo editorial: `noticia` en backend, `body`/`noticia` en API
- Editor: CKEditor
- Necesidades:
  - perfil sin `h1`
  - normalización de `noticia`
  - normalización de `body` y `noticia` en API

### Eventos

- Campo editorial: `body`
- Editor: Summernote
- Necesidades:
  - ocultar o impedir `h1` en toolbar
  - normalización backend/API de `body`

### Festivales

- Campo editorial: `body`
- Editor: CKEditor
- Necesidades:
  - perfil sin `h1`
  - normalización backend/API de `body`

### Intérpretes

- Campo editorial: `biografia`
- Editor: CKEditor vía componente `x-textarea`
- Necesidades:
  - confirmar cómo el componente activa CKEditor
  - aplicar perfil sin `h1`
  - normalización backend/API de `biografia`

### Mitos

- Campo editorial: `mito`
- Editor: CKEditor
- Necesidades:
  - perfil sin `h1`
  - normalización backend/API de `mito`

### Comidas

- Campo editorial: `receta`
- Editor: CKEditor
- Necesidades:
  - perfil sin `h1`
  - normalización backend/API de `receta`

### Enciclopedia

- Campo editorial: `body`
- Estado actual:
  - ya protegido
- Necesidades:
  - preservar compatibilidad
  - refactorizar a una solución compartida si eso no rompe el comportamiento validado

## Testing

## Backend/UI

- tests que inspeccionen el HTML del formulario o script generado para confirmar que no aparece `view: 'h1'`
- tests por entidad clave para guardar contenido con `<h1>` y verificar persistencia como `<h2>`
- tests para preservar `<h2>` y `<h3>`

## API

- Noticias
- Eventos
- Festivales
- Intérpretes si existe endpoint de escritura
- Mitos si existe endpoint de escritura
- Comidas si existe endpoint de escritura
- Enciclopedia para asegurar no regresión

## Riesgos abiertos

- confirmar si todos los módulos listados tienen endpoints API de escritura realmente activos hoy
- confirmar la forma exacta de inicialización de Summernote para eventos
- confirmar cómo `x-textarea` vincula CKEditor en Intérpretes, porque puede requerir un hook distinto al `#editor` clásico

## Orden de implementación sugerido

1. Relevar y unificar el sanitizador compartido
2. Aplicar normalización a requests backend/API
3. Ajustar perfiles CKEditor
4. Ajustar configuración Summernote para Eventos
5. Agregar tests por entidad y de no regresión para Evergreen
