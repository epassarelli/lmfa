# Noticias en Instagram desde n8n

Estado: implementación autorizada por el usuario el2026-11-11; conexión Meta probada. Usuario autorizó continuar el flujo y, si las verificaciones pasan, probar la misma placa publicada en Facebook.
Fecha: 2026-11-11.

## 1. Contexto y problema

Noticias ya publica en Facebook a las 11:00 de Argentina. El usuario pidió difundir la misma noticia en Instagram, conservando gasto cero, las placas v4 y selección automática de noticias públicas sin publicaciones repetidas por red.

## 2. Actores

Responsable editorial, operador técnico453, n8n, portal y cuenta profesional @mifolkloreargentino.

## 3. Alcance

- Reutilizar app MFA Publicaciones2383825398825453 y cuenta IG17841400312117345 vinculada a Página145931542128207.
- Credencial independiente en n8n; Facebook conserva la suya.
- Publicación de una foto editorial v4 y caption; ledger separado por proveedor/destino.
- Primera prueba con la misma noticia833 publicada en Facebook, previa revisión de pieza/copy y autorización de esa prueba.
- Flujo diario: misma noticia en ambas redes, recuperación de pendientes de Instagram tras éxito de Facebook, sin nuevos envíos a Facebook por fallos de Instagram.
- Fuera de alcance: Eventos, historias, reels, mensajes, comentarios, publicidad, IA paga y cambios de identidad visual.

## 4. Historias

Como responsable quiero que cada noticia activa seleccionada para Facebook se difunda también en Instagram y que cada red conserve su historial propio, sin aprobar cada noticia manualmente.

Como operador quiero distinguir un rechazo confirmado de una publicación de resultado incierto para evitar duplicados al reintentar.

## 5. Reglas

- Público significa el estado canónico publishedVisible del portal; no agregar una condición basada exclusivamente en el campo legacy estado.
- Facebook mantiene política ascendente ID>833. Instagram empieza por833, expresamente autorizada como primera prueba, y consume después las noticias con éxito Facebook.
- Identidad IG: instagram:account:17841400312117345. Un éxito Facebook no es un éxito Instagram.
- El flujo diario encadena IG a la noticia exacta recién confirmada por Facebook. La cola IG de éxitos Facebook queda para recuperación manual supervisada; incertidumbres exigen conciliación y no se reintentan automáticamente.
- Foto JPEG1080x1350 con el mismo diseño aprobado, obtenible por Meta a través de HTTPS sin autenticación del operador.
- Publicar únicamente imágenes generadas por este servicio mediante ruta acotada por hash; no exponer directorios, fuentes, tokens, reservas, captions privados ni archivos arbitrarios.
- La URL pública del JPEG se conserva el tiempo necesario para el procesamiento de Meta. No depender de localhost ni de archivos dentro de Docker accesibles solo localmente.
- Instagram: crear contenedor, comprobar su estado y publicar ese mismo contenedor. Sin reintento ciego de media_publish ante timeout o respuesta incierta; conservar container_id para conciliación.
- Guardar media_id y permalink devuelto por Meta; no fabricar URLs usando el ID numérico como shortcode.
- Caption con título, bajada y llamada al portal, hasta2200 caracteres. No prometer enlace en la bio sin verificarlo; las URLs en captions no deben presentarse como enlaces clickeables.
- Bandera Instagram desactivada por defecto hasta deploy/prueba. Scheduler y credencial Facebook se preservan.

## 6. Criterios de aceptación

- Dado éxito Facebook, cuando se consulta la cola IG, entonces aparece la misma noticia pública si no tiene éxito IG ni envío incierto/en curso.
- Dado éxito IG, cuando se consulta de nuevo, entonces no aparece como candidata IG; el historial Facebook no cambia.
- Dada una publicación incierta, cuando vuelve a correr n8n, entonces no crea otra publicación para esa noticia/destino.
- Dada una noticia retirada del portal después de reservar, cuando se intenta iniciar el envío, entonces se bloquea.
- Dada una URL de placa, cuando Meta la solicita, entonces recibe JPEG válido sin token y solo el archivo editorial previsto.
- Dado un error en Instagram, entonces Facebook no se reenvía y la ejecución conserva evidencia suficiente para recuperación.

## 7. Casos borde

Token caducado/revocado, permisos insuficientes, cuenta desvinculada, ausencia de candidatos, contenedor expirado o en ERROR, caída antes/después de media_publish, callback duplicado, ID ajeno, noticia modificada/retirada y captions demasiado extensos. No truncar el título silenciosamente.

## 8. Impacto técnico/editorial/SEO/performance

Archivos previstos: config/social_publication.php, rutas/controlador/middleware de publicaciones, AutomaticNewsPublication, SocialPublicationLedgerService, SocialPublicationGuard, renderizador, tests Feature y workflows/documentación n8n. Validar si container_id cabe en la evidencia JSON existente antes de proponer una migración; ninguna migración autorizada ni necesaria aún confirmada.

