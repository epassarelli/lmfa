# Cambio: NEWS-007 - Autopublicacion para administradores en backend

## Objetivo

Evitar que un usuario con rol Administrador tenga que volver a publicar o reactivar contenido que el mismo crea o edita desde el backend.

## Problema actual

El backend no es consistente entre tipos de contenido:

- `News` y `Event` usan `editorial_status` y hoy no garantizan publicacion directa para administradores en todos los casos.
- `Festival` en alta publica al administrador, pero en edicion no fuerza el contenido a publicado.
- `Interprete` en alta publica al administrador, pero en edicion no autopublica si el registro estaba pendiente.

Esto obliga al administrador a hacer pasos manuales posteriores para contenido que el mismo ya esta cargando o editando.

## Alcance incluido

- Ajustar el alta de noticias para que si el usuario autenticado es administrador el contenido quede en estado `published`.
- Ajustar la edicion de noticias para que si el usuario autenticado es administrador el contenido quede en estado `published`.
- Ajustar el alta de eventos/shows para que si el usuario autenticado es administrador el contenido quede en estado `published`.
- Ajustar la edicion de eventos/shows para que si el usuario autenticado es administrador el contenido quede en estado `published`.
- Ajustar la edicion de festivales para que si el usuario autenticado es administrador el contenido quede con `estado = 1`.
- Ajustar la edicion de interpretes para que si el usuario autenticado es administrador el contenido quede con `estado = 1`.
- Mantener el comportamiento actual para usuarios no administradores.

## Fuera de alcance

- No cambiar permisos ni roles.
- No modificar flujos de moderacion de contribuciones.
- No alterar modelos, rutas o vistas no relacionadas.
- No tocar tipos de contenido que ya cumplen este comportamiento en alta y edicion (`Album`, `Cancion`, `Mito`, `Comida`).
- No cambiar logica de eliminacion.

## Archivos afectados

- `app/Services/NewsService.php`
- `app/Services/EventService.php`
- `app/Http/Controllers/Backend/FestivalController.php`
- `app/Http/Controllers/Backend/InterpreteController.php`
- `tests/Feature/` con tests puntuales para validar el nuevo comportamiento

## Reglas funcionales

### Caso 1
Dado un administrador autenticado
Cuando crea una noticia desde el backend
Entonces la noticia debe guardarse con `editorial_status = published`

### Caso 2
Dado un administrador autenticado
Cuando edita una noticia desde el backend
Entonces la noticia debe quedar con `editorial_status = published`

### Caso 3
Dado un administrador autenticado
Cuando crea un evento/show desde el backend
Entonces el evento debe guardarse con `editorial_status = published`

### Caso 4
Dado un administrador autenticado
Cuando edita un evento/show desde el backend
Entonces el evento debe quedar con `editorial_status = published`

### Caso 5
Dado un administrador autenticado
Cuando edita un festival desde el backend
Entonces el festival debe quedar con `estado = 1`

### Caso 6
Dado un administrador autenticado
Cuando edita un interprete desde el backend
Entonces el interprete debe quedar con `estado = 1`

### Caso 7
Dado un usuario no administrador
Cuando crea o edita cualquiera de estos contenidos
Entonces debe mantenerse el comportamiento actual sin cambios

## Reglas tecnicas

- Reutilizar `auth()->user()->isAdmin()` como criterio de administrador cuando exista esa API.
- En controladores legacy que hoy usan `hasRole('administrador')`, mantener consistencia con la implementacion existente o normalizarla solo si es necesario para cumplir el cambio sin alterar otra logica.
- Mantener el comportamiento existente de `canPublish()` para usuarios no administradores.
- Si el estado final es `published`, respetar la logica existente de `published_at`.
- No introducir cambios de arquitectura ni nuevas tablas.

## Validacion

- Probar alta de noticia como admin.
- Probar edicion de noticia como admin.
- Probar alta de evento como admin.
- Probar edicion de evento como admin.
- Probar edicion de festival como admin.
- Probar edicion de interprete como admin.
- Probar que un no admin conserve el flujo actual.

## Riesgos

- Que una condicion de seguridad termine sobrescribiendo el estado final del administrador.
- Que haya diferencias entre `isAdmin()` y `hasRole('administrador')` en codigo legacy.
- Que la edicion de registros pendientes no se reactive correctamente si el cambio se aplica solo en una rama del flujo.

## Criterio de aceptacion

El cambio se considera terminado cuando un administrador puede crear y editar noticias y eventos, y editar festivales e interpretes desde el backend, y el contenido queda publicado o activo automaticamente, sin afectar el comportamiento de usuarios no administradores ni de contenidos fuera de alcance.
