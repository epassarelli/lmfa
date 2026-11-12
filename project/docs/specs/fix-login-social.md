# Cambio: reparar login social Google y Facebook

Estado: needs_review. Implementación local validada; commit y merge a dev autorizados por el usuario el 2026-11-12. La publicación general de Facebook sigue pendiente de Meta.

## Objetivo y alcance aprobado
Recuperar Google primero y Facebook después, preservando URLs, cuentas y validación de state de Socialite. Excluir auth/* de la caché pública; dar a Google un callback por defecto derivado de APP_URL; permitir mostrar cada proveedor por separado manteniendo compatibilidad con SOCIAL_LOGIN_ENABLED.

Archivos: app/Support/ResponseCache/PublicPagesCacheProfile.php, config/services.php, resources/views/auth/{login,register}.blade.php, .env.example, tests/Feature/SocialLogin{ResponseCache,ProviderButtons}Test.php y documentación de estado.

Sin migraciones, SQL modificatorio, nuevas dependencias, cambios en credenciales ni reglas de asociación de cuentas. No se modifica SocialiteController.

## Reglas y criterios de aceptación
- Dado un intento OAuth, cuando se solicita redirect o callback de Google/Facebook, entonces no se cachea su respuesta. Dos intentos generan estados distintos y cada estado coincide con su sesión. Se mantiene la validación de state.
- Dada una página pública anónima normal, cuando se solicita, entonces conserva su elegibilidad de caché.
- Dada configuración Google sin override, entonces el callback es APP_URL + /auth/google/callback; GOOGLE_REDIRECT_URI y GOOGLE_REDIRECT_URL mantienen precedencia.
- Dados GOOGLE_LOGIN_ENABLED y FACEBOOK_LOGIN_ENABLED, cada uno controla únicamente su botón en login/registro. Si no se define, usa SOCIAL_LOGIN_ENABLED. Los campos de email/contraseña se conservan.
- Facebook no se habilita públicamente hasta resolver publicación/verificación Meta y validar una cuenta sin rol de la app.

## Diagnóstico y evidencia comprobada
- La rama antigua fix/login-social contenía un arreglo de septiembre no integrado. El código actual y el perfil productivo no excluían auth/*.
- En Chrome se observó el mismo state entre intentos Facebook. El log productivo se consultó con lectura acotada de sus últimos 2 MB: tres errores con message vacío, sin clase de excepción. InvalidStateException no quedó confirmada por ese log.
- Google: app existente en producción para usuarios externos, callback .com correcto. Facebook: app existente Login MFA, distinta de la app de publicaciones automáticas.
- Facebook devolvía URL bloqueada. Se agregó y guardó el callback canónico .com, conservando los anteriores y HTTPS/modo estricto. Se agregó Website con la URL pública del sitio.
- Observación Meta 7.d por ausencia de plataforma: el usuario autorizó expresamente enviar la corrección. Tras el envío y recarga, Meta informó que no había acciones requeridas y desapareció la restricción de API.

## Intervención productiva anterior, ya ejecutada con autorización puntual
El usuario autorizó respaldar el perfil, agregar únicamente auth/* y purgar responsecache. Se guardó respaldo fuera de public_html y se verificó SHA-256 contra el original antes de editar. PHP no detectó errores de sintaxis; git diff remoto mostró una única línea agregada. responsecache:clear confirmó limpieza.

Después del cambio y purga, Facebook volvió autenticado al panel desde sesión del portal cerrada. Se cerró esa sesión, el usuario completó personalmente la reautenticación Google y Google también volvió autenticado al panel. Son pruebas con cuentas disponibles del operador, no acreditan registro de cuentas nuevas ni acceso general Facebook.

El hotfix remoto usó comillas dobles en la entrada auth/*; el código local usa comillas simples y agrega un comentario explicativo. Son equivalentes funcionalmente.

## Regla vigente de entrega
El usuario aclaró posteriormente: todo cambio de código/configuración se realiza en local y el despliegue lo hace manualmente él. Esa instrucción reemplaza cualquier autorización de despliegue anterior. No se ejecutarán más escrituras en producción, ni push ni merge a main.

La habilitación independiente de botones NO se desplegó. No se modificó .env productivo para habilitar Google. Este cambio versiona tanto el hotfix de caché como esa habilitación preparada en local.

## Validación local
- SocialLoginResponseCacheTest: 7 tests / 23 assertions. Redirects reales de Socialite con configuración ficticia, caché array activa, estados distintos y callbacks excluidos; sin llamadas externas ni BD.
- SocialLoginProviderButtonsTest: 5 tests / 55 assertions. Render de login/registro: apagado por defecto, switch global compatible, Google solo, Facebook solo y override false que prevalece sobre el global. Conserva campos email/contraseña.
- Corridas Docker contra la aplicación compartida: perfil/config inicial equivalentes a los del worktree; prueba de botones fuerza el finder de vistas del worktree. No se ejecutó suite completa ni prueba standalone del worktree.
- diff --check sin errores. Sin medición de performance productiva. No consultas ni JS nuevos; solo OAuth deja de cachearse. URLs y SEO sin cambios.

## Despliegue manual previsto para el usuario
1. Integrar este cambio en el release habitual; no sobrescribir cambios ajenos ni revertir el hotfix remoto de auth/*.
2. Para mostrar solo Google, configurar SOCIAL_LOGIN_ENABLED=false, GOOGLE_LOGIN_ENABLED=true y FACEBOOK_LOGIN_ENABLED=false. No cambiar client IDs, secretos o callbacks válidos.
3. Tras desplegar, ejecutar config:clear, view:clear y responsecache:clear como usuario del sitio, según el procedimiento de despliegue vigente.
4. Verificar que login y registro muestran Google y ocultan Facebook; probar Google desde sesión del portal cerrada y comprobar acceso al panel.
5. Habilitar Facebook solo después de resolver Meta y validar acceso general. No necesita migraciones ni compilación frontend por este cambio.

## Pendientes externos y límites
Meta bloquea Publicar por URL de eliminación de datos, verificación del negocio y cuestionario de tratamiento de datos; este último también exige un portfolio verificado. El portfolio existente del proyecto figura sin verificar. El usuario indicó que es un proyecto personal a su nombre, sin categoría empresarial que represente su situación. No se seleccionó ni envió una categoría ficticia. La app sigue Sin publicar; no se prometió resolver esa verificación creando otra app.

El .env local tiene callbacks Google duplicados y distintos de APP_URL=mfa.localhost; no se modificó. La prueba interactiva realizada fue productiva. El log productivo de 8,87 GiB queda como observación operativa fuera de alcance: revisar rotación por separado, sin truncarlo ni modificarlo aquí.
