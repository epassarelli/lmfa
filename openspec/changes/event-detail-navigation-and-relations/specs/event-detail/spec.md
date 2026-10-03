## ADDED Requirements

### Requirement: La ficha de evento muestra sus datos clave
La ficha pública de un evento SHALL mostrar fecha y hora, lugar, provincia, precio o gratuidad y enlace de entradas cuando esos datos existan, antes del cuerpo del evento.

#### Scenario: Evento con datos completos
- **WHEN** un visitante abre la ficha de un evento publicado con fecha, ciudad, provincia, precio y `ticket_url`
- **THEN** ve esos datos antes del cuerpo y un botón externo de entradas

### Requirement: La ficha de evento mantiene la navegación de la cartelera
La ficha SHALL incluir un buscador compacto de la cartelera, un breadcrumb con la provincia y un bloque para explorar provincias con eventos futuros.

#### Scenario: Buscar desde la ficha
- **WHEN** el visitante envía el buscador desde la ficha
- **THEN** llega al listado de la cartelera con esos filtros aplicados

### Requirement: La ficha de evento ofrece continuidad
La ficha SHALL listar próximos eventos de la misma provincia y del artista principal, excluyendo el evento actual y el contenido no público.

#### Scenario: Evento ya realizado
- **WHEN** el visitante abre un evento cuya fecha ya pasó
- **THEN** la respuesta es 200, ve un aviso de evento realizado y ve próximos eventos relacionados si existen

### Requirement: La ficha de evento vincula entidades relacionadas
La ficha SHALL mostrar, si existen y son públicos, los artistas, los festivales, los artículos de enciclopedia, las peñas (con su flag) y las noticias derivadas del festival o de los artistas del evento.

#### Scenario: Relaciones sin contenido
- **WHEN** un evento no tiene ítems públicos para un bloque relacionado
- **THEN** ese bloque no se renderiza
