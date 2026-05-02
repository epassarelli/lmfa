# Cambio: NEWS-006 — Suite de Tests Integrales

## Objetivo
Garantizar la estabilidad y seguridad de los nuevos flujos unificados de noticias mediante pruebas automatizadas.

## Problema actual
La falta de cobertura en los flujos de "Moderación" y "Contribución" hace que los cambios en el servicio de imágenes o en el modelo puedan romper la publicación sin ser detectado hasta que un usuario reporta un error.

## Alcance incluido
- `tests/Feature/News/UnifiedNewsFlowTest.php`
- Cobertura para:
    - Creación manual (Backend).
    - Aprobación de contribución (Frontend -> Backend).
    - Publicación de borrador (Moderación).
    - Descarga segura de imagen externa (Mocked HTTP).

## Fuera de alcance
- No se testean las vistas (solo lógica de controladores y servicios).

## Reglas técnicas
- Usar `Http::fake()` para simular servicios externos.
- Usar `Storage::fake('public')`.
- No usar `RefreshDatabase` (usar transacciones o limpieza manual).

## Criterio de aceptación
- Todos los tests pasan en el entorno local.
- Se ha probado específicamente el caso de intento de SSRF y es bloqueado por las excepciones de NEWS-002.
