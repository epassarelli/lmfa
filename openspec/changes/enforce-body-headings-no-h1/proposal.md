# Cambio: enforce-body-headings-no-h1

## Objetivo

Unificar en todas las entidades editoriales del backend la misma política ya aplicada en Evergreen: el contenido largo del cuerpo no debe ofrecer ni persistir encabezados `<h1>`, y cualquier intento de guardarlos debe normalizarse de forma segura.

## Problema actual

El módulo Evergreen ya quedó protegido para que CKEditor no ofrezca `h1` y el backend normalice cualquier `h1` residual a `h2`. Sin embargo, el resto de las entidades editoriales todavía tienen comportamientos dispares:

- algunas usan el partial global de CKEditor sin una configuración específica de headings
- Eventos usa Summernote y hoy no restringe el uso de `h1`
- el backend acepta HTML de cuerpo sin reforzar una política transversal
- el resultado es inconsistente para SEO y semántica editorial, porque un `h1` puede quedar dentro del body mientras la página ya tiene su `h1` principal

## Alcance incluido

- Aplicar a las entidades editoriales activas con campos body-equivalentes en backend:
  - Noticias (`noticia` / `body`)
  - Eventos (`body`)
  - Festivales (`body`)
  - Intérpretes (`biografia`)
  - Mitos (`mito`)
  - Comidas (`receta`)
  - Enciclopedia / Evergreen (`body`) como patrón de referencia ya validado
- Ajustar los editores del panel para que ofrezcan sólo párrafo, `h2` y `h3`, nunca `h1`
- Reforzar backend y API para normalizar cualquier `h1` residual a `h2` antes de persistir
- Agregar pruebas focalizadas por backend/API para validar la política transversal

## Fuera de alcance

- Modificar contenido ya publicado o ejecutar scripts de migración masiva sobre registros existentes
- Cambiar la estructura visual pública de las plantillas frontend
- Introducir un nuevo editor WYSIWYG o reemplazar Summernote por CKEditor en esta tarea
- Reescribir HTML histórico distinto de `h1` dentro de otros campos no editoriales

## Archivos afectados

- `resources/views/backend/partials/scripts/_ckeditor.blade.php`
- `resources/views/backend/news/form.blade.php`
- `resources/views/backend/festivales/form.blade.php`
- `resources/views/backend/knowledge_articles/form.blade.php`
- `resources/views/backend/interpretes/form.blade.php`
- `resources/views/backend/mitos/form.blade.php`
- `resources/views/backend/comidas/form.blade.php`
- `resources/views/backend/events/form.blade.php`
- `app/Support/*Sanitizer*.php` o helper transversal equivalente
- `app/Http/Requests/*` de backend afectados
- `app/Http/Requests/Api/*` de API afectados
- `app/Http/Controllers/Backend/EventController.php` o helper equivalente si Summernote requiere saneo adicional en el flujo actual
- `app/Services/*` donde ya exista normalización compartida por entidad
- `tests/Feature/*` de los módulos afectados
- `project/docs/00_estado_actual.md` si el cambio queda implementado

## Reglas funcionales

### Caso 1
Dado un editor del backend en una entidad con cuerpo enriquecido
Cuando abre el selector de headings del editor
Entonces no debe existir una opción para insertar `h1`

### Caso 2
Dado un contenido nuevo o editado que llega con `<h2>` o `<h3>`
Cuando se guarda la entidad
Entonces el HTML debe persistirse sin degradar esos headings ni convertirlos a `h1`

### Caso 3
Dado un contenido nuevo o editado que llega con `<h1>`
Cuando el backend o la API lo procesa
Entonces debe normalizarlo a `<h2>` antes de persistirlo

### Caso 4
Dada una entidad ya existente
Cuando se edita y se vuelve a guardar
Entonces el contenido del body-equivalente no debe terminar con `<h1>` aunque el payload lo intente enviar

## Reglas técnicas

- No modificar contenido existente directamente en base de datos
- Reutilizar el patrón Evergreen ya implementado siempre que sea viable
- Centralizar la normalización HTML para evitar lógica duplicada entre entidades
- Mantener compatibilidad con requests backend y API actuales
- Si Summernote no permite restringir `h1` de forma confiable sólo desde UI, reforzar siempre la normalización backend
- No cambiar arquitectura general ni introducir librerías nuevas sin justificación
- Cubrir el comportamiento con tests `Feature` usando `DatabaseTransactions`

## Validación

- Verificar en cada formulario afectado que el editor no ofrece `h1`
- Verificar persistencia de `<h2>` y `<h3>` sin conversión indebida
- Verificar normalización de `<h1>` a `<h2>` en backend
- Verificar normalización de `<h1>` a `<h2>` en API donde aplique
- Confirmar que no se modifica contenido existente fuera de los flujos de guardado

## Riesgos

- Summernote puede requerir una estrategia distinta a CKEditor para ocultar o limitar headings desde UI
- Algunas entidades usan nombres de campo distintos a `body` (`noticia`, `biografia`, `mito`, `receta`), por lo que conviene explicitar el alcance para no dejar huecos
- Existe riesgo de duplicar saneo entre requests y servicios si no se define una utilidad compartida

## Criterio de aceptación

El cambio se considera terminado cuando todas las entidades editoriales alcanzadas dejan de ofrecer `h1` en sus editores, ningún flujo backend/API persiste `h1` dentro del cuerpo o campo body-equivalente, el patrón queda alineado con Evergreen y existen pruebas automáticas que lo respalden.
