# Cambio: hide-social-login-buttons

Estado: APROBADA por el usuario en el chat (2026-10-12) e implementada en local.

## Problema
Los botones "Ingresar/Registrarme con Google y Facebook" no funcionan y dan una pésima experiencia. Se prefiere por ahora ingreso y registro con email y contraseña.

## Alcance
- Ocultar los botones y el separador en `auth/login` y `auth/register` detrás de `services.social_login.enabled` (env `SOCIAL_LOGIN_ENABLED`, apagado por defecto).
- Conservar las rutas `auth/google` y `auth/facebook` (y callbacks) y el controlador para reactivarlos sin cambios de código cuando se arregle la integración.

## Fuera de alcance
Arreglar el login social, borrar usuarios o rutas, tocar los enlaces a redes sociales del sitio público.

## Validacion
`SocialLoginButtonsTest` (4 casos) + `AuthenticationTest` y `RegistrationTest`.

## Despliegue
No definir `SOCIAL_LOGIN_ENABLED` en producción y limpiar la caché de vistas (`view:clear`) tras desplegar.
