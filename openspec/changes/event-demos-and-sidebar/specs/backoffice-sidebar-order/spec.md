## ADDED Requirements
### Requirement: Navegación directa y agrupada
Sidebar MUST mostrar Discos, Letras/Canciones, Festivales, Peñas, Comidas Típicas y Mitos y Leyendas directamente en Contenido Editorial; MUST agrupar los cuatro enlaces de Pasarela de Contenidos en un submenú. Conservar URLs, iconos, activación y permisos.
#### Scenario: Administrador
- **WHEN** abre Backoffice
- **THEN** Música y Cultura ya no es un grupo y Pasarela de Contenidos es plegable con sus cuatro enlaces.
#### Scenario: Permisos
- **WHEN** el usuario carece del permiso de un enlace
- **THEN** ese enlace sigue oculto por GateFilter.
### Requirement: Orden alfabético
Los enlaces MUST estar ordenados ASC por texto visible en cada sección y en todos los submenús, ignorando mayúsculas y tildes para la comparación. Los encabezados delimitadores mantienen su posición.
#### Scenario: Usuarios y Roles
- **WHEN** se despliega el grupo
- **THEN** muestra Permisos, Roles y Usuarios en ese orden.
