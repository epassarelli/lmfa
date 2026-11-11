# Spec: Home con más noticias, sin contador y tarjetas sin imagen de relleno

Estado: aprobada por el usuario en el chat (2026-11-11). Cambio reversible, de bajo riesgo; sin migraciones ni cambios de rutas/URLs.

## Contexto
- La Home muestra un contador de contenidos que ya no aporta y solo 6 noticias (límite histórico por tiempos de carga).
- La mayoría de las noticias no tiene imagen propia; la tarjeta muestra una imagen genérica de relleno (fallback) o la foto del intérprete relacionado.

## Alcance
1. Home: reemplazar el contador por una bajada fija sin números; eliminar la consulta `home:totales`.
2. Home: mostrar 24 noticias (constante `HomeController::NEWS_LIMIT`).
3. `x-noticia-card` (todos los listados: Home, /noticias, categorías, artista, relacionadas, búsqueda): renderizar imagen solo si la noticia tiene imagen propia (`own_media` u `own_legacy`). Sin imagen propia no se muestra nada: ni fallback ni foto del intérprete/festival relacionado.

## Fuera de alcance
Otros componentes de tarjeta (eventos, artistas, discos), imagen del detalle de la noticia y metadatos Open Graph.

## Criterios de aceptación
- Dado un listado con noticias sin imagen propia, cuando se renderiza, entonces la tarjeta muestra título, extracto, fecha y categoría sin `<img>`.
- Dada una noticia con imagen propia, entonces la tarjeta conserva la imagen.
- Dada una noticia sin imagen propia pero con intérprete con foto, entonces no se muestra imagen.
- La Home no contiene el texto "Ya reunimos" y muestra hasta 24 noticias.

## Impacto
- Performance: menos requests de imagen y DOM; una consulta menos (totales). 24 tarjetas con eager loading existente.
- SEO: el H1 y la metadescripción no cambian; la bajada fija mantiene texto introductorio. Sin cambios de URLs.
- Caché `home:ultimas-noticias` conserva la clave (se invalida sola a los 10 min o al publicar).

## Validación
Test Feature `tests/Feature/HomeNewsCardsTest.php`.
