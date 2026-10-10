## Context
Conjuntos y lugares ya migrados y validados en local. Sidebar definido en AdminLTE, con permisos por enlace. Base persistente.
## Goals / Non-Goals
Goals: tres ejemplos comprensibles y navegación alfabética dentro de cada sección/submenú.
Non-Goals: compactar formularios, producción, migraciones, modificar eventos o intérpretes anteriores.
## Decisions
- Script PHP revisable restringido a APP_ENV local y base mfa, transacción y servicios existentes. Dos conjuntos de 4/5 jornadas y un evento simple; dos sedes ficticias reutilizadas, intérpretes activos existentes. Todos publicados únicamente en local para probar frontend, con aviso explícito de programación ficticia. Sin fuentes ni coordenadas inventadas.
- Identificar por títulos exclusivos y guardar manifiesto de IDs/URLs en storage local. Repetición completa no inserta; estado parcial detiene, no repara ni sobrescribe. No crear usuarios ni modificar perfiles.
- Menú estático ordenado sin consultas ni JS nuevos. Conservar encabezados actuales y permisos; Pasarela plegable sin gate global que oculte accesos con permisos específicos.
## Risks / Trade-offs
Demo confundida con contenido real → prefijo y texto visible en todas las fichas. Relaciones ficticias a artistas → aviso explícito, sin alterar perfiles.
Duplicados o fallos → comprobación previa y transacción; manifiesto exportable otra vez sin insertar.
## Migration Plan
Sin esquema. Ejecutar script autorizado como www-data y verificar counts/relaciones/HTTP. Borrar demos requerirá autorización y revisión posterior; no eliminación automática.
## Open Questions
Sin bloqueos. QA visual no disponible en esta sesión; validar HTML con tests HTTP.
