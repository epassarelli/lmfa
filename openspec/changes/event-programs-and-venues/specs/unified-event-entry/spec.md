## ADDED Requirements

### Requirement: Ingreso único de eventos
Backoffice SHALL ofrecer solo Eventos / Cartelera en sidebar y un único Nuevo evento con elección Una fecha o Varias jornadas. Ambos formatos SHALL guardar por backend.events.store con validación del formato activo y redirección a la edición correspondiente. Los campos comunes SHALL cargarse una vez. Horarios y artistas SHALL ser individuales por jornada. Una jornada nocturna no exige varias jornadas por cruzar medianoche.
#### Scenario: Carga de varias jornadas
- **WHEN** el editor elige Varias jornadas en Nuevo evento y carga dos o más jornadas
- **THEN** se guarda un EventProgram con jornadas mediante los servicios existentes y se abre Editar evento con jornadas.
#### Scenario: Error de validación
- **WHEN** una jornada es inválida
- **THEN** no se guarda ninguna fila y se conserva formato y datos ingresados al mostrar errores.

### Requirement: Listado y edición comprensibles
Eventos SHALL mostrar una fila por evento independiente o por programación con jornadas, con cantidad de jornadas y acceso a datos comunes. SHALL permitir agregar jornadas a un evento independiente sin reemplazar su Event, slug, artistas ni relaciones. SHALL conservar rutas históricas de edición de jornadas, permisos, estados de publicación, y la navegación pública.
#### Scenario: Demos existentes
- **WHEN** el usuario busca DEMO LOCAL
- **THEN** ve los dos eventos con jornadas una sola vez y el evento independiente, con enlace a la edición correcta.
#### Scenario: Agregar jornadas
- **WHEN** un editor autorizado solicita agregar jornadas a un evento independiente
- **THEN** su Event se vincula como primera jornada a una nueva programación y puede agregar otras desde la edición del evento.
#### Scenario: Otro editor
- **WHEN** un colaborador intenta convertir un evento ajeno
- **THEN** recibe 403 sin modificarlo.
#### Scenario: Administrador con múltiples roles
- **WHEN** una cuenta tiene administrador y colaborador o prensa
- **THEN** el listado conserva la visibilidad administrativa de todos los eventos; solo usuarios sin rol administrador reciben el filtro por autor.
