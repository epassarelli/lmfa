# Cambio: NEWS-003 — Refactorización de NewsService

## Objetivo
Actualizar el servicio central de noticias para integrar el nuevo `ImageSourceResolver` y permitir la gestión explícita de autores y moderadores.

## Problema actual
`NewsService` asume que el usuario autenticado es siempre el creador de la noticia y solo acepta archivos subidos frescos. Esto rompe el flujo de moderación de contribuciones donde el creador es el colaborador y el moderador es el administrador.

## Alcance incluido
- Inyección de `ImageSourceResolver` en el constructor.
- Modificación de firmas de `createNews` y `updateNews` para aceptar `mixed $image`.
- Lógica de fallback para `created_by` y `approved_by`.
- Bloque `try/finally` para asegurar limpieza de archivos temporales descargados.

## Fuera de alcance
- No se cambia la estructura de la base de datos (se usan campos existentes).

## Archivos afectados
- `app/Services/NewsService.php`

## Reglas funcionales
- Si `$data['created_by']` está presente, el servicio DEBE respetarlo.
- Si `$data['published_at']` es null y el estado es `published`, usar `now()`.
- Sincronizar el campo `approved_by` si el flujo viene de una moderación.

## Reglas técnicas
- El método `resolve()` del resolver debe ejecutarse dentro de un `try` para garantizar que el `finally` limpie los archivos en `storage/app/tmp/news-images/`.

## Criterio de aceptación
- El backend de administración sigue funcionando exactamente igual (regresión cero).
- Es posible crear una noticia pasando un path de archivo local o una URL.
