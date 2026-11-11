# Checklist de despliegue v2.6.0 — Home con bloques de catálogo (2026-11-11)

Estado: **preparado, no desplegado**. Ninguno de estos pasos se ejecutó contra producción; los hace el responsable del despliegue.

## 0. Qué se despliega
Diferencia `main..dev` (`git diff --name-only main..dev`): 12 archivos, sin migraciones, sin `composer.lock`, sin variables de entorno ni rutas nuevas.

- Home: 24 noticias, enlace «Ver todas las noticias», cuatro bloques de 4 (próximos eventos, últimos intérpretes, festivales más visitados, últimos discos) y bajada fija sin contador. Specs: `home-noticias-sin-fallback.md` y `home-bloques-catalogo.md`.
- `x-noticia-card`: imagen solo si la noticia tiene una propia, en todos los listados que usan la tarjeta (Home, /noticias, categorías, artista, relacionadas, búsqueda).
- `AppServiceProvider`: invalida `home:bloques` al guardar o borrar contenido.
- Build: `public/build` recompilado (3 CSS con hash nuevo + `manifest.json`); el servidor no tiene Node.
- **No incluye** Matecito (rama `matecito`, pendiente de perfil y revisión).

## 1. Antes de subir
- [ ] `git status` limpio en `dev` y tests Feature en verde en `mfa_test` (único fallo conocido, previo y ajeno: `BackofficeSidebarTest` por el orden alfabético del menú).
- [ ] `npm run build && npm run build:verify` (ya corrido: 13 assets). Confirmar que `public/build/manifest.json` coincide con los CSS/JS versionados.
- [ ] Confirmar que `public/hot` **no** está en el servidor (si existe, el sitio pide los assets a un servidor Vite inexistente y se ve sin estilos).
- [ ] Backup habitual de la base (el release no migra, pero es la práctica del proyecto).
- [ ] Anotar el commit de `main` desplegado actualmente (para volver atrás).

## 2. Despliegue (lo ejecuta el responsable)
1. Merge de `dev` a `main` y push, según el flujo y CI vigentes.
2. En el servidor: `git pull` en `main`; verificar que llegaron completos `public/build/manifest.json` y `public/build/assets/*`.
3. `bash scripts/post-deploy.sh` (autoload, limpieza y regeneración de cachés, `responsecache:clear`).

## 3. Verificación posterior
- [ ] `/` responde 200, con el CSS de `/build/assets/app-public-*.css` cargado y estilos correctos.
- [ ] Se ven las 24 noticias; las que no tienen imagen propia aparecen sin imagen, sin foto del artista.
- [ ] Botón «Ver todas las noticias» → `/noticias-del-folklore-argentino`.
- [ ] Bloques: próximos eventos, últimos intérpretes, festivales más visitados y últimos discos (de a 4 como máximo; un bloque sin datos no se muestra); cada enlace de sección funciona.
- [ ] Ya no aparecen el contador ni «Destacados del catálogo».
- [ ] Mirar la tarjeta de eventos en celular (imagen alta `h-96` en 2 columnas) y el LCP de la Home.
- [ ] `/noticias-del-folklore-argentino`, una categoría, una ficha de artista y la búsqueda con tarjetas sin imagen (el cambio de tarjeta aplica a todos).
- [ ] Medir TTFB/LCP de la Home en producción (no hay medición real hasta ahora).

## 4. Reversa
Sin migraciones: basta volver al commit anterior de `main` (con su `public/build`) y correr `bash scripts/post-deploy.sh`. Mientras tanto, `home:bloques` y `home:ultimas-noticias` caducan solos a los 10 minutos.

## 5. Observaciones
- El caché de la Home puede tardar hasta 10 min en reflejar contenido nuevo si no pasa por un guardado normal (por ejemplo cargas directas en la base).
- El sitemap y el SEO de la Home no cambian (H1, metadescripción y URLs intactos); sumó enlaces internos a las secciones.
