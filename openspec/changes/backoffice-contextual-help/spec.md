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

## Resultado
mfa_test: 669 passed, 2 skipped. Nuevo `tests/Feature/BackofficeHelpTest.php` (8 tests).
