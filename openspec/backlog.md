# Backlog de Mejoras Técnicas — Mi Folklore Argentino

Este archivo contiene las tareas pendientes para la mejora de la arquitectura y funcionalidad del portal.

## 📥 Unificación de Flujos de Noticias [Prioridad: Alta]
Centralizar la creación y actualización de noticias en el `NewsService` para garantizar la consistencia de datos, SEO e imágenes WebP en todos los puntos de entrada (Admin, API, Contribuciones).

- [ ] **[NEWS-001]** Crear servicio `ImageSourceResolver` para normalizar múltiples fuentes de imagen (File, Path, URL).
- [ ] **[NEWS-002]** Implementar jerarquía de excepciones `ImageSourceException` para manejo robusto de errores.
- [ ] **[NEWS-003]** Refactorizar `NewsService` para utilizar el `ImageSourceResolver` y soportar autoría/moderación explícita.
- [ ] **[NEWS-004]** Integrar `NewsService` en `ContributionController@approve` eliminando la creación manual de modelos.
- [ ] **[NEWS-005]** Unificar lógica de publicación en `ModerationController` delegando al servicio centralizado.
- [ ] **[NEWS-006]** Implementar suite de tests integrales para validar los flujos unificados.

## 📈 SEO & Rendimiento [Prioridad: Media]
- [ ] **[SEO-001]** Implementar etiquetas `canonical` dinámicas en controladores frontend.
- [ ] **[SEO-002]** Agregar JSON-LD para entidades `Recipe`, `Event` y `MusicAlbum`.
- [ ] **[PERF-001]** Optimizar el componente `optimized-image` para prevenir CLS (width/height explícitos).

---

## Historial de Tareas Completadas
- (Vacío)
