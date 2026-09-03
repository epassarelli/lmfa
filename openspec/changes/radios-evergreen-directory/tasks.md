## 1. Relevamiento y contrato canónico

- [x] 1.1 Inventariar la tabla, modelo, rutas, vistas, media y tráfico de Radios legacy en modo solo lectura.
- [x] 1.2 Confirmar convenciones existentes para Provincia, Locality, media, estados editoriales, permisos, sitemap y API.
- [x] 1.3 Definir los nombres canónicos de señal, canal de escucha, programa y franja semanal sin colisiones con el legado.
- [x] 1.4 Definir el catálogo MVP de modos de emisión, tipos de canal y plataformas permitidas.
- [x] 1.5 Confirmar los requisitos editoriales de publicación para señal por aire, señal digital y programa independiente.

## 2. Persistencia y dominio

- [x] 2.1 Crear migraciones nuevas y reversibles para señales, canales, programas y franjas semanales, sin modificar tablas legacy.
- [x] 2.2 Implementar modelos, casts, relaciones, slugs, scopes de visibilidad y cálculo de próxima emisión.
- [x] 2.3 Implementar requests, servicio de dominio y policies para CRUD, publicación, despublicación, archivo y validaciones por tipo.
- [ ] 2.4 Crear factories y fixtures para FM local, radio web, señal híbrida y programa independiente.

## 3. Backoffice y API

- [ ] 3.1 Crear listados administrativos de señales y programas con filtros de estado, territorio, modo, plataforma y faltantes de calidad.
- [ ] 3.2 Crear formularios de señales, canales, programas y franjas con Select2 para relaciones extensas y UX clara para horarios.
- [ ] 3.3 Incorporar preview, publicar, despublicar y archivar con autorización y validación editorial.
- [ ] 3.4 Crear endpoints API autenticados para señales, canales, programas y programación, con paginación y filtros.
- [ ] 3.5 Implementar auditor read-only con score, faltantes, prioridad y export CSV; mantener Content Refresh desactivado.

## 4. Frontend y SEO

- [ ] 4.1 Crear landing de señales con búsqueda y filtros por territorio, modo y plataforma.
- [ ] 4.2 Crear ficha de señal con información de escucha, frecuencia, ubicación, mapa, contacto, fuentes y programas asociados.
- [ ] 4.3 Crear directorio y ficha de programas que incluya programación de radios y streams independientes.
- [ ] 4.4 Mostrar grilla semanal y próxima emisión exclusivamente desde franjas explícitas.
- [ ] 4.5 Implementar relaciones territoriales, canonical, robots de filtros, metadata, JSON-LD apropiado y sitemap condicionado por publicación/verificación.

## 5. Calidad, demo y release gate

- [ ] 5.1 Crear tests feature con `DatabaseTransactions` para permisos, publicación, filtros, programación, independencia de programas, SEO y visibilidad.
- [ ] 5.2 Revisar consultas, eager loading, paginación, carga de media y enlaces externos para evitar N+1 y recursos pesados.
- [ ] 5.3 Cargar en DEV un escenario demo de al menos diez señales y programas, con todas las variantes del MVP.
- [ ] 5.4 Validar manualmente escucha externa, horarios, filtros, mapas, canonical, schema, sitemap y navegación en entorno seguro.
- [ ] 5.5 Definir lote editorial real, fuentes, derechos de media y plan de rollback antes de habilitar producción o retirar el legado.
