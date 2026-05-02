# Cambio: Unificación de Flujos de Noticias

## Objetivo
Centralizar toda la lógica de creación, actualización y procesamiento de noticias en el `NewsService` para asegurar que todas las noticias (vengan del Admin, API o Colaboraciones) sigan las mismas reglas de negocio, validaciones y optimización de imágenes (WebP).

## Problema actual
Existen múltiples puntos de entrada (`NewsController`, `Api\NewsController`, `ContributionController`, `ModerationController`) que crean o modifican noticias de forma inconsistente. Algunos flujos saltan el procesamiento de imágenes WebP, no validan la unicidad del slug o ignoran el mapeo de campos legacy.

## Alcance incluido
- Creación de `ImageSourceResolver` para manejar `UploadedFile`, Paths locales y URLs externas.
- Refactorización de `NewsService` para desacoplarlo de `auth()->id()` y soportar `approved_by`.
- Unificación de los flujos de aprobación de contribuciones y moderación directa usando el servicio central.
- Implementación de seguridad anti-SSRF para descargas de imágenes externas.
- Limpieza garantizada de archivos temporales.

## Fuera de alcance
- No se modifica la interfaz visual del backend ni del frontend.
- No se alteran otros servicios de carga de imágenes (artistas, festivales, etc.) por ahora.
- No se modifica el flujo de la Newsletter.

## Archivos afectados
- `app/Services/ImageSourceResolver.php` (Nuevo)
- `app/Services/NewsService.php` (Modificación)
- `app/Http/Controllers/Backend/ContributionController.php` (Modificación)
- `app/Http/Controllers/Backend/ModerationController.php` (Modificación)
- `app/Exceptions/ImageSourceException.php` (Nuevo)

## Reglas funcionales

### Caso 1: Aprobación de Contribución con Imagen Externa
- **Dado** una contribución con una URL de imagen externa.
- **Cuando** el administrador hace clic en "Aprobar".
- **Entonces** el sistema descarga la imagen, la valida (< 5MB, tipo imagen), la convierte a WebP con variantes, crea la noticia asociada al colaborador original y marca al administrador como moderador.

### Caso 2: Intento de SSRF vía URL
- **Dado** una URL maliciosa (ej: `http://169.254.169.254/latest/meta-data/`).
- **Cuando** se intenta procesar.
- **Entonces** el `ImageSourceResolver` lanza una `ImageSecurityException` y bloquea la descarga.

## Reglas técnicas
- Usar `Http::fake()` para pruebas de red.
- Usar `try/finally` para asegurar el `unlink()` de archivos temporales.
- El contrato de imagen debe ser `UploadedFile|string|null`.
- El storage temporal será `storage/app/tmp/news-images`.

## Validación
- Ejecutar suite de tests `NewsServiceUnificationTest`.
- Verificar en BD que `created_by` y `approved_by` sean correctos tras una aprobación.
- Comprobar que no queden archivos en `storage/app/tmp/news-images` tras un error.

## Riesgos
- **Timeouts**: Las URLs externas pueden demorar la respuesta del servidor. (Mitigación: timeout corto de 5s).
- **Consumo de Memoria**: Procesar imágenes muy grandes puede agotar el límite de PHP. (Mitigación: validación de tamaño previo).

## Criterio de aceptación
El cambio se considera terminado cuando todos los controladores de noticias utilizan el `NewsService` para persistir datos y se han verificado los tests de integración con las 3 fuentes de imagen.
