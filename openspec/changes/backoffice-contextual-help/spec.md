# Spec: backoffice-contextual-help

Estado: implementada en local. Aprobación: explícita del usuario ("lo que quiero que hagas ya mismo es poner ayuda en cada ABM...").

## Problema
Quienes cargan contenido no entienden por qué ven unos registros y otros no, qué campos son obligatorios, qué medidas de imagen usar ni qué es el bloque SEO.

## Alcance
- Componentes Blade reutilizables (`resources/views/components/backend/`): `form-help` (qué es, qué ves, cómo se publica, obligatorios), `list-help` (por qué veo estos registros), `image-hint` (forma, medida ideal y mínima, formato, peso máximo y consejo, tomados de `config/image_profiles.php` y de los `FormRequest`), `seo-box` (bloque plegado «Cómo aparece en Google (opcional)»).
- Textos centralizados en `config/backoffice_help.php`; el alcance del listado de artistas se adapta al estado real del flag.
- Aplicado a: Artistas, Discos, Canciones, Festivales, Eventos (y evento con jornadas), Noticias, Mitos, Comidas y Clasificados; Mis fichas explica qué es una ficha, los roles Responsable/Editor/Miembro y por qué se ven unas secciones y otras no.
- Obligatorios: asterisco automático para todo campo `required`; se corrigió la biografía del artista y la imagen de noticia al crear (obligatorias en el servidor pero sin marca en el formulario).
- Lenguaje: «Slug» → «Dirección web»; «SEO», «Meta description», «Texto alternativo» → «Título/Resumen en Google» y «Descripción de la imagen». Clasificados traducido (estaba en inglés).
- Festivales: quien no puede publicar ya no ve un selector de estado que el servidor ignoraba; ve «Borrador (lo publica el equipo)».

## Fuera de alcance
Cambiar reglas de validación o de permisos; Peñas, Radios, Enciclopedia, Copa y Organizaciones (solo administrador); validación de dimensiones mínimas en el servidor (las medidas son recomendación).

## Observaciones
- Las medidas son recomendaciones: el servidor solo valida tipo y peso (5 MB; 2 MB en clasificados).
- `ShowRequest` no restringe mimes de la imagen de eventos (`image`) y `NoticiaRequest` no limita el peso: quedan como están.

## Ajuste: medidas de imagen desde la guía documentada
Las medidas salen de `project/docs/editorial/image-upload-guide.md` (fuente documentada previa; la primera versión de esta spec había usado valores propios, corregido). Sugeridas, no obligatorias: mínimo por tipo según la guía, máximo útil de 4000 px de lado (propuesta, no validada por el servidor) y peso máximo del servidor (5 MB; 2 MB clasificados). Al elegir un archivo, el formulario avisa sin bloquear si es más chico, más grande o de otra proporción (los artistas piden vertical o cuadrada), y si supera el peso máximo. No hay validación de dimensiones en el servidor. Un test compara los mínimos de `config/backoffice_help.php` con la guía.

## Ajuste 2: ajuste obligatorio de imágenes (recorte / reducción)
Aprobación explícita del usuario: ajuste obligatorio; lo que supera el ancho máximo se reduce; no se guarda el original; se agrega la librería si hace falta.
- Navegador (componente `image-hint`, un solo script para todos los formularios, parametrizado por `config/backoffice_help.php` + `config/image_profiles.php`): cumple → se sube igual; supera el ancho de la variante más grande → se reduce solo; otra proporción → editor Cropper.js con proporción fija, obligatorio (cancelar deja el campo vacío); muy pesada → recompresión. Salida con la menor pérdida posible (PNG se mantiene PNG si entra en el peso; el resto JPEG calidad 0,95 descendiendo solo si hace falta), nunca se agranda; el servidor sigue generando las variantes WebP desde ese archivo. Clasificados procesa varias imágenes en secuencia.
- Servidor: `ImageUploadService` guarda el original acotado al ancho de la variante más grande (red de seguridad si el navegador no ajustó) y no conserva el archivo sin procesar; sigue validando tipo y peso.
- Dependencia: Cropper.js 1.6.2 por CDN jsDelivr (mismo criterio que Select2): sin npm, sin build, sin Node en producción. Si no carga, el formulario sigue funcionando sin recorte (el sitio recorta al centro).
- Límite conocido: el comportamiento del navegador (JS) no tiene test automático; se verificó sintaxis y el HTML/atributos, falta la prueba manual.

## Resultado
mfa_test: 674 passed, 2 skipped. `BackofficeHelpTest` (9 tests) e `ImageUploadCapTest` (4 tests).
