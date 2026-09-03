## 1. Relevamiento y decisiones de transición

- [x] 1.1 Inventariar en modo solo lectura la tabla, modelo, rutas, vistas, tráfico y datos de Peñas legacy.
- [x] 1.2 Confirmar modelos, claves y convenciones reales para Provincia, Localidad, Evento, Artista/Intérprete, Festival, media, permisos y auditoría.
- [x] 1.3 Definir el nombre de la tabla y modelo canónicos, la estrategia de slug y la base de URL pública sin colisionar con rutas existentes.
- [x] 1.4 Definir el umbral de vigencia para contacto, horarios y reservas, y documentar los requisitos editoriales de publicación.
- [x] 1.5 Diseñar el mapeo legacy-a-canónico, criterios de equivalencia y condiciones para una futura redirección 301.

## 2. Persistencia y dominio canónicos

- [x] 2.1 Crear migraciones nuevas y reversibles para la entidad canónica, fuentes, verificación, ubicación y campos específicos de Peña, sin editar migraciones históricas.
- [x] 2.2 Crear la relación persistida con Eventos y solo las relaciones editoriales adicionales que estén confirmadas por modelos existentes.
- [x] 2.3 Implementar modelos, casts, scopes de visibilidad, relaciones y generación segura de slug.
- [x] 2.4 Crear factories y fixtures mínimas para pruebas, con registros publicados, no verificables, archivados y con agenda futura/pasada.
- [x] 2.5 Ejecutar `php artisan migrate:status`, presentar la migración y esperar autorización humana antes de cualquier ejecución de migraciones.

## 3. Reglas editoriales, permisos y API

- [x] 3.1 Implementar requests y servicio de dominio para alta, edición, publicación, archivado y sincronización de relaciones.
- [x] 3.2 Implementar policy y registrar autorización siguiendo los patrones existentes de admin y API.
- [x] 3.3 Crear endpoints API autenticados para listado, detalle, creación y actualización, con búsqueda, filtros y paginación.
- [x] 3.4 Impedir publicación por API cuando falten ubicación, fuentes, verificación u otros campos obligatorios.
- [ ] 3.5 Definir el contrato de auditoría y dejar la integración de Content Refresh desactivada hasta cumplir el gate de release.

## 4. Backoffice y auditor de calidad

- [x] 4.1 Crear listado administrativo con filtros por estado, territorio, tipo, verificación y faltantes de calidad.
- [x] 4.2 Crear formularios de alta y edición con Select2 o equivalente para relaciones extensas y campos de contacto, reservas, accesibilidad, fuentes y SEO.
- [x] 4.3 Incorporar acciones de vista previa, publicar, archivar y despublicar con validaciones y autorización.
- [x] 4.4 Implementar auditor de calidad con faltantes, score y priorización editorial, reutilizando patrones existentes cuando sean compatibles.

## 5. Frontend, navegación y SEO

- [x] 5.1 Crear la landing nacional de Peñas con búsqueda, filtros territoriales y por tipo, paginación, eager loading y caché selectiva.
- [x] 5.2 Crear el detalle público de Peña con información permanente, fuentes, fecha de verificación, contacto y accesibilidad.
- [x] 5.3 Mostrar únicamente Eventos futuros y publicados vinculados de forma explícita; ocultar el bloque cuando no existan.
- [ ] 5.4 Incorporar relaciones editoriales y enlaces internos rastreables solo cuando existan asociaciones persistidas.
- [x] 5.5 Implementar canonical, robots para filtros, breadcrumbs, metadata persistida, JSON-LD apropiado y sitemap condicionado por estado y verificación.
- [x] 5.6 Retirar la ruta legacy vacía por autorización explícita, sin 301 al no existir registros ni equivalencias aprobadas.

## 6. Piloto editorial y release gate

- [ ] 6.1 Definir el lote piloto de al menos diez Peñas y verificar identidad, ubicación, contacto, fuentes, vigencia y derechos de media.
- [ ] 6.2 Cargar o migrar el lote únicamente mediante el flujo aprobado, conservando la procedencia legacy cuando corresponda.
- [ ] 6.3 Validar fichas, filtros, agenda relacionada, canonical, schema, sitemap y enlaces internos en staging o entorno seguro.
- [ ] 6.4 Habilitar Content Refresh solo después de validar CRUD/API/auditor y los casos controlados definidos para el módulo.
- [ ] 6.5 Ejecutar smoke, monitoreo de indexación y plan de rollback antes de activar el directorio de manera amplia.

## 7. Pruebas, rendimiento y documentación

- [x] 7.1 Crear tests feature con `DatabaseTransactions` para permisos, CRUD, validaciones de publicación, filtros, paginación y visibilidad pública.
- [x] 7.2 Crear tests para excluir Eventos vencidos o no públicos, evitar relaciones inferidas y confirmar que las URLs legacy retiradas no se exponen.
- [x] 7.3 Crear pruebas SEO para canonical, robots, schema y sitemap, incluyendo la exclusión de perfiles no verificables.
- [x] 7.4 Revisar rendimiento de listados y detalle para evitar N+1, carga de media innecesaria y URLs de filtros de bajo valor.
- [x] 7.5 Actualizar la documentación operativa y `project/docs/00_estado_actual.md` solo al completar hitos verificables del módulo.