Consultas acotadas y filtros SQL por proveedor/destino; render fuera de transacciones; JPEG local servido sin regenerar por visita. No modificar rutas, SEO ni frontend editorial del portal. Publicar el JPEG requiere una ruta nueva específica; revisar cache y límites de acceso sin cachear APIs autenticadas.

## 9. Dependencias y evidencia comprobada

- Meta: caso de uso Instagram agregado y permisos instagram_basic e instagram_content_publish listos para pruebas; mensajes/comentarios no agregados.
- OAuth autorizado solo para Página/Negocio MFA y cuenta @mifolkloreargentino actual.
- GET Página con instagram_business_account devolvió ID17841400312117345 y username mifolkloreargentino.
- Credencial MFA - Instagram Publicaciones creada en n8n; interfaz mostró Connection tested successfully. No se exportó ni documentó el token.
- Pendiente: caducidad/duración efectiva del token, GET desde n8n a cuenta IG y límite de publicación, entrega pública JPEG, ledger/workflow IG, pruebas y deploy por el usuario.
- Esta conexión no acredita publicación real ni automatización IG activa.

## 10. Validación prevista

Tests Feature con DatabaseTransactions en mfa_test para independencia por red, aislamiento de destino, cola basada en éxito Facebook, estados inciertos, callbacks/idempotencia, rechazo de contenido retirado y ruta JPEG. Http::fake para Meta. Prueba privada del flujo n8n; luego UNA publicación real revisable y autorización específica. Verificar permalink, foto/caption, ledger y exclusión posterior antes de activar programación IG.

## Tareas

### Encadenamiento autorizado2026-11-11

Usuario pidió activar ambas redes a partir de las11 y autorizó «hazlo entonces». Implementar IG en el mismo workflow después de callback Facebook success, usando exactamente el content_id confirmado, sin consultar otra candidata IG. Un resultado fallido/incierto Facebook no inicia Instagram. Conservar cron11:00 Argentina y un único scheduler; ningún envío real adicional para probar. Validar con simulador éxito, fallo Facebook y fallo Instagram antes de publicar la nueva versión de n8n. El workflow IG separado queda manual/bloqueado para recuperación supervisada; un resultado incierto no habilita reintentos. Cambios: generador/export combinado y documentación, sin cambios PHP ni deploy Hostinger.

- [x] Identificar cuenta y acceso a app con Edu Passa.
- [x] Agregar caso de uso y permisos de contenido autorizados.
- [x] Autorizar cuenta MFA y comprobar vínculo por API.
- [x] Guardar credencial separada en n8n y comprobar conexión.
- [x] Verificar duración del token y lectura IG desde n8n.
- [x] Implementar destinos independientes, JPEG público y evidencia de contenedor.
- [x] Adaptar workflow inactivo y probar con simulaciones.
- [x] Validar regresión Facebook y documentar release.
- [x] Deploy humano y prueba real autorizada de833.
- [x] Activación diaria Instagram encadenada a Facebook11:00 Argentina.

Evidencia2026-11-11:61 tests/298 assertions pasan en mfa_test; siete escenarios n8n simulados sin llamadas Meta. Release/documentación en resources/integrations/n8n/INSTAGRAM.md. Consulta real con credencial inicial devolvió190/463, caducada; renovación de larga duración detenida en solicitud Meta de reingreso de contraseña (handoff al usuario). Aún sin deploy ni publicación IG. No se ejecutaron migraciones ni se alteró la programación Facebook.

Actualización: reingreso de contraseña completado por el usuario; token ampliado válido/Nunca según Meta, token de Página derivado y guardado solo en credencial IG de n8n. GET real desde n8n HTTP200 confirmó ID17841400312117345, username mifolkloreargentino y websitehttps://mifolkloreargentino.com. Credencial resuelta; pendiente únicamente deploy humano y prueba/activación.

Prueba productiva2026-11-11: preflight/deploy del usuario verificados por GET;833 publicada una sola vez en Instagram, media17964203609999673, permalinkhttps://www.instagram.com/p/Dfm52_xlrID/, ledger target2/attempt3 success/1envío. Caption/IMAGE/permalink y exclusión posterior de cola confirmados; Facebook mantiene success/1envío. JPEG v4 revisado visualmente. Workflow de prueba reblockeado; programación Instagram todavía inactiva. Guía/evidencia en INSTAGRAM.md.


Resultado del encadenamiento: Activación2026-11-11 autorizada por el usuario y verificada: MfaNewsDailyV401, Facebook + Instagram diario11:00 Argentina, único cron0 0 11 * * * / America/Argentina/Buenos_Aires. Facebook conserva los parámetros de sus14 nodos; IG empieza después del callback success con exactamente el content_id confirmado, sin consultar otra candidata ni tener scheduler independiente. Cuatro escenarios n8n simulados pasaron: éxito conjunto, rechazo/incertidumbre FB sin IG y error IG sin duplicar FB. Versión publicada=activa y log Activated workflow comprobados tras reinicio. Sin publicaciones reales adicionales, migraciones ni deploy adicional del portal. Borradores IG separados/prueba833 siguen bloqueados e inactivos. PC/Docker/n8n deben estar funcionando; incertidumbres requieren conciliación.
