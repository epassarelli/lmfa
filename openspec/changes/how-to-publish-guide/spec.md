# Spec: how-to-publish-guide

Estado: implementada en local. Aprobación: explícita del usuario ("hazlo").

## Contexto y problema
Al retirar el flujo "Contribuir" quedó sin explicación pública de cómo enviar información al portal. Hoy se publica con cuenta + fichas (Mis fichas) + ABM del backoffice, y quien llega no sabe por dónde empezar. No se encontró la página estática original (probablemente fuera del código); esta spec la reemplaza.

## Alcance
- Página pública indexable `/como-publicar` ("Cómo publicar en Mi Folklore Argentino"): pasos (cuenta → Mis fichas → aprobación → cargar → revisión), qué se puede cargar según la ficha, imágenes, derechos, verificadas, preguntas frecuentes. Marcado sin variaciones por usuario (cache-safe).
- Enlaces: botón "Publicar" del menú, pie de página, registro, Mi panel, Mis fichas y la ayuda contextual de cada formulario ("Guía completa").
- Sitemap estático.
- Sin migraciones.

## Fuera de alcance
Cambiar permisos o flujos; el texto de derechos es propuesta y requiere revisión editorial del usuario.

## Reglas de contenido (verificadas en código)
- Artista administrado → Discos, Canciones, Noticias y Eventos. Festival administrado → Eventos. Sin fichas → Mis fichas.
- Quien no puede publicar deja el contenido en borrador hasta que el equipo lo revisa; los verificados (permiso `publish contents`) publican directo. Los verificados los otorga el equipo.
- Se avisa por email cuando una solicitud de ficha se resuelve.
- No se publican letras completas sin autorización.

## Criterios de aceptación
- Dado un visitante, cuando abre `/como-publicar`, entonces ve los pasos, enlaces a registro/ingreso y a Mis fichas, sin noindex y con canonical.
- El menú "Publicar" y el pie llevan a la guía; el sitemap estático la incluye.
- Mi panel, Mis fichas y los formularios enlazan a la guía.

## Preguntas abiertas
Texto de derechos y criterio para ser verificado: redactados como propuesta ("lo decide el equipo").

## Resultado
Ver tests/Feature/PublishGuidePageTest.php.
