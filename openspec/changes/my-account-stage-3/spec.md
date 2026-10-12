# Cambio: my-account-stage-3

Estado: APROBADA por el usuario en el chat (2026-10-13: "sigamos con etapa 3"). Continua `my-account` (etapas 1 y 2). Implementada en local, sin migraciones.

## Alcance
1. **Avisos por email de las solicitudes de ficha.** Al resolverse una solicitud (aprobada o rechazada, por un administrador o por el responsable de la ficha), la persona que la hizo recibe un correo con el resultado, el rol otorgado o el motivo y el enlace a Mis fichas. Es un aviso transaccional (sin opt-in). Best-effort: si el correo falla se registra y la resolucion no se afecta. No se envia a cuentas anonimizadas (`.invalid`).
2. **Sesiones.** En "Mis datos": lista de sesiones activas (dispositivo aproximado, ultima actividad, marca de "esta sesion") y boton **Cerrar las otras sesiones** (pide la contraseña actual; tambien rota el `remember_token`). Cambiar la contraseña tambien cierra las otras sesiones. Usa la tabla `sessions` (`SESSION_DRIVER=database`, que es el valor por defecto del proyecto); con otro driver la tarjeta no se muestra.
3. **Eliminar mi cuenta.** Anonimizacion en el lugar, el mismo criterio que ya usa el flujo de eliminacion de datos de Meta (un borrado real arrastraria en cascada solicitudes, membresias y notificaciones y dejaria la autoria de contenido en NULL o apuntando a un usuario inexistente). Requiere contraseña actual y escribir `ELIMINAR`; limitado por `throttle`; **no disponible para administradores** (los gestiona otro administrador).
   - Se borra: roles y permisos, membresias de fichas, solicitudes pendientes (se cancelan), suscripcion al newsletter, mensajes de contacto enviados con su cuenta o su email, cuentas sociales, tokens de API, sesiones y notificaciones internas.
   - Se anonimiza: nombre ("Usuario eliminado N"), email (`deleted-user-N-xxxx@mifolkloreargentino.invalid`), telefono, contraseña aleatoria, `google_id`/`facebook_id`, foto, ultimo ingreso.
   - Se conserva: el contenido que cargo (discos, noticias, eventos, etc.) queda sin su nombre. Las fichas que administraba como unico responsable quedan sin responsable (se le avisa antes).
   - Al terminar se cierra la sesion.
- Se extrae `AccountEraser::scrubIdentity()` y el flujo de Meta lo reutiliza (misma anonimizacion de identidad, sin cambiar su comportamiento).

## Fuera de alcance
Autenticacion en dos pasos (las columnas existen pero no hay implementacion: proyecto aparte), avisos por email a responsables cuando llega una solicitud nueva, exportacion de datos, preferencias de notificacion.

## Archivos
`app/Mail/EntityClaimResolvedMail.php` + vista, `app/Services/EntityClaimService.php`, `app/Services/UserSessionService.php`, `app/Support/DeviceLabel.php`, `app/Services/AccountEraser.php`, `AccountController`, rutas, `backend/account/edit.blade.php`, `LegalController` (reuso), `tests/Feature/MyAccountStage3Test.php`.

## Seguridad
Contraseña actual para cerrar sesiones y eliminar la cuenta; frase de confirmacion; `throttle`; solo se opera sobre el usuario autenticado; el administrador no puede autoeliminarse; correos sin datos sensibles y con el motivo escapado.

## Validacion
`MyAccountStage3Test`, `MetaDataDeletionFlowTest` (regresion del flujo reutilizado) y suite completa.

## Resultado (implementado en local, 2026-10-13)
- `MyAccountStage3Test` (18 casos); `MetaDataDeletionFlowTest` (10) sigue pasando con el reuso de `scrubIdentity`; suite en base limpia 634 pasan, 0 fallan; en `mfa`, solo los 13 previos.
- Nota de pruebas: dentro de un mismo test el manejador de sesion de base de datos se reutiliza entre peticiones y no inserta la fila de la segunda; las aserciones de sesiones verifican por eso que las otras sesiones desaparecen y la ajena se conserva (comportamiento real verificado a mano en el checklist).
