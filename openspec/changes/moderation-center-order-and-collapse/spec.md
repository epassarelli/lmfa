# Cambio: moderation-center-order-and-collapse

Estado: **aprobada por el usuario (2026-10-20) e implementada en local**. Validada con tests; falta la revisión visual del usuario.

Ajustes durante la implementación:
- En lugar de un parcial `_card.blade.php`, se usan dos componentes Blade anónimos: `components/backend/moderation-card` y `components/backend/moderation-actions`.
- `Interprete` no tenía relación `user()` (el «Autor» salía siempre vacío): se agregó `belongsTo(User::class)` en `app/Models/Interprete.php`. Como `user_id` tiene valor por defecto 1, los artistas legacy mostrarán a ese usuario como autor.
- `created_at` existe en intérpretes, discos y canciones, así que la columna «Fecha Carga» se muestra en los tres.
- El test queda en `tests/Feature/Backend/ModerationCenterTest.php`.

## Objetivo

Que el Centro de Moderación (`/admin/moderation`) priorice lo que más importa al editor, no muestre ruido y tenga una presentación homogénea.

## Problema actual

- El orden es arbitrario: jornadas, lugares, festivales, noticias, eventos y una tarjeta "Otros" que mezcla artistas, discos y canciones.
- Solo "Noticias" se puede contraer.
- Festivales, jornadas y lugares se muestran como párrafos sueltos, sin la tabla ni las columnas de Noticias.
- "Otros" mezcla tres tipos en una sola tabla y no permite ordenarlos por prioridad.
- Las tarjetas sin pendientes ocupan espacio igual.
- Artistas, discos y canciones acceden a `$item->user` sin eager loading (N+1).

## Alcance incluido

1. **Orden fijo de tarjetas**, de arriba hacia abajo:
   1. Eventos
   2. Jornadas de eventos
   3. Noticias
   4. Intérpretes
   5. Discos
   6. Canciones
   7. Festivales
   8. Lugares propuestos
2. Separar "Otros" en tres tarjetas propias: Intérpretes, Discos y Canciones.
3. **Todas las tarjetas contraíbles** con el mismo botón de Noticias (`data-card-widget="collapse"`) y **contraídas por defecto**. El título mantiene el contador `(n)`.
4. **Tarjetas con 0 pendientes no se renderizan.** Si no queda ninguna, se muestra un único aviso "No hay contenido pendiente de moderación".
5. **Homogeneizar la estética** de Festivales, Jornadas y Lugares con la de Noticias: `card-outline`, tabla `table-sm`, botones `btn-xs` con ícono (Revisar / Publicar).
6. **Columnas** (se agrega o quita según el valor que aportan):

   | Tarjeta | Columnas |
   |---|---|
   | Eventos | Evento, Autor, Fecha Evento, Acciones (sin cambios) |
   | Jornadas | Evento/Jornada, Autor, Acciones |
   | Noticias | Título, Autor, Fecha Carga, Acciones (sin cambios) |
   | Intérpretes / Discos / Canciones | Nombre, Autor, Fecha Carga, Acciones |
   | Festivales | Título, Autor, **Provincia** (nueva), Fecha Carga, Acciones |
   | Lugares | Nombre, Ciudad / Provincia, Acciones |

   Se quita la columna "Tipo" de la tabla "Otros" porque cada tipo pasa a tener su tarjeta. Se agrega Provincia a Festivales porque ayuda a decidir la revisión. "Fecha Carga" en artistas, discos y canciones se muestra solo si la tabla legacy tiene `created_at`; si no, se omite para ese tipo.
7. **Eager loading** (`user`, `provincia`, `creator`) en todos los listados para evitar N+1.
8. **Mantener abierta la tarjeta donde se acaba de publicar.** Al publicar un ítem, el redirect vuelve al índice con esa tarjeta expandida, para no obligar a reabrirla en cada acción. Las demás siguen contraídas. *(Decisión propia, no pedida: si no te gusta, se saca.)*

## Fuera de alcance

