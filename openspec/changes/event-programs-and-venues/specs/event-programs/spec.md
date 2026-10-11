## ADDED Requirements

### Requirement: Carga común de jornadas
El sistema SHALL permitir crear una edición con hasta 60 jornadas explícitas, datos e imagen comunes y artistas/horarios propios; cada jornada SHALL ser un Event con URL propia. SHALL permitir agregar jornadas y preservar excepciones al corregir datos comunes.
#### Scenario: Tres jornadas con artistas distintos
- **GIVEN** un editor autorizado carga una edición y tres jornadas
- **WHEN** guarda
- **THEN** se crean tres eventos vinculados, cada uno con sus artistas y fecha, copiando defaults comunes en una transacción
#### Scenario: Corrección común con excepción
- **WHEN** se modifica una entrada común y una jornada tenía una entrada distinta
- **THEN** se actualizan las jornadas que heredan y se conserva la excepción
#### Scenario: Jornada nocturna
- **WHEN** una jornada comienza el viernes y termina el sábado
- **THEN** se conserva un único evento, sin crear otro día ni inventar artistas

### Requirement: Visibilidad y navegación pública
El sistema SHALL mostrar ficha de conjunto y de cada jornada, con canonical distinto, programación paginada, estados públicos y vínculos recíprocos. SHALL preservar slugs históricos y filtrar fechas por intersección para eventos en curso.
#### Scenario: Jornada borrador
- **WHEN** se visita un conjunto publicado con una jornada pública y otra borrador
- **THEN** solo se muestra la jornada pública y sus artistas
#### Scenario: Conjunto sin jornadas públicas
- **WHEN** se visita un conjunto sin jornadas públicas
- **THEN** responde 404 y no se incluye en sitemap
#### Scenario: Buscar durante la duración
- **WHEN** se busca una fecha comprendida entre inicio y fin de un evento continuo
- **THEN** el evento aparece una sola vez

### Requirement: Permisos editoriales
El sistema SHALL permitir editar solo conjuntos propios o como administrador y SHALL impedir que editores sin permiso de publicación publiquen o vinculen eventos a conjuntos ajenos. Publicar una jornada SHALL NOT publicar otras jornadas automáticamente. Los cambios de editores sin permiso SHALL volver a borrador para revisión.
#### Scenario: Payload de colaborador
- **WHEN** un colaborador sin permiso de publicación intenta publicar su conjunto o asociar una jornada a un conjunto ajeno
- **THEN** la publicación se conserva como borrador y la asociación ajena se rechaza
