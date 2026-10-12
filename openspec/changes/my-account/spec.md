# Cambio: my-account (avatar en el menu de usuario + pagina "Mis datos")

Estado: APROBADA por el usuario en el chat (2026-10-12): avatar de iniciales (A), sin edicion de email, etapas 1 y 2. Implementada en local.

## Objetivo
Dar a cada usuario con sesion un lugar propio: avatar en el menu del backoffice y una pagina "Mis datos" para actualizar sus datos y su contraseña, con un resumen de su actividad en el portal.

## Etapa 1
- **Menu de usuario (AdminLTE):** avatar de **iniciales** generado (SVG en data URI: sin subida, sin almacenamiento, sin servicios externos), encabezado con nombre y descripcion ("Colaborador verificado · desde oct 2026") y boton **Mis datos** junto a **Salir**.
- **Pagina `/admin/mi-cuenta`** (cualquier usuario autenticado, no depende del flag de acceso por fichas; sin id en la URL: solo se edita a uno mismo):
  - Datos personales: nombre y telefono. **El email es solo lectura** (decision del usuario); se informa como pedir un cambio.
  - Contraseña: actual + nueva (minimo 8) + confirmacion, `throttle`. Aviso para quien ingreso con Google/Facebook y no conoce su contraseña (usar "Olvidaste tu contraseña").
  - Solo lectura: rol, miembro desde, ultimo ingreso.
- Lista cerrada de campos editables (`name`, `phone`, `password`): `User::$fillable` incluye `status`, `is_verified_publisher`, `google_id`, etc., que nunca se aceptan desde esta pantalla.
- **Reutilizacion de Usuarios:** reglas compartidas (`App\Support\UserRules`) entre el CRUD del administrador y Mis datos (el administrador tambien pasa a poder cargar/editar el telefono; minimo de contraseña unificado en 8).
- **`last_login_at`** se completa en cada login (a lo sumo una vez cada 10 minutos).

## Etapa 2 (en la misma pagina)
- **Mis fichas:** resumen con enlaces de edicion (reutiliza el mismo listado que Mis fichas), conteo de solicitudes propias (pendientes/aprobadas/rechazadas) y de solicitudes por revisar si es responsable de una ficha.
- **Mi contenido:** discos, canciones, artistas, festivales, eventos y noticias creados por la persona, con total, publicados y en revision (solo filas con datos). No guarda nada: se calcula al abrir la pagina. Sirve ademas como señal para decidir a quien dar el rol `verificado`.
- **Newsletter:** suscribirse o darse de baja con el email de la cuenta (misma semantica `active`/`unsubscribed`, `source = account`).

## Sitio publico (punto 4 del pedido): NO se implementa ahora
Las paginas publicas se sirven desde la cache de pagina completa y esa decision se toma **antes** de iniciar la sesion (ver `PublicPagesCacheProfile`): un menu con nombre/avatar renderizado en el servidor se guardaria y se mostraria a visitantes anonimos. Hacerlo bien exige un menu hidratado por JS contra un endpoint sin cache y una marca de sesion para no consultarlo en cada visita de invitados: complejidad y costo de rendimiento desproporcionados frente al aporte (quien tiene sesion ya llega a su menu con "Publicar" → /admin). Queda un test que asegura que las paginas publicas no muestran datos del usuario.

## Fuera de alcance
Subida de foto, cambio de email, avisos por email, cierre de otras sesiones, eliminacion de cuenta y 2FA (etapa 3 posible), menu en el sitio publico, migraciones (no hacen falta: `profile_photo_path` queda sin uso).

## Archivos
`app/Support/InitialsAvatar.php`, `app/Support/UserRules.php`, `app/Services/AccountSummaryService.php`, `app/Http/Controllers/Backend/AccountController.php`, `app/Listeners/RecordLastLogin.php`, `resources/views/backend/account/edit.blade.php`, `app/Models/User.php`, `config/adminlte.php`, `lang/vendor/adminlte/es/menu.php`, `routes/admin.php`, `app/Http/Controllers/Backend/UserController.php` + `_form`, `MyEntitiesController`, `EventServiceProvider`, `tests/Feature/MyAccountTest.php`.

## Rendimiento y SEO
Backoffice (sin cache publica). Resumen de contenido: 6 consultas agregadas por usuario. Sin cambios en paginas publicas.

## Validacion
`MyAccountTest` y suite completa.
