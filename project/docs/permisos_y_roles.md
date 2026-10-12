# Permisos y roles del backoffice

Modelo vigente desde 2026-10-12 (spec `roles-trust-ladder-and-cleanup`). Resume **quién puede qué y por qué mecanismo**, para no tener que deducirlo de las policies.

## Dos ejes independientes

| Eje | Qué es | Quién lo define | Qué controla |
|---|---|---|---|
| **Rol global** | `administrador`, `colaborador`, `verificado` (o ninguno) | Se asigna al usuario (Usuarios) | Qué puede crear en general y si publica sin revisión |
| **Membresía en una ficha** | `owner`, `editor`, `member` en una organización activa vinculada a un artista o festival | El administrador, al aprobar una solicitud de vínculo | Qué puede editar sobre esa ficha y su contenido |

La relación que declara quien pide el vínculo (artista, representante, organizador, **prensa**, otra) es solo contexto para el administrador: no otorga permisos. "La prensa de un artista" se modela con esa relación + membresía, no con un rol global.

## Roles globales

| Rol | Para qué | Puede |
|---|---|---|
| `administrador` | Equipo del portal | Todo: publicar, borrar, usuarios, roles, moderación |
| `colaborador` | Cualquier persona que aporta | Crear contenido (queda en **revisión**); editar lo propio |
| `verificado` | Persona de confianza (se asigna **además de** `colaborador`) | Todo lo de colaborador, pero lo que escribe (lo propio o de sus fichas) se **publica directo** |
| (sin rol) | Registrado | Solo "Mis fichas"; si un administrador le aprueba una ficha, gestiona el contenido de esa ficha |

`verificado` es aditivo: solo aporta el permiso `publish contents`. Asignarlo solo, sin `colaborador`, no habilita crear.

## Quién publica
`User::canPublish()` = administrador **o** permiso `publish contents` (rol `verificado`). Los servicios y controladores de Eventos, Noticias, Discos, Canciones, Intérpretes y Festivales usan esa única regla; quien no puede publicar siempre queda en borrador/pendiente aunque el request diga otra cosa. Mitos, Comidas, Peñas y Radios siguen en revisión (pendiente de definir).

## Edición: quién puede editar un registro existente
Ordenado de lo más específico a lo más general. Alcanza con que se cumpla una condición (el administrador siempre pasa):
1. **Es el creador** del registro (`user_id` / `created_by`).
2. **Administra el artista** vinculado: membresía activa `owner`/`editor` en una organización activa (`canManageInterpreteId`).
3. **Eventos y noticias con varios artistas:** administra **todos** los artistas vinculados (`managesAllInterpretes`), salvo que sea el creador.

Los permisos globales `update ...` ya **no** permiten editar contenido ajeno. Borrar es solo para el administrador.

## Crear
- Colaborador: crea intérpretes, festivales, discos, canciones, eventos, noticias, mitos, comidas (permisos `create ...` / rol).
- Quien administra al menos un artista: puede crear discos, canciones, eventos y noticias de ese artista; el registro debe incluir un artista propio.
- Sin fichas y sin rol: solo puede **solicitar** vínculo o alta de ficha desde Mis fichas.

## Secciones del sidebar (flag `BACKOFFICE_ACCESS_BY_ENTITIES`)
Con el flag activo, Intérpretes, Discos, Canciones, Festivales y Eventos solo se muestran a quien administra fichas que las habiliten; Noticias, a quien administra un artista o es `verificado`; Mitos y Comidas, solo a `verificado` y administradores; Copa del Folklore, Enciclopedia y Peñas, solo a administradores (ver `config/backoffice_access.php`).

## Los cuatro mecanismos del código
1. **Lista fija de roles** en la policy (`hasAnyRole([...])`): crear contenido.
2. **Permiso por nombre** (`can('create album')`): crear y leer.
3. **Creador** (`user_id` / `created_by`): editar lo propio.
4. **Membresía de ficha** (`canManage*`): editar lo del artista/festival que administra.

Regla práctica: *crear* depende del rol/permiso global; *editar* depende del creador o la membresía; *publicar* depende de `canPublish()`; *borrar* es del administrador.

## Gestión
- Asignar `verificado`: Backoffice → Usuarios → editar → roles (marcar `colaborador` y `verificado`).
- Cambios de roles/permisos de base de datos: migración revisable (`2026_10_12_100000_trusted_publishing_role_and_remove_prensa_role`).

## Aprobación de vínculos (delegada)
- El **administrador** aprueba las solicitudes de alta de ficha, al primer owner de una ficha y cualquier solicitud en cualquier momento (Backoffice → Solicitudes de vínculo).
- El **owner activo** de una ficha aprueba o rechaza desde Mis fichas las solicitudes de vínculo de otros usuarios sobre esa ficha, otorgando Editor o Miembro (nunca Responsable). Interruptor: `ENTITY_CLAIMS_DELEGATE_TO_OWNERS`.

## Eventos: artistas y festivales
Un evento tiene artistas y, opcionalmente, festivales. Para editarlo por membresía (si no sos el creador): si tiene festivales, tenés que administrar **todos** (la grilla de artistas se ignora); si no tiene festivales, tenés que administrar **todos** sus artistas. Para crear, alcanza con incluir un artista o un festival que administrás.

## Entrada desde el sitio público
El botón **Publicar** del sitio lleva a `/admin`: un visitante pasa por el login y vuelve a `/admin`. No existe otro circuito de aportes (el flujo "Contribuir" se retiró). Con el acceso por fichas activo, quien no es administrador aterriza en **Mis fichas**, desde donde solicita el vínculo o el alta de una ficha.

## Mis datos (cuenta propia)
Cualquier usuario con sesión tiene `/admin/mi-cuenta` (botón **Mis datos** en el menú de usuario): edita nombre, teléfono y contraseña; el email es solo lectura. Solo se aceptan esos campos: roles, estado y demás los gestiona el administrador desde Usuarios. No depende del flag de acceso por fichas.

También permite ver y cerrar las **sesiones abiertas** en otros dispositivos y **eliminar la cuenta** (se anonimiza: se borran los datos personales y los vínculos, y el contenido cargado se conserva sin el nombre). Los administradores no pueden eliminar su propia cuenta desde ahí. Quien hace una solicitud de ficha recibe un correo cuando se resuelve.

## Avisos Clasificados
Siguen siendo visibles para **todos** los usuarios con sesión (decisión del 2026-10-13): no forman parte de las secciones restringidas por el acceso por fichas.