- **Peñas y radios**: no existen en la cola de moderación (`ModerationQueue`) ni en `publish`. Las radios no tienen campo de estado visible. Se tratan en un cambio aparte; si hace falta una columna, será una migración con aprobación propia.
- Cambiar qué se considera "pendiente" (`ModerationQueue`), el tablero del administrador o sus contadores.
- Rutas, permisos (`role:administrador`), lógica de `publish` y modelos.
- Paginación o filtros de los listados.
- Migraciones o cambios de BD.

## Archivos afectados

- `resources/views/backend/moderation/index.blade.php` (reescritura de la estructura)
- `resources/views/backend/moderation/_card.blade.php` (parcial reutilizable, nuevo)
- `app/Http/Controllers/Backend/ModerationController.php` (eager loading y redirect con tarjeta abierta)
- `tests/Feature/Backend/ModerationCenterTest.php` (nuevo, con `DatabaseTransactions`)

## Reglas funcionales

### Orden
Dado un administrador con pendientes en todos los tipos
Cuando abre el Centro de Moderación
Entonces las tarjetas aparecen en el orden indicado arriba.

### Contraído por defecto
Dado un administrador con pendientes
Cuando abre el Centro de Moderación
Entonces todas las tarjetas se ven contraídas, con el título y el contador visibles, y se expanden con el botón de la tarjeta.

### Tarjetas vacías ocultas
Dado que no hay festivales en borrador
Cuando se abre el Centro de Moderación
Entonces no se renderiza la tarjeta de Festivales.

### Sin pendientes
Dado que no hay ningún pendiente
Cuando se abre el Centro de Moderación
Entonces se muestra el aviso "No hay contenido pendiente de moderación" y ninguna tarjeta.

### Festivales con estética de Noticias
Dado un festival en borrador
Cuando se abre el Centro de Moderación y se expande Festivales
Entonces se ve en una tabla con Título, Autor, Provincia, Fecha Carga y los botones Revisar y Publicar, igual que Noticias.

### Publicar mantiene el contexto
Dado un administrador que publica un festival desde su tarjeta
Cuando termina la acción
Entonces vuelve al índice con la tarjeta de Festivales expandida y las demás contraídas.

### Permisos sin cambios
Dado un usuario sin rol administrador
Cuando entra al Centro de Moderación
Entonces sigue sin tener acceso.

## Reglas técnicas

- Blade clásico y AdminLTE 3; sin JS nuevo (se usa el widget `collapse` de AdminLTE).
- Estado inicial contraído: clase `collapsed-card` en la tarjeta e ícono `fa-plus` en el botón.
- Sin cambios de rutas, URLs ni nombres de ruta.
- Sin migraciones. No se tocan datos.
- Misma definición de "pendiente": se sigue usando `ModerationQueue`.

## Validación

- Test Feature (`DatabaseTransactions`, base de test): orden, tarjetas contraídas, tarjetas vacías ocultas, aviso sin pendientes, columnas de Festivales y redirect de `publish`.
- Correr también `AdminAttentionDashboardTest` y `BackofficeSmokeTest` para detectar regresiones.
- Antes de ejecutar: verificar que usen `mfa_test` y no la base de desarrollo.
- Revisión visual en navegador a cargo del usuario (los tests no validan la apariencia).

## Performance y SEO

- Menos consultas: se eliminan los N+1 de artistas, discos y canciones.
- Tarjetas vacías no se renderizan, así que el DOM es más chico.
- Backoffice autenticado: sin impacto SEO ni caché pública.
- Las tarjetas contraídas siguen enviando su contenido en el HTML. Con volúmenes altos convendría paginar, pero queda fuera de este cambio.

## Riesgos

- Contraer todo agrega un clic por revisión. Se mitiga con la regla "publicar mantiene el contexto".
- Si alguna tabla legacy (artistas, discos, canciones) no tiene `created_at`, esa columna se omite para ese tipo.
- Queda pendiente la decisión de peñas y radios, que se difiere.

## Criterio de aceptación

El cambio se considera terminado cuando el orden, el contraído por defecto, la ocultación de tarjetas vacías y la estética de Festivales se verifican con tests, la suite relacionada no tiene regresiones nuevas y el usuario validó visualmente la pantalla en local.
