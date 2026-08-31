# INI-0020 — Release checklist multimedia transversal

## Alcance
Release independiente del núcleo multimedia. No incluye upgrades funcionales de Festivales, Biografías, Recetas, Mitos ni Discografía.

## Evidencia técnica previa
- CI #61 verde.
- Migraciones completas sobre MySQL en CI.
- PHP syntax OK.
- Apps Script syntax OK.
- Blade compile OK.
- PHPUnit específico INI-0020 OK.
- 11 fallbacks WebP presentes y validados por test.
- GitHub ↔ Apps Script sincronizado con clasp.
- FEATURED_IMAGE_URL transportado por Evergreen, Eventos y Noticias.
- Auditor local read-only ejecutado sin errores de resolución.

## Smoke visual previo al release
Validar en entorno ejecutable:
1. News: card + detalle con imagen propia, legacy, relacionada y fallback.
2. Event: listado + detalle con imagen propia/relacionada/fallback.
3. Festival: card + detalle.
4. Knowledge: detalle.
5. Artist: card/header.
6. Album: card/detalle/sidebar.
7. Recipe: card/detalle.
8. Myth: card/detalle.
9. Confirmar que fallbacks minimal usan contain y no quedan recortados de forma absurda.
10. Confirmar ausencia del placeholder gris en superficies migradas.
11. Confirmar alt razonable y meta/OG image en detalles migrados.

## Prueba de ingestión controlada
Después de desplegar la API INI-0020:
1. Elegir una fila Noticia o Evento en BORRADOR, ENVIAR_API=S y sin carga previa.
2. Completar FEATURED_IMAGE_URL con una URL oficial/promocional verificable.
3. Ejecutar una única carga controlada.
4. Esperar HTTP 201 y CREADO_DRAFT.
5. Verificar ID_WEB, FECHA_ENVIO_API y ENVIAR_API=N.
6. Verificar media_assets asociado al contenido.
7. Verificar source_url y metadatos de procedencia cuando correspondan.
8. Abrir el draft en panel/front y confirmar que EditorialImageResolver utiliza el asset ingerido.

No usar Evergreen para la primera prueba porque el Apps Script actual publica Evergreen automáticamente.

## Auditoría real en producción
Una vez desplegado el release:
`php artisan mfa:images:audit --published`

Interpretación:
- own_media: correcto.
- own_legacy: compatible; no requiere migración inmediata.
- related: correcto si la relación es editorialmente lógica.
- fallback: no es error por sí mismo; sólo reparar cuando exista una imagen verificable adecuada.

Generar CSV sólo si hace falta inspeccionar casos:
`php artisan mfa:images:audit --published --csv=storage/app/image-audit.csv`

## Rollout
1. Confirmar backup operativo habitual de base/código.
2. Merge del PR sólo con aprobación explícita.
3. Deploy de código.
4. Ejecutar migraciones.
5. Limpiar/recargar caches según procedimiento habitual.
6. Smoke visual de las superficies listadas.
7. Ejecutar auditoría read-only de producción.
8. Ejecutar una prueba controlada FEATURED_IMAGE_URL.
9. Observar logs/API y errores de imagen.
10. Cerrar BL-0020I y BL-0020J sólo con evidencia positiva.

## Rollback
Si hay regresión visual o funcional:
1. Revertir el release/commit desplegado según procedimiento habitual.
2. Si la aplicación vuelve a una versión que no conoce source_url/source_type/rights_status, las columnas adicionales son compatibles y pueden permanecer temporalmente.
3. Sólo si es necesario revertir esquema: ejecutar rollback de la migración
   `2026_08_31_170000_add_source_metadata_to_media_assets.php`.
4. Las columnas son nullable y no sustituyen campos previos; no existe transformación destructiva de datos legacy.
5. Los fallbacks viven como assets estáticos y su retirada no modifica contenido editorial.

## Gate
No iniciar el upgrade funcional de Festivales hasta:
- smoke visual aprobado;
- ingestión FEATURED_IMAGE_URL validada;
- auditoría real revisada;
- rollback confirmado;
- aprobación explícita de Eduardo.
