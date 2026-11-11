# Noticias Instagram — release2026-11-11

## Estado

Implementado en local, pendiente de deploy del usuario y primera publicación real. Sin migraciones ni dependencias nuevas. Instagram desactivado por defecto. No se modificó el workflow activo de Facebook.

- App2383825398825453; Página145931542128207; cuenta IG17841400312117345 (@mifolkloreargentino).
- Credencial independiente `MFA - Instagram Publicaciones` (`qaekI4MYP9zPCJe7`), tipo Facebook Graph API. Nunca exportar el valor del token.
- Workflow `MfaInstagramNews01`, fuente `daily/instagram-news.json`, manual, inactivo y bloqueado. Permite únicamente noticia833 en modo prueba. No incluye trigger diario hasta validar publicación real.
- La API usa el mismo PAT limitado del operador453. Prefijo nuevo `/api/v1/social-publications/instagram` y estados independientes de Facebook.
- Instagram toma noticias públicas con éxito Facebook confirmado desde833, orden ascendente; no consume la próxima candidata de Facebook ni vuelve a publicar en esa red.

## Deploy humano

Revisar diff/CI y subir el código por el flujo habitual del proyecto. No ejecutar migrate: este release no agrega migraciones.

Mantener la configuración existente y agregar al `.env` productivo:

```dotenv
SOCIAL_PUBLICATION_INSTAGRAM_ENABLED=true
SOCIAL_PUBLICATION_INSTAGRAM_ACCOUNT_ID=17841400312117345
```

Después, desde la raíz de la aplicación en Hostinger:

```bash
php artisan config:cache
php artisan route:clear
php scripts/social-publication-preflight.php
```

Habilitar la API no activa el workflow ni publica. Mantener bloqueado n8n hasta verificar token vigente, GET autenticado de candidatos y render833.

## Primera prueba autorizada

El usuario autorizó probar la misma placa de Facebook si todas las verificaciones pasan. Usar833, sin pedir otro consentimiento idéntico. La placa se genera con el diseño v4 aprobado, convertido a JPEG1080x1350 para IG; se conserva el texto de título/bajada/URL del portal. No prometer enlace en bio sin comprobarlo.

1. Verificar desde n8n identidad IG y cuota con la credencial nueva; confirmar vigencia en el depurador Meta sin mostrar secretos.
2. GET candidatos de Instagram debe devolver833 como primera, sin escribir datos.
3. Render generado por reserva: comprobar JPEG real, SHA256, dimensiones y GET público HTTPS. La ruta por hash solo entrega JPEGs generados en storage privado; no publica fuentes ni archivos arbitrarios.
4. Ejecutar una sola vez el workflow con `allow_publish=true`, `test_news_id=833`, siempre inactivo y sin horario. Meta POST no tiene retry.
5. Crear contenedor, consultar FINISHED, registrar container_id en portal con nueva verificación de noticia y hacer UN media_publish.
6. Consultar media_id/permalink, registrar success y verificar exclusión de cola. Ante timeout/error, guardar uncertain y conciliar el contenedor; no repetir publicación. Si se observa media_id pero falla permalink, queda en la evidencia del intento para recuperar el resultado.
7. Volver a bloquear la prueba al terminar. La programación diaria se define/activa después del resultado verificado, conservando Facebook11:00.

## Evidencia local

- 61 tests Feature,298 assertions, sin fallos: Instagram(8), AutomaticNewsPublication(14), SocialPublicationLedger(34), SocialPublicationOperatorCommand(5). Base existente mfa_test y DatabaseTransactions; conectores simulados. JPEG real validado, incluyendo ruta pública y digest.
- Siete escenarios del workflow real de n8n contra simulador privado: éxito, cola vacía, rechazo de creación, error de publicación, fallo de permalink, espera de procesamiento y espera agotada. Éxitos con un solo media_publish y callback success; fallos inciertos sin reenvíos; cola vacía sin POST; seis consultas como máximo ante espera agotada.
- El primer pase detectó un error de sintaxis en la validación de permalink; corregido y repetidos los tres escenarios afectados con callback correcto.
- Token corto inicial caducado190/463, reemplazado después del reingreso de contraseña realizado por el usuario. Meta mostró válido/Nunca para el token ampliado (acceso a datos aproximadamente3 meses); se derivó token de Página y guardó exclusivamente en la credencial IG. Conexión n8n exitosa y GET real ID/username/website HTTP200:17841400312117345, mifolkloreargentino, https://mifolkloreargentino.com. Un primer diagnóstico solicitaba account_type, no admitido en esta ruta Facebook Login; retirado ese campo, lectura correcta. Sin publicación real todavía.

Simulador: `mock-instagram-protocol.cjs`, solo escucha127.0.0.1 dentro del contenedor, sin credenciales. Generación del borrador: `node resources/integrations/n8n/build-instagram-workflow.cjs`. El generador siempre produce inactivo/bloqueado, nunca publica ni activa.

## Límites operativos

Un rechazo remoto se trata conservadoramente como incierto en este workflow; requiere conciliación antes de reenviar. Si falla el callback, este sí admite reintento idempotente. Si una caída impide confirmar el contenedor, la reserva processing vence a uncertain; no volver a activar manualmente por ensayo.

El JPEG se guarda una vez fuera de la transacción y luego se sirve sin regenerarlo. La cola filtra en SQL con límite/paginación; no se agregó consulta por noticia. API autenticada no-store, asset público cacheado/noindex. Sin cambios de SEO público ni mediciones atribuibles a producción. Retención/limpieza de placas queda pendiente de una política futura.
