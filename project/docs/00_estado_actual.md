# 00 - Estado Actual del Proyecto


### Implementado en local 2026-10-13: newsletter, Fase 2 (rama `feature/newsletter`)

Spec `newsletter-editorial-control`. Ediciones con borrador automático semanal (nunca envía), selección de contenido, vista previa, envío de prueba, aprobación con contenido congelado, envío inmediato o programado y registro de envíos. Comandos `newsletter:build-draft` (lunes 07:00) y `newsletter:dispatch-scheduled` (cada 15 min, solo aprobadas). 1 migración nueva más (aplicada solo en `mfa_test`; **pendiente en `mfa`**). mfa_test: 704 passed, 2 skipped. Pendiente: Fase 3 (aperturas/clics, rebotes, plantilla con imágenes) y verificación de SPF/DKIM/DMARC.

### Implementado en local 2026-10-13: newsletter, Fase 1 (rama `feature/newsletter`)

Spec `newsletter-safe-base`. Doble confirmación, constancia de consentimiento, protección de abuso, baja correcta con `List-Unsubscribe`, envío solo a confirmados, registro de envíos, envío automático retirado del scheduler y administración de suscriptores. Corrige además un acceso indebido al listado de suscriptores (cualquier usuario con sesión). 2 migraciones nuevas (aplicadas solo en `mfa_test`; **pendientes en `mfa`**). mfa_test: 695 passed, 2 skipped. Pendiente: Fase 2 (ediciones con vista previa y aprobación) y Fase 3 (métricas, rebotes, plantilla); verificar SPF/DKIM/DMARC y el remitente en producción.

### Implementado en local 2026-10-12: página pública «Cómo publicar»

Spec `how-to-publish-guide`. `/como-publicar` (controlador `PublishGuideController`, vista `frontend/guia/como-publicar`), enlazada desde menú, pie, registro, Mi panel, Mis fichas y la ayuda de los formularios; en el sitemap estático. mfa_test: 678 passed, 2 skipped. Sin migraciones. Pendiente: revisión editorial del texto de derechos y de cuentas verificadas; no se encontró la página estática original.

### Implementado en local 2026-10-12: ayuda contextual en el backoffice

Spec `backoffice-contextual-help`. Componentes `x-backend.form-help|list-help|image-hint|seo-box` y textos en `config/backoffice_help.php`, aplicados a los ABM de contenido y a Mis fichas. mfa_test: 674 passed, 2 skipped. Sin migraciones. Ajuste obligatorio de imágenes (recorte/reducción en el navegador con Cropper.js y original acotado en `ImageUploadService`). Pendiente: validación visual con varios roles, prueba manual del editor de imágenes (JS sin test automático) y revisión editorial de los textos.

### Implementado en local 2026-10-12: registro sin rol, listados acotados, alta directa limitada y Mi panel

Spec `registration-role-and-scoping`. Registro sin rol por defecto (decisión); listados acotados para no administradores; alta directa de artistas/festivales solo admin y verificado con el flag activo; dashboard de no administradores reemplazado por Mi panel. mfa_test: 661 passed, 2 skipped. Sin migraciones. Pendiente: validación visual y, en producción, los 6 usuarios sin rol quedan sin rol.

### Implementado en local 2026-10-12: tablero "Requiere tu atención" e insignias del sidebar

Spec `admin-attention-dashboard`. `AdminAttentionService` (caché 30 s + invalidación por hooks de modelo), `ModerationQueue`, `PendingBadgeFilter`, nuevo `dashboard.blade.php`, festivales en el Centro de Moderación. Validado en `mfa_test`: 655 passed, 2 skipped. No se corrió `mfa` completa en este ciclo. Sin migraciones. Pendiente: validación visual en navegador.

### Implementado en local 2026-10-13: selección acotada a lo propio (con el flag activo)

- Spec `member-selection-scoping` (decisiones del usuario: 1 sí, 2 listados solo con lo suyo, 3 festivales con la misma lógica). Medición previa con el flag encendido: un colaborador con una ficha veía y creaba Discos/Canciones de cualquier artista, veía todos los Intérpretes y podía crear Noticias/Eventos sin artistas propios. `MemberSelectionScopingTest` (12 casos); suite en base limpia 649 pasan, 0 fallan; en `mfa` solo los 13 previos. Los tests de contacto ya no dependen de que la tabla esté vacía (se vacía dentro de la transacción). **Observación abierta:** con el flag activo un colaborador con una ficha todavía puede crear artistas/festivales nuevos de forma directa (permiso `create interprete/festival`), salteando la solicitud de alta; no se tocó por no estar pedido.

### Ejecutado 2026-10-13: mensajes de contacto demo y decisión sobre Clasificados

- Seeder local `ContactMessagesDemoSeeder` (solo local/testing, idempotente, prefijo `[DEMO]` en el nombre y dominio `example.test`): 34 mensajes en `mfa` (18 nuevos, 9 leídos, 6 respondidos, 1 spam; incluye HTML para ver el escape, un mensaje largo, uno de 40 días sin responder, dos del mismo remitente y uno de una cuenta registrada; más de una página). Limpieza: `DELETE FROM contact_messages WHERE nombre LIKE '[DEMO]%';`. `ContactMessagesDemoSeederTest` (3). **Avisos Clasificados sigue visible para todos** (decisión del usuario).

### Implementado en local 2026-10-13: Mis datos, etapa 3 (avisos, sesiones, eliminación de cuenta)

- Spec `my-account-stage-3`. Sin migraciones. `MyAccountStage3Test` (18 casos); suite en base limpia 634 pasan, 0 fallan; en `mfa` solo los 13 previos. Avisos por email de solicitudes (best-effort), sesiones activas y cierre de las otras (tabla `sessions`, requiere `SESSION_DRIVER=database`), eliminación por anonimización (los administradores no pueden autoeliminarse). **Para verificar en producción:** `SESSION_DRIVER` real y que `MAIL_*` envíe. Fuera de alcance: 2FA (columnas sin implementación) y avisos a responsables cuando llega una solicitud nueva. No validado en navegador.

### Implementado en local 2026-10-13: formulario de contacto rehecho y visible

- Spec `contact-form-overhaul`. Diagnóstico: la tabla `contact_messages` no existía (500 al enviar), errores de correo tragados, captcha estático 3+4, sin límites, autorrespondedor a cualquier dirección, sin bandeja, clases Bootstrap en el front y sin enlaces visibles. Ahora: migración defensiva `2026_10_13_100000` (aplicada en `mfa_test`; aplicada también en `mfa` el 2026-10-13), formulario Tailwind con defensas sin captcha, bandeja en el backoffice, enlaces en menú y footer, `public/build` recompilado. `ContactFormTest` (25) + 3 tests de `SeoInfrastructureTest` pasan a otra página de muestra (el canonical de `/contacto` ahora es limpio). Suite en base limpia: 616 pasan, 0 fallan; en `mfa` quedan solo los 13 previos por datos reales. **En producción:** verificar `DESCRIBE contact_messages` antes de migrar (puede existir como tabla heredada) y probar el envío real de correo. No validado en navegador.

### Implementado en local 2026-10-12: menú de usuario con avatar y página "Mis datos"

- Spec `my-account` (etapas 1 y 2). Sin migraciones. `MyAccountTest` (18 casos); suite en base limpia 591 pasan, 0 fallan. Avatar de iniciales (SVG data URI, sin subida), `/admin/mi-cuenta` (nombre, teléfono, contraseña; email solo lectura), resumen de fichas/solicitudes, contenido propio y newsletter; `last_login_at` en cada login; reglas compartidas con el CRUD de Usuarios (contraseña mínima 8, teléfono). **Menú en el sitio público: no se implementó** — las páginas públicas van a caché de página completa decidida antes de iniciar sesión (`PublicPagesCacheProfile`) y un menú personalizado se serviría a anónimos; haría falta un menú hidratado por JS con endpoint sin caché y marca de sesión (complejidad y costo desproporcionados). **Observación:** `lang/vendor/adminlte` en la raíz no se carga (el proyecto usa `resources/lang`); el override efectivo está en `resources/lang/vendor/adminlte/es/menu.php`. No validado en navegador.

### Ejecutado 2026-10-12: se retira el flujo "Publicar / Contribuir"

- Spec `remove-contribuir-flow` (pedido del usuario: una sola puerta, Publicar → login → /admin). Con la confirmación de que producción no tiene contribuciones se agregó la migración `2026_10_12_140000` (aplicada en `mfa` y `mfa_test`) que elimina la tabla `contributions` y `users.points/rank`; respaldo de la fila local de prueba fuera del repo. Enlace "Solicitá administrar su ficha" en artista y festival. Con el acceso por fichas activo, los no administradores que entran a `/admin` van a Mis fichas. Tests: `ContributionFlowRemovedTest` (7), `ImageSourceResolverSecurityTest` (4, cobertura directa de SSRF que antes dependía de la aprobación de contribuciones); suite en base limpia 568 pasan, 0 fallan. **Antes del despliegue:** resolver contribuciones pendientes en producción y exportar la tabla. Pendiente de decisión: enlace "¿Sos este artista? Solicitá administrar tu ficha" en páginas públicas, y si Avisos Clasificados sigue visible.

### Ajuste 2026-10-12 tras probar el sidebar: Copa, Enciclopedia y Peñas solo administrador

- Detrás del flag, como el resto. `BackofficeAccessByEntitiesTest` (20 casos). Un colaborador sin fichas ve solo Mis fichas y Avisos Clasificados. **Pendiente de decisión:** cómo se integra el flujo público "Publicar" (`/admin/contribuir`) con Mis fichas y si Avisos Clasificados sigue visible.

### Ejecutado 2026-10-12: cuentas de validación locales, flag local y checklist de producción

- Seeder `AccessValidationAccountsSeeder` (solo local/testing): 4 cuentas `val.*@example.test` con claves aleatorias no mostradas + artista y festival DEMO sin publicar; ejecutado en la base local (+4 usuarios, +1 interprete, +1 festival, +2 organizaciones, +2 membresías; eventos y noticias sin cambios). `BACKOFFICE_ACCESS_BY_ENTITIES=true` solo en el `.env` local (no versionado). Con el flag encendido por entorno, 18 tests antiguos cambian de resultado por diseño (colaboradores sin fichas ahora son redirigidos); `TestCase` apaga el flag para que la suite no dependa del entorno.
- Checklist detallado y verificado contra el estado real: `project/docs/releases/checklist-despliegue-dev-2026-10-12.md` (31 commits sin push, 6 migraciones, sin cambios de dependencias ni de build). **Nada se ejecutó en producción.**

### Ejecutado 2026-10-12: base `mfa_test` creada, migrada y sembrada

- A pedido del usuario se creó `mfa_test` (script revisable), se migró (87) y se sembró con `TestingReferenceDataSeeder`; `mfa` sin cambios. Suite en `mfa_test`: **557 pasan, 0 fallan** (2 omitidos previos); en `mfa` siguen 13 fallos por datos reales. Se corrigieron fixtures con ids fijos y `PeniaProfileFactory`; se agregó el paso de siembra al CI (`.github/workflows/ci.yml`). **Sin verificar:** ejecución real del workflow en GitHub (requiere push). Los tests ya no dependen de variables de entorno del flag de acceso por fichas (`TestCase` lo apaga).

### Implementado en local 2026-10-12: Noticias, Mitos y Comidas en el acceso por fichas (opción C)

- Spec `gate-editorial-sections-by-entities`. `BackofficeAccessByEntitiesTest` ampliado (16 casos); suite completa 546 pasan, 13 fallos previos por datos reales. Sigue detrás del flag (apagado): con el flag activo, colaboradores sin ficha ni rol `verificado` pierden Noticias, Mitos y Comidas (datos locales: casi no las usan; **verificar en producción antes de activar**). Pendiente: activar el flag tras validación manual, checklist de despliegue, `mfa_test`.

### Implementado en local 2026-10-12: login y registro sin botones sociales

- Spec `hide-social-login-buttons`: los botones de Google y Facebook quedan ocultos (flag `SOCIAL_LOGIN_ENABLED`, apagado por defecto). `SocialLoginButtonsTest` (4 casos). **Al desplegar:** no definir el flag en producción y correr `view:clear`. Las rutas y el controlador de login social no se tocaron.

### Implementado en local 2026-10-12: eventos de festivales (Etapa 3)

- Spec `entity-member-content-access-festival-events`. `MemberFestivalEventsTest` (10 casos); Etapa 2 intacta (12). Sin migraciones. **No validado:** navegador. Cierra las etapas de contenido de fichas. **Pendiente para decidir juntos:** Mitos/Comidas/Noticias de colaboradores y activación del flag `BACKOFFICE_ACCESS_BY_ENTITIES`; `mfa_test` sin crear.

### Implementado en local 2026-10-12: revisión de solicitudes delegada al owner

- Spec `owner-delegated-claim-review`. `DelegatedClaimReviewTest` (9 casos). Sin migraciones. **No validado:** navegador (tarjeta "Solicitudes para mis fichas" en Mis fichas). Pendiente: Mitos/Comidas de colaboradores, activación del flag de acceso por fichas, Etapa 3, `mfa_test`.

### Implementado en local 2026-10-12: migración de roles aplicada y cierre de observaciones previas

- Migración `2026_10_12_100000` **aplicada en la base local por pedido del usuario**: permiso `publish contents`, rol `verificado`, los 2 usuarios de prueba con rol prensa pasaron a colaborador (434) y el rol prensa se eliminó; usuarios totales sin cambios (445). Falta aplicarla en otros entornos.
- `canciones/store-ajax` ahora autoriza (4 tests nuevos, `CancionStoreAjaxAuthorizationTest`); dos tests dejaron de depender de `provincias` sembradas (posible causa de fallo en CI; **no verificado en una base limpia**, `mfa_test` sigue sin crearse). Suite completa 517 pasan, 13 fallos previos por datos reales.

### Implementado en local 2026-10-12: camino de confianza, baja del rol prensa y correcciones de permisos

- Spec `roles-trust-ladder-and-cleanup`; modelo en `project/docs/permisos_y_roles.md`. `TrustedPublishingTest` (10 casos); suite completa 513 pasan, 13 fallos previos por datos reales. **Migración `2026_10_12_100000` preparada, NO ejecutada:** crea permiso `publish contents` y rol `verificado`, pasa a colaborador a los usuarios con rol prensa y elimina ese rol (requiere confirmación). Hasta ejecutarla, nadie salvo el administrador publica y los 2 usuarios de prueba con rol prensa quedan sin permisos de creación. Pendiente: colaborador/prensa resuelto; siguen Mitos/Comidas, flag, Etapa 3, delegar aprobaciones al owner y observaciones previas.

### Implementado en local 2026-10-11: rol sugerido al aprobar (alternativa B)

- Spec `claim-approval-role-suggestion`. `ApprovalRoleSuggestionTest` (7 casos). **No validado:** navegador (alertas en la pantalla de detalle). Resuelto el pendiente 1 de la lista; siguen: colaborador vs prensa, Mitos/Comidas, flag, Etapa 3 y observaciones.

### Implementado en local 2026-10-11: eventos y noticias de artistas (Etapa 2)

- Spec `entity-member-content-access-events-news`. Regla multi-artista aprobada, todo a revisión. `MemberEventsNewsAccessTest` (12 casos); suite completa 496 pasan, 13 fallos previos por datos reales. Endurecimiento de `EventService`/`NewsService`: no publican si el usuario no puede publicar. **No validado:** navegador con cuenta real. **Pendientes que debo recordarte (memoria del proyecto):** rol sugerido por relación al aprobar, diferencia colaborador vs prensa, Mitos/Comidas de colaboradores, activación del flag, Etapa 3 y observaciones previas.

### Implementado en local 2026-10-11: solicitud de alta de ficha

- Spec `entity-creation-requests`. Migración `2026_10_11_120000` aplicada por pedido del usuario en la base local (las 3 solicitudes existentes quedaron como `link`; `entity_id` ahora nullable). `EntityCreationRequestTest` (8 casos); suite completa 484 pasan, 13 fallos previos por datos reales. **No validado:** navegador (formulario y aviso de similares por JS). Falta aplicar la migración en otros entornos. Pendiente: Etapa 2 y recordatorio Mitos/Comidas.

### Implementado en local 2026-10-11: acceso al backoffice según fichas (flag apagado)

- Spec `backoffice-access-by-entities`. Flag `BACKOFFICE_ACCESS_BY_ENTITIES` (por defecto false): restringe Intérpretes, Discos, Canciones, Festivales y Eventos a quienes administran fichas; el resto (Mitos, Comidas, Noticias) queda igual por decisión del usuario. `BackofficeAccessByEntitiesTest` (11 casos); suite completa 476 pasan, 13 fallos previos por datos reales. **No validado:** navegador con cuentas reales. **Recordatorio pendiente al usuario:** decidir qué pasa con Mitos/Comidas/Noticias de los colaboradores (memoria del proyecto). Siguiente: solicitud de alta de ficha y Etapa 2.

### Implementado en local 2026-10-11: contenido del artista para quien administra su ficha (Etapa 1)

- Spec `entity-member-content-access` (decisiones del usuario: letras publicables directo, carga directa, trabajo en dev). Miembros owner/editor activos gestionan discos y canciones de sus artistas; test `MemberContentAccessTest` (9 casos); suite completa 465 pasan y 13 fallos previos por datos reales. **No validado:** comportamiento en navegador con cuenta real sin roles. Pendiente: Etapa 2 (eventos y noticias), Etapa 3 (festivales). Observación: `canciones/store-ajax` sin policy (previa). Sin push ni despliegue.

### Pendiente de ejecución humana 2026-10-11: base aislada de tests

- `.env.testing` ahora apunta a `mfa_test` (antes `mfa`, base con datos reales: causa de 13 fallos de la suite por colisión de datos). La base **aún no existe**: crearla con `database/testing/create_test_database.sql` y migrarla con `--env=testing` tras revisar `migrate:status` (pasos en el encabezado del .sql). Hasta entonces los tests locales fallan con "Unknown database" (falla segura); para correr temporalmente contra `mfa`: `-e DB_DATABASE=mfa`. Falta resolver datos de referencia (p. ej. `provincias`, no sembradas por migración) tras la primera corrida. CI no cambia (el job fija `DB_DATABASE`). Humo nuevo: `BackofficeSmokeTest` y cartelera en `PublicQualityGateTest`.

### Corrección 2026-10-11: description del JSON-LD en cartelera

- Cartelera (`shows/index.blade.php`) usaba solo `body` para `Event.description`; ahora usa `SeoMetadata::event()` como la ficha (meta_description → excerpt → body → genérico). Spec local `event-agenda-description-fallback`, aprobada por el usuario. Test nuevo `EventAgendaDescriptionTest` (3 casos); `tests/Feature/Events` pasa completo. Fallo previo no relacionado: `PublicTemplateSeoTest` (UniqueConstraintViolationException, falla igual sin este cambio). Sin BD, sin URLs. Los 114 eventos locales sin body siguen siendo deuda editorial; no se afirma que esto resuelva el aviso productivo de Search Console. Implementado en local; sin commit, push ni despliegue.
### Checkpoint de feature en curso — 2026-10-10 (integrada en dev local el 2026-10-11, sin cierre)

**Integración 2026-10-11:** a pedido del usuario se mergeó `feature/event-programs-and-venues` en `dev` local para no desfasarse, aunque la feature sigue abierta (validación visual del usuario y ajustes pendientes). Suite completa en local contra `mfa`: 456 pasan, 13 fallan por colisión con datos reales (slugs de knowledge_categories, conteos de auditorías, grupos del torneo), ninguno nuevo. Sin push, sin tag de release (feature no cerrada), sin despliegue.

Por pedido del usuario se guarda un commit de avance de Eventos/jornadas/lugares y sidebar en `feature/event-programs-and-venues` para trabajar luego en un fix. **Feature no finalizada ni archivada**: resta la validación visual/operativa del usuario y continuar ajustes; el hallazgo de descripción vacía en cartelera está documentado, sin corrección implementada. Evidencia automatizada vigente: regresión seleccionada de 106 pruebas y posterior validación dirigida de roles (7 pruebas). Sin push, merge ni despliegue; cambios previos ajenos preservados fuera del commit.

### Auditoría 2026-10-09: description en Eventos

- Revisado el recorrido Backoffice → events.body/excerpt/meta_description → Event → ShowsController/SeoMetadata → contenido visible y JSON-LD. No existe columna description: es una propiedad generada para Google. Backoffice expone Detalles (body) y Resumen (excerpt); no expone meta_description.
- HTTP público: 17 Events de dos páginas de cartelera y sus 17 fichas con description no vacía; fichas también con meta description. No hay URL específica del aviso Search Console ni acceso al informe privado. Debilidad: cartelera usa solo body; puede emitir vacío con registros sin detalle, a diferencia de las alternativas de la ficha. Local: 114/324 eventos sin texto body, ninguno público vigente/en curso. No se afirma que esto causó el aviso productivo.
- Solo auditoría read-only; sin código, cambios BD ni despliegue. Evidencia y recomendación: `project/docs/releases/event-description-audit-2026-10-09.md`.

### Corrección local 2026-10-08: visibilidad administrativa de Eventos

- Reporte de listado vacío pese a eventos públicos. Misma tabla events (324 filas) y conexión local para ambos módulos; 317 eventos/programaciones unificadas. Detectado filtro por autor aplicado a administrador+colaborador; una cuenta local con esos roles no posee eventos y quedaba sin resultados.
- Corregida precedencia de administrador en EventController; colaborador/prensa sin administrador conserva filtro por autor. Spec `unified-event-entry`, tarea 6.5. Regresión reproduce antes y pasa después; archivo UnifiedEventEntryTest: 7 pruebas, 59 aserciones, todas pasan. HTTP interno con cuenta combinada confirma los tres demos visibles. Sin modificar cuentas, roles ni contenido; sin impacto SEO/nuevas consultas, sin migración ni despliegue. La sesión exacta del usuario aún no está confirmada; si persiste, revisar URL/filtros.

### Ingreso unificado de Eventos 2026-10-08 (local)

- **Ampliación aprobada por el usuario:** `openspec/changes/event-programs-and-venues/specs/unified-event-entry/spec.md`. Misma rama activa, sin nueva migración ni conversión masiva.
- **Flujo:** única entrada Eventos / Cartelera y único Nuevo evento, con Una fecha o Varias jornadas. Nombre, detalle, lugar e imagen se ingresan una vez; cada jornada mantiene artistas y horarios. Validación condicional, mínimo dos jornadas al crear y conservación de datos/formato ante errores. Una noche que cruza medianoche sigue siendo una fecha.
- **Listado/edición:** una fila por evento, con cantidad de jornadas y acceso a su edición de datos comunes/jornadas. Búsqueda encuentra artistas de cualquier jornada y título principal; orden y paginación en SQL, eager loading sin consultas por fila. Conjuntos / Jornadas eliminado del sidebar; textos de Backoffice hablan de eventos. Rutas históricas de creación/índice redirigen al ingreso único y rutas de edición/guardado permanecen compatibles; URLs públicas conservadas.
- **Evento existente:** botón Agregar jornadas al evento; operación transaccional con autorización update, conserva Event original como primera jornada, ID/slug/artistas/relaciones/imagen. Idempotente; imagen común se puede cargar desde la edición del evento principal. No se ejecutaron conversiones persistentes sobre contenido anterior.
- **Validación:** 106 pruebas Feature, 52.822 aserciones, pasan (Eventos, ingreso unificado, sidebar, publicación, modelos, festivales y SEO); DatabaseTransactions sobre local/mfa, caché en memoria y response cache desactivado, sin resets. Seis pruebas nuevas con 53 aserciones; consultas del listado constantes al crecer de dos a ocho jornadas y HTML <350 KB. 24 vistas compiladas, PHP y sintaxis JS verificados, Pint/OpenSpec/diff check pasan. Auditoría HTTP interna del listado con demos reales: 200 para 4 jornadas, 5 jornadas y Una fecha. Conteos finales sin cambios: events 324, venues 2, interpretes 446, news 366, users 435, migrations 82. Sin mediciones productivas.
- **Listo para probar:** Backoffice → Eventos / Cartelera → Nuevo evento. QA visual e interacción CKEditor/Select2/selector pendientes de prueba del usuario (sin navegador disponible en esta sesión). Sin commit, push, integración ni despliegue.

### Ajuste local autorizado 2026-10-08: demos de jornadas y sidebar

- **Spec:** `openspec/changes/event-demos-and-sidebar/`, pedido explícito del usuario. Misma rama `feature/event-programs-and-venues`; sin nuevas migraciones, integración ni despliegue.
- **Sidebar:** Música y Cultura desagregado en enlaces directos; Pasarela de Contenidos agrupado como submenú. Enlaces ordenados ASC dentro de cada sección y submenú, ignorando tildes/mayúsculas. Encabezados, rutas y permisos conservados. Sin consultas ni JS nuevos; sin impacto SEO público.
- **Demos persistentes locales:** Encuentro del Río (conjunto 25, cuatro jornadas, 15–18/10/2026), Fiesta de la Luna (conjunto 26, cinco jornadas, 29/10–02/11/2026), Peña del Patio (evento independiente 4123, 12/11/2026). Jornadas 4114–4122. Dos sedes ficticias 19/20, seis intérpretes activos existentes relacionados sin cambiar perfiles. Programación y vínculos inventados, publicados exclusivamente en local con aviso visible DEMO LOCAL; no acreditan curación ni participación real. Jornadas 2 con excepción de precio para probar herencia.
- **Carga revisable:** `project/scripts/local/event-programs-demo.php`, guardas local/mysql/mfa, transacción, detección de parciales y repetición sin duplicar. Manifiesto con IDs/URLs: `storage/app/local-demos/event-programs-manifest.json`. No se eliminó ni sobrescribió contenido anterior; conteos finales events 324 (+10), venues 2 (+2), interpretes 446, news 366, users 435, migrations 82; conjuntos nuevos 2.
- **Validación:** 2 tests Feature del sidebar pasan (30 aserciones), con DatabaseTransactions, response cache desactivado y caché en memoria; Pint, diff check y OpenSpec pasan. Las 12 fichas públicas HTTP 200 con aviso DEMO visible, verificadas desde el contenedor porque PowerShell no resuelve mfa.localhost. Repetición reporta created_now=false sin nuevas filas. Pendiente QA visual/interacción del usuario.

### Implementado y validado en local 2026-10-08: conjuntos de eventos, jornadas y lugares

- **Rama:** `feature/event-programs-and-venues`, creada por pedido explícito del usuario, con sus cambios anteriores preservados. **Spec:** `openspec/changes/event-programs-and-venues/`; alcance aprobado: ficha de conjunto y jornadas propias, ABM independiente de Salas/Lugares, selección/propuesta de sede y CKEditor. Sin mapa ni compactación del formulario laptop.
- **Implementación:** EventProgram con defaults e imagen comunes, jornadas Event con artistas/horarios propios y excepciones; ABM de Venue con búsqueda, estados, coordenadas, duplicados y baja bloqueada si tiene usos; ingresos por Backoffice/API/Bandeja/colaboraciones con servicios comunes; páginas públicas, sitemap y agenda por intersección de fechas. URLs existentes preservadas, cache invalidado al editar y listados paginados con eager loading.
- **Validación:** 98 pruebas Feature pasan entre corrida combinada y repetición del test de encabezados (fixture ajustada por colisión con categoría preexistente); cubren Eventos, permisos, contribuciones, publicación, modelos, festivales y SEO. Transacciones y caché en memoria, sin resets. OpenSpec, sintaxis de 38 archivos PHP, 21 vistas Blade y diff check pasan; build Vite y 13 assets verificados. Prueba de consultas constantes, sin lazy loading y HTML acotado del conjunto pasa; sin mediciones productivas.
- **Migración local:** usuario autorizó explícitamente y se aplicó solo `2026_10_08_120000_create_event_programs`, después de mostrar status. Registros anteriores preservados; 82 migraciones aplicadas. **Listo para prueba del usuario:** revisión interactiva de CKEditor/Select2 pendiente porque esta sesión no tiene navegador disponible. Evidencia y checklist: `openspec/changes/event-programs-and-venues/validation.md`. Sin commit, push ni despliegue.
### Implementado en local y validado por tests 2026-10-10: solicitud de vínculo usuario ↔ ficha (slice 3)

- **Rama:** `feature/organization-entity-management`. Spec: `openspec/changes/entity-claim-requests/`. Sin push, merge ni tag.
- **Qué hay:** `admin/mis-fichas` (cualquier autenticado: fichas que administra, solicitud con búsqueda, estados, cancelar) y `admin/claims` (solo `administrador`: listado por estado, detalle, aprobar con rol, rechazar con motivo). La aprobación usa la organización activa de la ficha o crea la principal y suma al usuario como miembro activo; si la única organización está inactiva se rechaza la aprobación. Funciona igual con login común, Google o Facebook porque depende solo del guard `auth`.
- **Migración:** `2026_10_10_140000_create_entity_claim_requests_table` aplicada en la base local `mfa` tras mostrar `migrate:status` y recibir confirmación. Reversible; tabla nueva sin datos migrados.
- **Validación:** `tests/Feature/EntityManagement`: 51 tests, 301 aserciones OK. Regresión Artists + Festivals + Pasarela + AdminAutoPublish: 139 tests, solo las 2 fallas previas de los auditores. Demo local: 3 solicitudes pendientes.
- **Hallazgo previo al deploy:** el login con Google y Facebook hoy no funciona (reportado por el usuario); no se investigó todavía y es independiente de esta entrega.
- **No validado:** revisión visual en navegador; avisos por correo (no existen); botón público "Reclamar este perfil" y verificación automática (fuera de alcance).

### Implementado en local y validado por tests 2026-10-10: seguimiento de contacto en Organizaciones (opción A)

- **Rama:** `feature/organization-entity-management` (continúa los slices 1 y 2). Spec: `openspec/changes/organization-contact-tracking/`. Sin push, merge ni tag.
- **Qué hay:** en `organizations`, Instagram, Facebook, etapa del contacto, último contacto y notas; listado con filtro por etapa, enlaces a redes y "Registrar contacto"; export CSV de contactables (activas + consentimiento + sin baja + con email). Pensado para el flujo "contacto por IG/FB, luego se obtiene el email". El envío masivo desde el portal queda para cuando haya volumen (opción C); `crm-contact-directory` sigue sin implementar.
- **Migración:** `2026_10_10_120000_add_contact_tracking_to_organizations` aplicada en la base local `mfa` tras mostrar `migrate:status` y recibir confirmación. Reversible; 5 columnas; sin datos migrados.
- **Validación:** `tests/Feature/EntityManagement`: 35 tests, 190 aserciones OK. Regresión Artists + Festivals + Pasarela + AdminAutoPublish: 139 tests, solo las 2 fallas previas de los auditores. Datos demo re-sembrados (9 organizaciones, dos en contacto por redes sin email).
- **Performance y SEO:** sin impacto público. El export recorre con cursor y sin N+1; el listado conserva 25 por página.
- **No validado:** revisión visual en navegador ni envío real de correos (no existe). Los consentimientos deben registrarse solo cuando la persona acepta recibir comunicaciones.

### Implementado en local y validado por tests 2026-10-10: backoffice de Organizaciones (slice 2)

- **Rama:** `feature/organization-entity-management` (continúa el slice 1; el checkout principal quedó en esta rama para revisión local). Spec: `openspec/changes/organization-admin-backoffice/`. Sin push, merge ni tag.
- **Qué hay:** `admin/organizations` (solo `administrador`): listado con filtros, alta/edición con consentimiento y verificación, fichas vinculadas (intérpretes y festivales, con Select2 remoto y organización principal) y miembros con rol/estado. Desactivar una organización revoca permisos. Sin migraciones.
- **Validación:** `tests/Feature/EntityManagement`: 24 tests, 112 aserciones OK (11 del slice 1 + 13 nuevos: acceso, listado, alta/edición, slug, desactivación, vínculos, miembros y búsquedas). Regresión Artists + Festivals + Pasarela + AdminAutoPublish: 139 tests, solo las 2 fallas previas de los auditores. Sin datos residuales en la base.
- **Performance:** listado con 25 por página y conteos en SQL; la edición carga vínculos (morph) y miembros con eager loading; las búsquedas remotas devuelven máximo 20 resultados y exigen 2 caracteres. Sin impacto público ni SEO.
- **No validado:** revisión visual en navegador (queda a cargo del usuario) y el comportamiento de Select2 remoto en pantalla real.
- **Datos demo locales (2026-10-10, autorizado, solo dev):** `OrganizationEntityDemoSeeder` (idempotente, rechaza producción) cargó 7 organizaciones, 9 fichas sin publicar y 10 usuarios `demo.*@demo-local.example.test`, con escenarios de solista, dúo con ayudante y solicitud pendiente, productora con 3 artistas, festival con editor y lector, organización inactiva, contactos sin cuenta y una baja. Se deshace con `OrganizationEntityDemoSeeder::purge()` (solo toca `demo-org-*` y ese dominio). Credencial de prueba definida en el seeder. Test: `OrganizationDemoSeederTest`. 27 tests, 144 aserciones OK en `EntityManagement`.
- **Pendiente:** solicitud de vínculo y login con Google (slice 3), autorización de Eventos y Noticias tras integrar `feature/event-programs-and-venues`. Conflicto probable al integrar en `routes/admin.php`, `config/adminlte.php`, CHANGELOG y este archivo.

### Implementado en local y validado 2026-10-10: administración de fichas por organización (slice 1)

- **Rama desde dev:** `feature/organization-entity-management` (worktree aislado en `storage/app/worktrees/organization-entity-management`; el checkout principal estaba ocupado por `feature/event-programs-and-venues`). Spec: `openspec/changes/organization-entity-management/`. **Sin push, sin merge, sin tag** (el tag se asigna al integrar).
- **Qué hace:** vincula `organizations` con intérpretes y festivales (`organization_entities`) y autoriza la edición por membresía activa `owner`/`editor`. Preserva admin y acceso legacy por `user_id`. Agrega consentimiento/baja/verificación a `organizations`. Listado de festivales de colaboradores incluye lo administrado; listado de intérpretes se recorta solo con `ENTITY_MANAGEMENT_SCOPE_LISTINGS=true` (default apagado). Altas de no administradores crean su organización (`OrganizationProvisioner`).
- **Migración:** `2026_10_08_130000_create_organization_entities_and_contact_metadata` aplicada en la base local `mfa` (autorizada por el usuario, ejecutada sola con `migrate:status` previo). Reversible. Sin datos migrados: las 445 fichas y 47 festivales siguen sin organización hasta que haga falta.
- **Validación:** `EntityManagementTest` 11 tests / 37 aserciones OK, sin datos residuales en la base. Regresión Artists + Festivals + Pasarela + AdminAutoPublish: 139 tests, 2 fallas **previas** (`ArtistAuditCommandTest`, `FestivalAuditCommandTest`), que fallan igual sin este cambio por el contenido real de la base restaurada. Tests corridos desde el worktree con un autoload local en `vendor/autoload.php` (ignorado por git).
- **Performance y SEO:** sin cambios en rutas públicas ni sitemap. La verificación de permiso hace una consulta indexada por usuario y tipo (memoizada); el scope de listado usa subconsulta, 1 consulta (verificado por test).
- **No validado:** comportamiento en navegador, y con cuentas reales. `organizations` nunca se usó en producción.
- **Pendiente / siguientes slices:** pantalla de administración de organizaciones y miembros; autorización de Eventos y Noticias (después de integrar `feature/event-programs-and-venues`, que modifica `Event*`, `AuthServiceProvider` y rutas); login con Google y solicitud de vínculo con verificación por email; decidir relación con `crm-contact-directory`. Reconciliar seeder y base de permisos del rol colaborador. Al integrar, es probable un conflicto trivial en CHANGELOG y este archivo con la otra rama.

### Integrado en dev y validado localmente 2026-10-03: contenidos vinculados de festivales

- **Rama desde dev:** `codex/festival-related-content`. Spec aprobada por el usuario: `openspec/changes/festival-public-related-content/`. Checkout aislado en `storage/app/worktrees/festival-public-related-content` porque otra tarea cambió la rama del checkout principal durante la ejecución.
- **Comportamiento:** las fichas de festivales publicados muestran eventos públicos activos desde el comienzo del día, artistas activos, evergreen visible y noticias publicadas, sin flag ni allowlist. Bloques vacíos ocultos; recomendaciones de tres festivales por provincia y mes conservadas. Se preservan las relaciones públicas de la ficha Evento incorporadas por el otro cambio; Artista y las continuaciones del servicio original mantienen el piloto.
- **Programación:** próximas fechas paginadas de tres en tres mediante `eventos_page`, con título y hora de cada jornada y orden estable por fecha/ID. Canonical de ficha preservado; páginas posteriores `noindex,follow`. No se interpreta Festival–Artista como confirmación de grilla.
- **Performance y SEO:** se eliminan las cargas completas y duplicadas de relaciones en el detalle, preservando el fallback de imagen con las colecciones acotadas. Prueba de consultas constantes al crecer de una a nueve relaciones, sin lazy loading durante el render y HTML menor a 350 KB. No hay nuevos scripts, dependencias, migraciones ni cambios de URLs.
- **Validación:** 18 pruebas Feature, 111 aserciones, todas pasan (relaciones públicas, filtros, paginación, metadata, sitemap y landings). `openspec validate festival-public-related-content` y `git diff --check` pasan. Tests con `DatabaseTransactions` sobre la base local `mfa`, response cache desactivado y caché en memoria; sin DDL ni resets.
- **Integración autorizada:** conflictos resueltos conservando ambos registros de documentación y la condición de dev que oculta la hora `00:00`. CHANGELOG actualizado en Unreleased. Regresión combinada: 45 pruebas Feature pasan, 272 aserciones (Festivales, ficha de Evento y relaciones del backend). Sin migraciones ni resets.
- **Pendiente operativo:** sin push ni despliegue. Al desplegar, invalidar response cache con el flujo habitual. El cambio de código no crea contenido ni vínculos.
- **Demo local autorizada posteriormente (2026-10-03):** cargados cinco eventos futuros, dos noticias, un artista y un evergreen con prefijo `[DEMO LOCAL]`, vinculados a Jesús María (ID 1); usuario/categoría propios de prueba. Fechas y contenidos ficticios, no programación ni curación real. Script revisable y restringido a `local/mfa`: `storage/app/local-demos/festival-links.php`; IDs en `festival-links-manifest.json` de esa carpeta. Transacción confirmada, sin modificar la ficha real ni desvincular relaciones existentes. Response cache local invalidado. HTTP local 200: ficha, página 2, evento, noticia, evergreen y artista. Los cuatro módulos aparecen; página 1 muestra noches 1–3 y página 2 noches 4–5. No se ejecutó ninguna acción en producción.

### Cambio local 2026-10-03: ficha de evento con navegación y relaciones

- Rama `feature/event-detail-navigation`, commits `5aa88b1` (fase 1) y `2106877` (fase 2). OpenSpec: `openspec/changes/event-detail-navigation-and-relations` (aprobado Fases 1 y 2; Fase 1b de datos fuera de alcance). **Implementado, validado en local e integrado a `dev` por el usuario (`07a52f5`); sin desplegar.**
- Auditoría read-only previa: `project/docs/sql/audit_eventos_cobertura.out.txt` (base local: 309 eventos públicos, 3 futuros, 98 % con intérpretes, 9 % con provincia, 0 vínculos con festival, enciclopedia o peña).
- Fase 1: ficha a ancho completo; ficha técnica (fecha y hora, lugar, dirección, provincia, precio o gratis, entradas, cómo llegar) que solo muestra datos cargados; aviso "Este evento ya se realizó" (los pasados siguen `index,follow`); buscador compacto con el parcial `shows/_filters` (también en el índice); breadcrumb con provincia; bloques "Más de {artista}" (próximas o hasta 3 anteriores) y "Próximos eventos en {provincia}"; "Explorar por provincia" solo con provincias con eventos futuros y conteo (corrige el `take(8)` alfabético del índice); artistas visibles fuera del piloto Festival Vivo; JSON-LD `Event` completo sin `offers` inventadas; provincias y resolución por slug cacheadas; consultas muertas eliminadas. `ShowsController::resolve()` sin cambios (test de la prioridad provincia > evento).
- Fase 2: `Event::knowledgeArticles()`; `EventRelatedContentService` (festivales, artículos, peñas con `FEATURE_PENIA_DIRECTORY`, noticias derivadas de festival y artistas, máx. 3); bloques con tracking `event_related_*`; backend de eventos con Select2 para festivales, artículos y peñas, más `end_at` y `excerpt`. Sin migraciones.
- Validación: `tests/Feature/Events` 30/30 (`EventDetailTest`, `EventBackendRelationsTest`, `EventApiAgentFlowTest`, `EventVenueRelationTest`), `PublicEventVisibilityTest`, Festivales, `PublicQualityGateTest` y SEO de plantillas en verde salvo fallos previos ajenos (colisiones de `knowledge_categories.slug` con datos reales, conteo de `FestivalAuditCommandTest`, `created_by` en `PeniaProfilePilotSeederTest`). Ficha: ~8 → 16 consultas de aplicación (caché caliente, sin N+1, todas con `limit`); índice: 6 → 6 (en frío 9 → 7). HTML de la ficha ~24 KB → ~41 KB.
- Pendiente: QA visual del formulario de backend en navegador; Rich Results Test en staging; completar `province_id` (decisión y ejecución humana).

### Incidente 2026-09-16/17: recuperación de contraseña y pérdida de datos locales

- **Causa real del error 500 en `/password/email`**: credencial SMTP rechazada por Hostinger (`535 5.7.8 authentication failed`). El `.env` de producción tenía `MAIL_PASSWORD=1208Gina#` sin comillas, y en un `.env` el `#` abre un comentario: dotenv entregaba `1208Gina`, sin el último carácter. Verificado con `Dotenv::parse`. Con `QUEUE_CONNECTION=sync` el envío ocurre dentro del request, así que la excepción de transporte se convierte en un 500 visible. **No era un bug de código.** Corrección: comillas alrededor del valor. Esa contraseña quedó expuesta en un canal de chat y debe rotarse.
- **Diagnóstico equivocado, commiteado y luego revertido**: los commits `bb16d31` y `8ceb65b` afirmaban que la causa era la falta del trait `CanResetPassword` en el modelo User. Es falso: `Illuminate\Foundation\Auth\User` ya lo incluye. El test que acompañaba el cambio (`method_exists`) pasaba con o sin él, así que no verificaba nada. Ambos cambios fueron revertidos; el tag `v2.2.1` se eliminó y nunca llegó al remoto.
- **Pérdida y recuperación de datos en la BD local**: el test agregado usaba `RefreshDatabase` y `.env.testing` apunta a `DB_DATABASE=mfa`, la misma base de desarrollo. Al ejecutarlo se corrió `migrate:fresh` y se vaciaron todas las tablas, perdiendo los datos restaurados el 2026-09-09. **Producción no fue afectada.** El 2026-09-17 se rehízo la importación con `storage/app/local-backups/import-local.php`, primero en modo `--check` (transacción revertida) y luego `--apply`: 445 intérpretes, 4608 canciones, 411 discos, 808 recetas, 284 mitos, 364 noticias, 309 eventos, 47 festivales, 40 artículos y 434 usuarios, con 87 claves foráneas sin huérfanos y las 81 migraciones preservadas. Conteos confirmados por consulta directa a la BD. Refuerza la regla ya vigente: en este repositorio nunca se usa `RefreshDatabase`, sólo `DatabaseTransactions`.
- **Idioma de la interfaz de autenticación**: `config/app.php` ya declaraba `locale => 'es'`, pero `resources/lang` sólo contenía `en/`, así que los mensajes de login, registro, recuperación de contraseña, los errores de validación y el cuerpo de los correos transaccionales caían al fallback inglés. Se agregaron `resources/lang/es/{auth,passwords,validation,pagination}.php` y `resources/lang/es.json` con las claves JSON de las notificaciones del framework. Los textos de los formularios ya salían en español porque los aporta AdminLTE. Verificado por HTTP contra el sitio local y renderizando el correo de recuperación completo, sin restos en inglés. Convención adoptada: nombres de campo como sustantivos sin artículo y mensajes con "El campo :attribute", para que cierre la concordancia de género.
- **Deploy**: `php artisan about` en producción reporta `Config NOT CACHED` pese a haber corrido el ciclo de cachés; `Routes` y `Views` sí quedan cacheadas. Pendiente de revisar. `scripts/post-deploy.sh` no incluye `composer dump-autoload` ni `responsecache:clear`, ambos necesarios según esta misma documentación.
- **Cerrado 2026-09-18 — causa real del login sin sesión**: no era la sesión ni `EnforceCanonicalDomain` (ambas hipótesis descartadas). El `POST /login` sí autentica y redirige 302 a `/admin`; el problema es que ese `GET /admin` vuelve a dar 302 a `/login` porque la respuesta quedó **cacheada** por `Spatie\ResponseCache\Middlewares\CacheResponse`. Ese middleware corre en el stack global, antes de `StartSession` (`App\Http\Kernel`), así que `request()->user()` es siempre `null` cuando `PublicPagesCacheProfile::shouldCacheRequest()` evalúa si cachear — la regla "nunca cachear si hay usuario logueado" nunca se disparaba, y `/admin` no estaba en `excludedPatterns`. El paquete trata los redirects como cacheables (`CacheAllSuccessfulGetRequests::hasCacheableResponseCode`), así que un solo `GET /admin` sin sesión (bot o primer visitante del día) dejaba ese 302 cacheado hasta 6 horas (`cache_lifetime_in_seconds`) y se lo servía después a cualquiera, logueado o no. Fix: `admin*`, `avisos-clasificados/publicar` y `avisos-clasificados/mis-avisos` agregados a `excludedPatterns` en `App\Support\ResponseCache\PublicPagesCacheProfile`. Ver `CHANGELOG.md` v2.3.1. **Pendiente**: desplegar y correr `artisan responsecache:clear` en producción (ya incluido en `scripts/post-deploy.sh`) para purgar el 302 ya cacheado — el fix de código solo no alcanza mientras esa entrada vieja siga en cache.

### Ejecución de backlog Drive 2026-09-14: mapa de intención SEO

- `BL-0010O` cerrada como análisis autónomo: [mapa SEO de consultas e intención](releases/seo-keyword-intent-map-2026-09-14.md) cubre artistas, biografías, letras, discos, noticias, eventos, festivales y Evergreen con destino existente, riesgo de canibalización y acción segura.
- Evidencia cuantitativa disponible: línea base GSC de 28 días registrada el 2026-08-10 (189 clics, 26.767 impresiones, CTR 0,71 %) y cuatro consultas de Letras con oportunidad de CTR. El documento separa esos datos comprobados de clústeres propuestos.
- Sin crear URLs, modificar contenido, canonicals, indexación, producción, despliegues ni commits. Queda pendiente un export GSC/GA4 vigente consulta–página para validar volumen y canibalización real; ese insumo alimenta `BL-0010V`.
- `BL-0010V` queda **Parcial**: se documentó la [especificación del tablero SEO](releases/seo-monitoring-dashboard-spec-2026-09-14.md) con fuentes, segmentos, KPIs ponderados, vistas, cadencia y gates. Falta el export verificable GSC/GA4 para cargar datos y obtener comparaciones temporales reproducibles.

### Cambio local 2026-09-13: performance, contenido y sidebars

- **Performance real**: root cause del LCP `loading="lazy"` que nunca funcionaba: 10 clases de componente Blade con constructor vacío (`app/View/Components/*`) tapaban los componentes anónimos reales y descartaban en silencio cualquier prop no declarado en el constructor (`imageLoading`, `imageFetchpriority`, etc.). Se eliminaron las 10 clases muertas más 8 análogas en `app/View/Components/Sidebar/`. También se corrigió un closure y una colisión de nombres de ruta que bloqueaban `route:cache`, y un bug real donde `EditorialImageResolver` volcaba un modelo Eloquent completo como JSON en el `alt` de una imagen de fallback (Album/Cancion, por la relación `interprete()` homónima a la columna).
- **Imágenes**: los 11 fallbacks editoriales se redujeron de ~230 KiB a ~55 KiB c/u y se renombraron con sufijo `-v2` (el cache `immutable` de `public/.htaccess` no invalida por contenido, solo por URL). Nueva guía `project/docs/editorial/image-upload-guide.md` con tamaño mínimo y ratio recomendado por entidad.
- **Contenido y SEO**: unificado el copy, contenedor (`bg-white p-2 rounded shadow-sm`) y tipografía de H1 en las 8 homes de sección (antes 3 tratamientos distintos conviviendo). Se activaron rutas/bloques que existían en código pero nunca se conectaron (filtro de categorías en Noticias, "más visitas" en Canciones). Buscador por nombre nuevo en Artistas (no existía ninguno funcional). Contadores de volumen de catálogo en Home y en las secciones principales.
- **Paginación**: las 8 secciones que usaban `simplePaginate()` pasan a `paginate()` para mostrar total y números de página (antes solo Festivales lo tenía; mismo componente compartido en todo el sitio, no eran componentes distintos).
- **Sidebars**: auditoría completa de los 17 componentes — 9 sin usar, 7 de esos rotos (rutas/campos de antes del refactor de modelos). Se borraron los 8 verdaderamente muertos y se dejó un criterio explícito por sección: publicidad en Noticias/Recetas/Mitos, invitación a publicar en Festivales/Cartelera, sidebar propio (sin default heredado) en Clasificados/Peñas/Radios, widget nuevo "Artistas más leídos" en la ficha de artista.
- **Íconos sociales**: colores reales de marca (Facebook, Instagram, X) como insignias cuadradas `rounded-lg`, consistente con los botones del sitio.
- Peñas y Radios se activaron localmente vía `.env` (`FEATURE_PENIA_DIRECTORY`, `FEATURE_RADIO_DIRECTORY`) para poder revisarlas — confirmar si corresponde promoverlas a producción.
- Pendiente, fuera de alcance por decisión explícita: bloque editorial "Un día como hoy" (requiere contenido curado a mano, no generado).

### Cambio local 2026-09-08: paginados públicos

- Pedido explícito del usuario: estética compartida y textos españoles en todos los controles públicos. OpenSpec: `openspec/changes/unify-public-pagination-spanish`.
- Se sustituyeron 17 puntos de paginación por el componente Tailwind `public-pagination`, con espaciado uniforme, navegación accesible y preservación de filtros. El backend conserva Bootstrap. Sin cambios de BD, consultas, rutas ni despliegue.
- Validación: 7 pruebas Feature / 41 aserciones correctas; Vite compilado y 13 assets verificados. QA visual pendiente: Browser sin conexiones y Docker local apagado.
- Inventario por entidad y checklist: `openspec/changes/unify-public-pagination-spanish/implementation-notes.md`.
- Auditoría simple de campos SEO solicitada antes de este cambio: `project/docs/releases/seo-fields-audit-2026-09-08.md`. Los campos principales existen en las 12 entidades editoriales revisadas; quedan brechas de edición/uso, especialmente Noticias/Eventos y Clasificados. No se verificó completitud en BD ni producción.

> **Fuente de verdad operativa.** Actualizar al cerrar cada sesion de trabajo.
> Ultima actualizacion: 2026-09-18 (causa real del login sin sesion encontrada: 302 a /login cacheado por responsecache antes de que StartSession corra; fix en PublicPagesCacheProfile, pendiente desplegar y correr responsecache:clear en produccion)

---

## Rama activa

`main` - rama activa y desplegada en producción al 2026-09-08.

### Próxima ejecución autónoma

- **Objetivo:** llevar el producto al 99% de preparación operativa sin adelantar despliegues ni decisiones humanas.
- **Tarea elegible actual:** no hay tareas `IA_AUTONOMA` pendientes. `PROD-09` cerró el empaquetado seguro de Vite para producción sin Node; `PROD-01` queda en revisión porque el remanente de Laravel/Symfony requiere una estrategia coordinada para Laravel 11+.
- **Último cierre autónomo:** `PROD-09` agregó validación integral del manifiesto y assets Vite, 5 pruebas Node, artefacto CI descargable `vite-build-<SHA>` y runbook de transferencia/rollback. Evidencia local: build correcto y 13 assets declarados presentes.
- **Último cierre autónomo:** `PROD-08` incorporó la suite publica de calidad a CI: 2 pruebas y 50 aserciones sobre las landings criticas, estructura semantica accesible y presupuesto HTML de 350 KB. Tambien corrigio el `h1` ausente de la landing de Festivales.
- **Cierre anterior:** `PROD-04` retiró logs de depuración de layouts activos; prueba de regresión y Blade cache verdes.
- **Último cierre autónomo:** `PROD-02` completó `GET /healthz`, diagnóstico restringido a administrador, heartbeat de scheduler y [runbook operativo](releases/operational-health-runbook.md); pruebas: 4 passed, 18 assertions.
- **Siguientes gates humanos:** `PROD-01` redujo Composer audit de 56 a 43 avisos, sin pendientes de Guzzle; el remanente requiere una estrategia coordinada para Laravel 11+. Para Peñas/Radios resta confirmar la activación visible, cargar/revisar contenido real y validar Content Refresh antes de automatizar volumen.
- **Regla de continuidad:** `03_backlog_mvp.md` es la cola local ejecutable; Drive conserva la priorización humana, comercial y editorial.

**Flujo vigente:** rama feature -> PR/CI -> revision de Eduardo -> merge a `main` por Eduardo -> pull y deploy en produccion. Los agentes no tocan ni fusionan `main` ni ejecutan despliegues.

### Orden de cierre técnico vigente

| Punto | Alcance | Estado |
|------:|---------|--------|
| 1 | Reparar CI y conflictos Fortify/Jetstream | `done` |
| 2 | Cerrar permisos editoriales y feature flags de Peñas/Radios | `done` |
| 3 | Corregir la migración destructiva de Peñas | `done` |
| 4 | Terminar Peñas y cargar diez perfiles reales | `done` técnicamente; carga/revisión editorial pendiente |
| 5 | Completar Radios: backoffice, programas públicos, auditor y SEO | `done` técnicamente; carga/revisión editorial pendiente |
| 6 | Integrar Peñas y Radios con Content Refresh | `done` técnicamente; piloto real pendiente antes de automatizar volumen |
| 7 | Desplegar y validar base técnica | `done`: `main` desplegado, assets Vite presentes, migraciones aplicadas y home/`healthz` en `200`; resta validar activación visible |

---

## Estado del proyecto

Backlog original completo (PC-01 a PC-13 en `done`). El proyecto salio de la fase puramente tecnica de estabilizacion y entro en una fase dual: **autoridad editorial continua** y **evolucion funcional del portal**.

El backlog operativo activo para MFA ya no vive en `project/docs/backlog.json`: la operacion diaria priorizada se sigue en Google Drive (`Backlog Asistente ChatGPT`, pestaña `Backlog`). El JSON local queda como legado estructurado e historico.

Se realizo auditoria completa del codigo el 2026-04-26. Ver seccion de bugs corregidos.

### Direccion vigente: dos programas paralelos

| Programa | Objetivo | Unidad de avance | Gate principal |
|----------|----------|------------------|----------------|
| **A. Autoridad editorial** | Mejorar profundidad, verificabilidad, estructura, SEO, relaciones y media; descubrir altas relevantes sin degradar calidad. | Lotes pequenos por entidad, priorizados por deuda y demanda. | Investigacion y fuentes completas; revision humana antes de `ENVIAR_API=S`. |
| **B. Producto y servicios** | Completar navegacion, automatizacion, comunidad, distribucion y servicios diferenciales. | Releases tecnicos independientes, con CI, smoke, medicion y rollback. | Release gate por modulo y validacion real en produccion. |

Ningun programa reemplaza al otro. El backlog oficial sigue siendo `Backlog Asistente ChatGPT` en Drive; `project/docs/backlog.json` es referencia legacy y no gobierna prioridades.

### Corte histórico técnico y operativo 2026-09-01

> Esta fotografía conserva el estado previo al deploy del 2026-09-08. El estado vigente del release se resume al inicio del documento y en “Confiabilidad del release”.

- **Festivales:** modernizacion técnica y Content Refresh en condiciones productivas; auditor y cola editorial operativos. Desde `ED-A4`, el auditor incorpora visitas en consola/CSV y desempata por mayor demanda.
- **Biografias / Artistas:** modelo, migraciones legacy, backoffice, API, frontend, SEO/schema y auditor modernizados en produccion.
- **Recetas:** contrato editorial, campos estructurados, backoffice, API, frontend/schema y auditor disponibles en produccion; recuperacion masiva pendiente.
- **Mitos y Leyendas:** contrato cultural, region/tipo, SEO, media, backoffice/API/frontend y auditor modernizados; recuperacion masiva pendiente.
- **Content Refresh:** soporta Artista, Receta, Mito, Peña, Radio y ProgramaRadio con `CREAR/ACTUALIZAR`; Festival mantiene el mismo circuito de ficha evergreen. Exige `ACCION_API` explicita, bloquea updates vacios, preserva campos omitidos y dispone de preflight offline separado para los seis casos core y los seis de directorios. Las altas de Peñas, Radios y Programas fuerzan `draft/pending` desde el cargador y no habilitan los feature flags. Los E2E controlados siguen pendientes.
- **Producto:** `BL-0022C` recomienda como primer piloto `Festival evergreen -> Evento futuro -> Artista -> repertorio/actualidad`, condicionado a auditoria de cobertura, feature flag, allowlist y aprobacion funcional.
- **Curacion:** `BL-0021B` tiene 3 de 10 borradores preparados: Gaston Cordero, Los Trovadores de Cuyo y Juan Bautista Bertorello. Todos permanecen con `ENVIAR_API=N`.
- **Penas:** módulo evergreen cerrado para DEV sobre `penia_profiles`. Las rutas, controlador, modelo y vistas del módulo legacy fueron retirados, pero la tabla `penias` y el puente `penia_profiles.legacy_penia_id` se preservan hasta completar y validar el backfill. La migración destructiva `2026_09_03_010000_retire_legacy_penias_table` ahora es un no-op seguro y `2026_09_03_015000_restore_legacy_penias_bridge` repara de forma idempotente instalaciones donde aquella versión ya hubiera corrido. La auditoría previa confirmó que la tabla estaba vacía, por lo que no existen filas perdidas que reconstruir. Incluye API, landing, ficha con el mismo buscador de la home, descubrimiento de Peñas de la misma provincia, ubicación completa, mapa por coordenadas, sitemap, CRUD, preview noindex, acciones editoriales, auditor read-only `mfa:penias:audit` y Content Refresh `CREAR/ACTUALIZAR` con updates parciales. `PeniaProfilePilotSeeder` reemplaza el escenario ficticio por 10 fichas reales de la ciudad de Salta basadas en el directorio municipal: La Cautiva, La Panadería del Chuña, Boliche Balderrama, La Casona del Molino, El Antigal, Huayra, Peña del Minero, Casa Grande, La Vieja Estación y Peña del Chaqueño Palavecino. La carga es idempotente, archiva sólo los slugs demo conocidos, conserva cualquier ficha real ya revisada y crea las nuevas como `draft/pending`, sin publicarlas ni adjudicar una verificación. El público permanece apagado por defecto con `FEATURE_PENIA_DIRECTORY=false`: la landing, las fichas, el NAV y sus sitemaps sólo se exponen al habilitar el flag. Colaboradores y prensa pueden crear y editar únicamente sus borradores propios; todo intento de auto-verificar, aprobar o publicar se normaliza a propuesta pendiente y las acciones canónicas quedan reservadas al rol `administrador`. Antes de activar producción, un administrador debe ejecutar la carga explícita, confirmar contacto/datos, revisar derechos de imagen y aprobar cada ficha.
- **Radios:** punto 5 cerrado en DEV sobre el dominio canónico `radio_signals`, `radio_listening_channels`, `radio_programs` y `radio_program_slots`. Incluye API autenticada; backoffice completo de señales, canales, programas y franjas; preview, publicación y despublicación; filtros de calidad/verificación; landing y ficha de emisoras; directorio y ficha pública de programas bajo `/radios-de-folklore-argentino/programas`; próxima emisión calculada por zona horaria; relaciones emisora-programa; sitemap; canonical/noindex; datos estructurados `RadioStation` y `RadioSeries`; auditor read-only `mfa:radios:audit`; y Content Refresh para señales y programas con updates parciales, canales/franjas estructurados y altas `draft/pending`. La visibilidad pública permanece apagada por defecto con `FEATURE_RADIO_DIRECTORY=false`, independientemente de Peñas. Colaboradores y prensa sólo pueden proponer y editar borradores propios; verificar, publicar y despublicar sigue reservado a `administrador`. El módulo legacy permanece intacto. Quedan pendientes un lote editorial real y el release gate de staging/producción (punto 7).
- **Confiabilidad del release:** el gate automatizado quedó verde en GitHub Actions sobre `d655139` (run `33866188915`). El 2026-09-08 se confirmó el despliegue de `main`, las migraciones y respuestas `200` de home y `/healthz`. Como el servidor no dispone de NPM, Vite debe compilarse fuera del servidor y subirse completo como `public/build`; una primera subida sin ese artefacto dejó el sitio sin estilos y se recuperó al subirlo. Peñas y Radios siguen condicionados por flags y su presencia en el NAV aún no quedó confirmada.

### Festival Vivo - piloto local 2026-09-02

- El recorrido `Festival -> Evento futuro -> Artista` esta implementado en las tres fichas, limitado en SQL y protegido por feature flag + allowlist.
- La auditoria read-only local encontro dos festivales elegibles: IDs `4279` y `4280`, cada uno con un evento futuro publico y un artista activo relacionado. Solo el `.env` local habilita esos IDs; el default versionado permanece apagado.
- Las impresiones `view_item_list` y clics `select_content` usan IDs internos, modulo y posicion, sin datos personales.
- Pruebas de servicio, render habilitado/deshabilitado, SEO/canonical y enlaces publicos locales: `7 passed (30 assertions)`; compilacion Vite correcta.
- No esta cerrado para produccion: falta observar el piloto durante siete dias y decidir expansion o rollback con medicion real.

### Linea base editorial de produccion — 2026-09-01

| Entidad | Total | P1 | P2 | P3 | Score promedio | Imagen fallback | Visitas en auditor |
|---------|------:|---:|---:|---:|---------------:|----------------:|-------------------:|
| Festivales | 30 | 22 | 6 | 2 | 45,5 | 2 | No |
| Artistas | 444 | 313 | 131 | 0 | 39,0 | 125 | Si |
| Recetas | 808 | 808 | 0 | 0 | 13,0 | 808 | Si |
| Mitos | 284 | 284 | 0 | 0 | 20,9 | 284 | Si |
| **Total** | **1.566** | **1.427** | **137** | **2** | **22,4 ponderado** | **1.219** | **1.536 registros** |

Orden operativo de curacion: prioridad, menor score, mayores visitas dentro del mismo nivel de deuda e ID como desempate estable. La magnitud de P1 requiere lotes pequenos, auditoria antes/despues y medicion posterior.

### Alineacion con la vision

| Dimension | Alineacion | Lectura |
|-----------|------------|---------|
| Base tecnica confiable | Alta y desplegada | `main` está en producción, las migraciones fueron aplicadas y home/`healthz` responden `200`; falta cerrar la comprobación visible de Peñas/Radios. |
| Cobertura integral | Alta | MFA articula artistas, discos, letras, noticias, eventos, festivales, Enciclopedia, recetas y mitos. |
| Autoridad editorial | Media/alta en sistema; baja en inventario legacy | Existen contratos y auditores, pero 1.427 registros siguen P1. |
| SEO y performance | Alta | Base tecnica disponible; falta medicion continua de impacto. |
| Interconexion y descubrimiento | Media | Existen relaciones, pero faltan recorridos transversales consistentes y medibles. |
| Servicios diferenciales | Media/baja | Agenda, Pasarela, UGC, newsletter y torneos necesitan validacion o propuesta operativa cerrada. |
| Automatizacion segura | Media/alta | Bandeja, APIs y auditores estan integrados; faltan los seis E2E controlados. |
| Monetizacion sostenible | Baja/media | Falta ligar roadmap, audiencias y servicios a ingresos mas alla de AdSense. |

El siguiente salto combina autoridad demostrable con mejores recorridos de usuario, servicios recurrentes, distribucion y medicion de valor; no consiste solamente en publicar mas contenido.

### Proximos gates comunes

1. Ejecutar los seis casos core y los seis casos de directorios de Content Refresh con sus preflight y runbooks, una fila por vez.
2. Validar durante siete dias el piloto local/controlado de `Festival Vivo` antes de ampliar allowlist o evaluar release.
3. Completar y revisar el lote de 10 Biografias P1; despues continuar con Recetas y Mitos.
4. Mantener la medición de visitas del auditor de Festivales dentro de la línea base editorial recurrente.
5. Aprobar la politica de derechos antes de automatizar Discografia/Cancionero.
6. Validar Pasarela y UGC end-to-end antes de tratarlos como servicios cerrados.

### Gobernanza documental verificada el 2026-08-20

- ruta local contrastada: `C:\proyectos\lmfa`
- rama contrastada: `20260820`
- commit contrastado: `20c0394b5a9273b63ee059d52f9c26b627a43da2`
- precedencia canónica consolidada en `project/docs/FUENTES_CANONICAS.md`
- matriz puntual de entidades y relaciones consolidada en `project/docs/09_matriz_canonica_entidades_relaciones.md`
- `project/docs/backlog.json` ratificado como legado no canónico para seguimiento diario
- `project/docs/01-funcional.md` ratificado como nombre vigente frente a referencias legacy con guion bajo

---

## Base de datos

**Motor:** MariaDB 10.8 en Docker (`lmfa-db-1`).

### Tablas activas de dominio (leidas y/o escritas por la app)

#### Contenido editorial principal

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `news` | `News` | Activa - modelo canonico para noticias. Campo principal: `editorial_status`. |
| `events` | `Event` | Activa - modelo canonico para eventos/shows. Campo principal: `start_at`. |
| `interpretes` | `Interprete` | Activa |
| `albunes` | `Album` | Activa |
| `canciones` | `Cancion` | Activa |
| `festivales` | `Festival` | Activa |
| `mitos` | `Mito` | Activa |
| `comidas` | `Comida` | Activa |
| `knowledge_categories` | `KnowledgeCategory` | Activa - taxonomia evergreen de Enciclopedia |
| `knowledge_articles` | `KnowledgeArticle` | Activa - base editorial de `/enciclopedia` |

#### Clasificados y taxonomias auxiliares

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `classifieds` | `Classified` | Activa |
| `categories` | `Category` | Activa - categorias de clasificados |
| `tags` | `Tag` | Activa - etiquetas de clasificados |
| `classified_tag` | `ClassifiedTag` / pivot | Activa - relacion N:M clasificados/tags |
| `categorias` | `Categoria` | Activa - categorias legacy/canonicas usadas por `news` |

#### Geografia y taxonomias de navegacion

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `provincias` | `Provincia` | Activa |
| `localities` | `Locality` | Activa - usada por festivales |
| `meses` | `Mes` | Activa - usada por festivales |

#### Pasarela, UGC y moderacion

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `contributions` | `Contribution` | Activa - flujo UGC |
| `organizations` | `Organization` | Activa - Pasarela |
| `organization_members` | `OrganizationMember` | Activa - Pasarela |
| `social_accounts` | `SocialAccount` | Activa - Pasarela |
| `publication_requests` | `PublicationRequest` | Activa - Pasarela |
| `publication_targets` | `PublicationTarget` | Activa - Pasarela |
| `publication_attempts` | `PublicationAttempt` | Activa - Pasarela |
| `publication_templates` | `PublicationTemplate` | Activa - Pasarela |
| `moderation_reviews` | `ModerationReview` | Activa |
| `audit_logs` | `AuditLog` | Activa |
| `newsletter_subscribers` | `NewsletterSubscriber` | Activa |
| `user_notifications` | `UserNotification` | Activa |
| `data_deletion_requests` | `DataDeletionRequest` | Activa - legales / Meta-Facebook |

#### Torneos folkloricos

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `folklore_tournaments` | `FolkloreTournament` | Activa |
| `folklore_tournament_groups` | `FolkloreTournamentGroup` | Activa |
| `folklore_tournament_participants` | `FolkloreTournamentParticipant` | Activa |
| `folklore_tournament_matches` | `FolkloreTournamentMatch` | Activa |

#### Pivots y relaciones activas

| Tabla | Tipo | Estado |
|-------|------|--------|
| `interprete_noticia` | Pivot legacy | Activa - relacion noticias legacy/artistas y compatibilidad historica |
| `event_interprete` | Pivot | Activa - relacion eventos/artistas |
| `event_festival` | Pivot | Activa - relacion eventos/festivales |
| `festival_interprete` | Pivot | Activa - relacion festivales/artistas |
| `festival_news` | Pivot | Activa - relacion festivales/noticias |
| `album_interprete` | Pivot | Activa |
| `albunes_canciones` | Pivot | Activa |
| `knowledge_article_interprete` | Pivot | Activa - relacion articulos/artistas |
| `knowledge_article_cancion` | Pivot | Activa - relacion articulos/canciones |
| `knowledge_article_album` | Pivot | Activa - relacion articulos/discos |
| `knowledge_article_festival` | Pivot | Activa - relacion articulos/festivales |
| `event_knowledge_article` | Pivot | Activa - relacion articulos/eventos |
| `knowledge_article_provincia` | Pivot | Activa - relacion articulos/provincias |
| `knowledge_article_related` | Pivot | Activa - enlazado interno entre articulos |

#### Seguridad, autenticacion e infraestructura Laravel

| Tabla | Uso | Estado |
|-------|-----|--------|
| `users` | Usuarios, auth, permisos, publisher fields y tokens sociales | Activa |
| `roles` | Spatie Permission | Activa |
| `permissions` | Spatie Permission | Activa |
| `model_has_roles` | Pivot ACL | Activa |
| `model_has_permissions` | Pivot ACL | Activa |
| `role_has_permissions` | Pivot ACL | Activa |
| `personal_access_tokens` | Sanctum | Activa |
| `sessions` | Sesiones Laravel | Activa |
| `jobs` | Cola database | Activa |
| `failed_jobs` | Fallos de cola | Activa |
| `migrations` | Historial de migraciones | Activa |

### Tablas existentes con uso parcial, ambiguo o pendiente de auditoria

| Tabla | Estado |
|-------|--------|
| `radios` | Legado conservado sin cambios. El reemplazo canónico usa `radio_signals`, `radio_listening_channels`, `radio_programs` y `radio_program_slots`; está desplegado y resta validación pública/editorial. |
| `penias` | Legado conservado temporalmente. El directorio vigente opera sobre `penia_profiles`; la tabla y el puente `legacy_penia_id` se preservan hasta validar el backfill y retiro definitivo. |
| `venues` | Existe en BD por la transformacion a `events`, pero no hay `Venue` model en `app/Models`. Estado funcional incompleto / pendiente de auditoria. |

### Observacion puntual de inventario local

Conteos observados en la BD local durante la auditoria del 2026-08-20:

- `news`: 353
- `events`: 8
- `noticias`: 0
- `shows`: 0
- `publication_requests`: 0
- `publication_targets`: 0
- `publication_attempts`: 0
- `jobs`: 0
- `failed_jobs`: 0
- `newsletter_subscribers`: 2
- `data_deletion_requests`: 0

Lectura correcta:

- `news` y `events` son las tablas canonicas efectivamente cargadas en este entorno local.
- `noticias` y `shows` siguen existiendo por compatibilidad, pero no contienen datos operativos en esta BD local.
- La Pasarela de Contenidos esta implementada en codigo y cubierta por tests, pero no presenta actividad cargada en este entorno local al momento de la auditoria.

### Indices de performance agregados recientemente

Se agregaron migraciones de indices para mejorar tiempo de respuesta en frontend y listados administrativos:

- `2026_08_09_120000_add_performance_indexes.php`
- `2026_08_09_140000_add_secondary_performance_indexes.php`
- `2026_08_09_150000_add_content_section_indexes.php`

Cobertura principal:

- `news`: `published_at`, combinaciones por `created_by`, `categoria_id`
- `events`: `start_at`, combinaciones por `editorial_status`, `province_id`
- `festivales`: `status`, `published_at`, `province_id`, `mes_id`, `title`, `user_id`
- `interpretes`: `interprete`, `estado`, `slug`
- `albunes`: `estado`, `created_at`, `interprete_id`, `anio`, `slug`
- `canciones`: `estado`, `cancion`, `interprete_id`, `slug`
- `comidas`: `estado`, `titulo`, `visitas`, `slug`
- `mitos`: `estado`, `titulo`, `visitas`, `slug`

### Tablas legacy (existen en BD, no deben usarse para contenido nuevo)

| Tabla | Estado |
|-------|--------|
| `noticias` | Datos previos a la migracion a `news`. No se lee ni escribe. Evaluar limpieza. |
| `shows` | Legacy previa a `events`. Sigue existiendo en BD por compatibilidad historica. No debe usarse para nuevas altas. |
| `images` | Reemplazada por `media_assets`. |

### Tabla canonica de media

| Tabla | Modelo | Estado |
|-------|--------|--------|
| `media_assets` | `MediaAsset` / `Image` | Activa - almacenamiento canonico de imagenes y variantes webp. El modelo `Image` extiende `MediaAsset` por compatibilidad de codigo. |

### Nota critica - compatibilidad legacy

Los modelos `News` y `Event` tienen accessors que mapean nombres de campos viejos (`titulo`, `noticia`, `fecha`, `estado`) a los nuevos (`title`, `body`, `start_at`, `editorial_status`). Estos solo funcionan en instancias, no en queries `where`. Siempre usar el nombre canonico en consultas.

### Nota tecnica - DDL en entorno local

MariaDB 10.8.8 sobre el bind mount anterior de Windows/WSL se caia con ciertos `ALTER TABLE` sobre tablas InnoDB. El 2026-09-04 se reprodujo con indices de `penias` y `classifieds`, y el historial recuperado retrocedio a 40 entradas. El servicio fue reconstruido en el volumen Docker `lmfa_db_data`, conservando `database_local` sin borrar; el nuevo entorno completo las 81 migraciones y `php artisan test` pasa con 335 pruebas, 2 skipped y 1769 aserciones. Mantener vigilancia sobre DDL local, pero el entorno ya no esta bloqueado.

### Recuperacion local propuesta

El servicio `db` usa el volumen nombrado `lmfa_db_data`, administrado por
Docker, para evitar el bind mount de Windows que provocaba los crashes. El
directorio `database_local` no se elimina ni se monta: conserva el estado
anterior para una eventual recuperacion manual. Antes de migrar una base nueva,
verificar `docker compose ps`, `mysqladmin ping` y `php artisan migrate:status`.
Si el nuevo volumen no fuera estable, restaurar temporalmente el mount anterior
sin borrar ninguno de los dos almacenamientos.

El volumen reconstruido cuenta con un escenario demo idempotente: 24 provincias,
2 festivales, 2 eventos y 2 interpretes de Festival Vivo; 10 Peñas piloto en
`draft/pending`; y 5 radios con 10 programas publicados. Se creo el usuario
local `admin.local@mifolkloreargentino.test` con rol `administrador` para operar
los seeders y el backoffice. En modo oscuro, con ambos flags en `false`, la home
responde `200` y las superficies publicas de Peñas, Radios y Programas
responden `404`.

---

## Arquitectura de rutas

| Archivo | Middleware | Proposito |
|---------|------------|-----------|
| `routes/web.php` | `web` | Frontend publico + colaboraciones UGC |
| `routes/admin.php` | `web`, `auth` | Panel admin + Pasarela de Contenidos bajo `/admin` |
| `routes/api.php` | `api`, `auth:sanctum` | API REST v1 |

**Nomenclatura:** rutas admin usan prefijo `backend.*` (ej: `backend.events.index`). Pasarela usa `pasarela.*`.

---

## Estado por modulo

### Backend administrativo funcional

- CRUD y gestion editorial de `Events`, `News` (unificado), `Knowledge Articles`, `Interpretes`, `Albunes`, `Canciones`, `Comidas`, `Festivales`, `Mitos`, `Categorias`, `Tags`, `Clasificados`, `Roles`, `Users` y `Permissions`.
- Centro de moderacion funcional.
- Gestion de newsletter funcional.
- Backend de torneos folkloricos funcional para torneos y partidos.

### Frontend publico funcional

- Home.
- Noticias del folklore argentino.
- Enciclopedia (`/enciclopedia`) con portada, familias y articulos.
- Cartelera de eventos folkloricos.
- Miniportal del artista con biografia, noticias, letras, discografia y shows.
- Cancionero / letras de canciones.
- Discografias.
- Festivales y fiestas tradicionales.
- Mitos y leyendas.
- Recetas de comidas tipicas.
- Clasificados.
- Contacto.
- Buscador.
- Sitemaps.
- Newsletter.
- Social Auth.
- Copa del Folklore Argentino 2026: portada, participantes, fixture, zonas, llaves y reglamento.

### API REST funcional

- Endpoints de lectura/escritura autenticada para:
  - `news`
  - `knowledge-articles`
  - `knowledge-categories`
  - `albums`
  - `songs`
  - `foods`
  - `festivals`
  - `artists`
  - `myths`
  - `events`
- Lectura disponible para cualquier token Sanctum valido.
- Escritura restringida a `role:administrador`.

### Optimizaciones recientes validadas localmente

- **Listados backend**:
  - `News`, `Events`, `Festivales`, `Interpretes` y `Users` dejaron de depender de tablas client-side pesadas para el flujo principal.
  - Se uso paginacion server-side y consultas mas acotadas.
  - Se redujeron eager loads innecesarios y se quitaron miniaturas donde no aportaban al listado.

- **Frontend publico**:
  - Home, Noticias, Festivales, Enciclopedia, Cartelera, Cancionero, Mitos, Recetas, Discos e Interpretes recibieron recortes de queries, cache y render.
  - Se cachearon bloques editoriales y sidebars donde era razonable.
  - Se corrigieron multiples `file_exists()` y otros checks costosos en vistas/components.
  - Se redujo el peso de paginas alfabeticas de `mitos` y `comidas` con paginacion mas corta y tarjetas mas livianas.

- **Performance mobile / Core Web Vitals**:
  - El frontend publico ahora usa bundle propio: `resources/css/app-public.css` y `resources/js/app-public.js`.
  - `Font Awesome` dejo de cargarse globalmente en el frontend publico y quedo restringido al bundle del backend/AdminLTE.
  - Alpine salio del bundle base publico y solo se carga en pantallas que realmente lo necesitan, como clasificados con slider.
  - Google Analytics y AdSense pasaron a carga diferida via `layouts/partials/third-party-scripts.blade.php`.
  - Se priorizo mejor la primera imagen visible de home/noticias.
  - `optimized-image.blade.php` fue reescrito para elegir un `src` inicial mas razonable por variante y usar `decoding="async"`.
  - Se agrego `content-visibility: auto` para sidebar, footer y bloques secundarios de varias homes/listados, buscando reducir costo de render inicial en mobile.
  - El 2026-08-25 se aplico una nueva tanda de recorte server-side para mobile: home bajo de 12 a 6 noticias iniciales; noticias bajo de 16 a 12 con sidebar de 10 a 4; discos bajo de 24 a 12; letras bajo de 36 a 18; artistas bajo de 24 a 12; festivales redujo destacados/relacionados a 3-4 items; comidas y mitos mantuvieron frontend legacy pero recortaron bloques secundarios alfabeticos de 12 a 6.

- **Criterio de trabajo**:
  - `AGENTS.md` fue consolidado y ahora deja explicito que toda implementacion futura debe priorizar performance, UX y SEO desde el inicio, no como correccion posterior.

### Implementado y validado localmente

- **Enciclopedia del folklore argentino**:
  - Frontend publico en `/enciclopedia`, `/enciclopedia/{categorySlug}` y `/enciclopedia/{categorySlug}/{articleSlug}`
  - Backend admin en `/admin/knowledge-articles`
  - API REST en `/api/v1/knowledge-articles` y `/api/v1/knowledge-categories`
  - Sitemap integrado para portada, familias activas y articulos publicados
  - Taxonomia inicial cargada por `KnowledgeCategorySeeder`
  - Relaciones editoriales con `Interprete`, `Cancion`, `Album`, `Festival`, `Event`, `Provincia` y articulos relacionados
  - Cobertura local validada con tests feature del modulo

### Implementado, sin probar en produccion

- **Pasarela de Contenidos** (`/admin/pasarela`): dashboards, social accounts, publication requests, notifications y templates. Codigo completo, nunca probado end-to-end en produccion.
- **Colaboraciones UGC** (`/admin/contribuir`): flujo unificado para contribuciones. Noticias verificadas end-to-end; el resto del flujo todavia requiere validacion operativa completa en produccion.
- **Legales + Meta/Facebook**: paginas publicas en `/privacidad`, `/condiciones`, `/eliminacion-de-datos`, compatibilidad historica en `GET /deleteuserdata`, callback `POST /deleteuserdata` con `signed_request` firmado, persistencia `data_deletion_requests` y estado publico en `/deleteuserdata/status/{confirmationCode}`. La migración ya fue aplicada en producción; resta confirmar las URLs en Meta y probar el callback real.
- **Inventario tecnico y legacy**: auditoria dedicada consolidada en `project/docs/08_inventario_tecnico_legacy.md`, con evidencia de rutas, tablas, modelos, jobs, integraciones, modulos parciales y estado local de `news` / `events` / `noticias` / `shows` / Pasarela.

### Modulos diferidos - proxima version

- **Entrevistas**: rutas activas en `web.php`, controller sin metodos `byArtista`/`show`, sin vistas, modelo inexistente.
- **Radios**: el reemplazo evergreen está desplegado en producción con dominio canónico, API, backoffice, frontend, auditor, sitemap, SEO/schema, Content Refresh y tests. Convive con el legado; falta confirmar activación visible y contenido inicial.
- **Peñas**: módulo evergreen desplegado en producción en `penia_profiles`, con API, frontend, CRUD, auditor y lote piloto disponible. La carga, verificación humana y derechos de imagen siguen pendientes antes de automatizar o publicar en volumen.
- **Videos**: existe `Frontend/VideosController`, pero referencia un modelo/modulo no consolidado. Debe tratarse como componente incompleto.

---

## Bugs corregidos

### 2026-04-26

| Archivo | Problema | Fix |
|---------|----------|-----|
| `BusquedaController` | Usaba columnas inexistentes en DB para Noticias y Shows | Cambiado a `News::where('title', ...)` y `Event::where('title', ...)` |
| `SitemapController` | Consultaba `events` con campos legacy `estado` y `fecha` | Cambiado a `editorial_status` y `start_at` |
| `SitemapController` | `newsIndex()` usaba `Noticia::where('estado', 1)` | Cambiado a `News::where('editorial_status', 'published')` |

### 2026-08-04

| Archivo | Problema | Fix |
|---------|----------|-----|
| `app/Http/Controllers/Backend/ContributionController.php` | Archivo sintacticamente roto, bloqueaba comandos Artisan globales | Reescritura completa del controlador y flujo de aprobacion/rechazo |
| `database/migrations/2026_08_04_010200_create_knowledge_article_relationship_tables.php` | Tipos FK incompatibles con tablas legacy y DDL inestable en MariaDB local | Migracion reescrita con tipos reales e indices inline creados por SQL explicito |
| `app/Http/Controllers/Api/KnowledgeArticleController.php` | La API dependia del middleware de ruta y no reforzaba policy en controlador | Se agregaron autorizaciones explicitas por policy |

### 2026-08-06

| Archivo | Problema | Fix |
|---------|----------|-----|
| `app/Http/Controllers/Frontend/SitemapController.php` + `routes/web.php` | `sitemap.xml` indexaba solo un sitemap general y un sitemap news acoplados; habia mezcla de familias y compatibilidad legacy poco clara | Se separo la arquitectura en sitemaps por tipo (`estaticas`, `artistas`, `biografias`, `noticias`, `google-news`, `eventos`, `festivales`, `discografias`, `letras`, `evergreen`) y se agregaron redirects 301 desde `/sitemap-main.xml` y `/sitemap-news.xml` |
| `resources/views/sitemap-*.blade.php` | XML de sitemaps con estructura fija y metadatos no semanticos para todas las familias | Se reemplazo por vistas especificas para sitemapindex, urlset generico y Google News usando fechas tolerantes a legacy |
| `app/Http/Controllers/Frontend/NoticiasController.php` + `resources/views/frontend/noticias/show.blade.php` | Noticias publicadas legacy podian responder 500 por `created_at` nulo y metadatos SEO inflexibles | Se reforzo el filtro publico/no futuro y se agregaron fallbacks seguros para `published_at` / `created_at` / `updated_at` sin ocultar 404 reales |
| `app/Http/Middleware/EnforceCanonicalDomain.php` + `app/Http/Kernel.php` | La consolidacion de `www` a `https://mifolkloreargentino.com` dependia solo de Apache y no estaba garantizada cuando la request llegaba a Laravel | Se agrego middleware global posterior a `TrustProxies` para forzar host/protocolo canonicos respetando `X-Forwarded-*` y manteniendo ruta + query string |

### 2026-08-09 / 2026-08-10

| Area | Problema | Fix |
|------|----------|-----|
| Backend listados | Listados administrativos pesados, con demasiados registros, imagenes y procesamiento del lado cliente | Paginacion server-side, consultas mas selectivas y simplificacion de vistas para `news`, `events`, `festivales`, `interpretes` y `users` |
| Frontend queries | Varias homes e indices publicos ejecutaban demasiadas queries o bloques repetidos | Se agrego cache por seccion, eager loading mas fino y reduccion de consultas accesorias |
| Vistas/components | Existian checks costosos por item (`file_exists`) y tarjetas con demasiado peso de render | Se eliminaron checks innecesarios, se uso imagen optimizada minimal donde correspondia y se redujo markup inicial en secciones alfabeticas |
| Mobile frontend | El frontend publico arrastraba JS/CSS global del backend y terceros al arranque | Se separo bundle publico (`app-public.css/js`), se quito Font Awesome global, se minimizo JS base y se difirio carga de Analytics/AdSense |
| Imagenes | El componente de imagen iniciaba demasiado grande para ciertos contextos | `optimized-image.blade.php` ahora selecciona un `src` inicial acorde a la variante (`card`, `hero`, `detail`, etc.) y mantiene `srcset` |
| Render mobile | Sidebar, footer y bloques secundarios seguian penalizando el render inicial | Se introdujo `content-visibility: auto` (`cv-auto`) en sidebar global, footer y secciones secundarias de home, noticias, festivales y enciclopedia |
| Gobernanza del repo | `AGENTS.md` estaba incompleto respecto del criterio operativo actual | Se consolido el archivo y se incorporo la politica transversal de performance, UX y SEO por defecto |
| API Enciclopedia / automatizacion editorial | El circuito Google Sheets/Apps Script podia bloquear Evergreen con `422` por categoria si la automatizacion dependia de IDs numericos o mandaba una familia invalida/inactiva | La API ahora resuelve categoria evergreen por `knowledge_category_id`, `knowledge_category_slug` o `knowledge_category_name`, exige que la familia exista y este activa, y devuelve `code: BLOQUEADO_CATEGORIA` cuando el error es deterministico |

### 2026-08-20

| Area | Problema | Fix |
|------|----------|-----|
| Cumplimiento Meta / legales | Las URLs requeridas por Meta para politica, condiciones, eliminacion de datos y callback devolvian `404` y no existia trazabilidad local para solicitudes de borrado | Se agregaron paginas publicas legales, callback `POST /deleteuserdata` con verificacion `HMAC-SHA256`, persistencia `data_deletion_requests`, estado publico por codigo y limpieza transaccional/idempotente de vinculos Facebook |
| Seguridad web | El callback externo de Meta necesitaba entrar sin CSRF, pero no podia abrirse toda la proteccion del sitio | `VerifyCsrfToken` ahora exceptua solo `deleteuserdata` y el endpoint usa `throttle:meta-deleteuserdata` |
| Configuracion OAuth Facebook | La configuracion seguia dependiendo de `FACEBOOK_REDIRECT_URL` y no dejaba explicita la URI canonica requerida | `config/services.php` ahora acepta `FACEBOOK_REDIRECT_URI` con fallback seguro, `.env.example` expone las variables Facebook/Google y la callback canonica `.com` |
| Testing | No habia pruebas automaticas para el flujo legal/Meta | Se agrego `tests/Feature/MetaDataDeletionFlowTest.php` cubriendo paginas `200`, firma valida/invalida, idempotencia, URL canonica, estado publico y preservacion editorial |
| Operacion autonoma MFA | La primera via implementada quedo atada a Laravel + `codex exec`, no es apta como ejecucion live confiable en este host y cerro `BL-0011F` con evidencia local insuficiente | La auditoria reabrio `BL-0011F` en Drive como `Parcial`; se preparo una alternativa nativa read-only en PowerShell con tarea programada deshabilitada y sin escrituras automáticas activas |

### Automatizacion del backlog MFA

- Via anterior auditada:
  - `php artisan mfa:orchestrate-backlog --project=mfa`
  - dependia de `config/mfa_orchestrator.php`
  - el bridge live previsto usaba `codex exec`
  - `codex exec --help` sigue devolviendo `Acceso denegado` en este host
  - el adapter live no quedo completo para lectura/escritura real de Drive
- Correccion operativa:
  - `BL-0011F` fue reabierta en Drive como `Parcial`
  - el cierre por fixture/snapshot no se considera evidencia suficiente para marcar `Hecha` en el backlog real
- Via preparada al cierre de esta sesion:
  - runner nativo read-only: `scripts/run_mfa_backlog_readonly.ps1`
  - prueba local segura: `scripts/test_mfa_backlog_readonly.ps1`
  - tarea programada nativa deshabilitada: `scripts/register_mfa_backlog_readonly_task.ps1`
  - guia operativa: `project/docs/ia/backlog_drive_readonly_runner.md`
- Backlog local:
  - `project/docs/backlog.json` queda marcado como `legacy_reference_only`

---

## Archivos eliminados - 2026-04-26

| Archivo | Motivo |
|---------|--------|
| `routes/pasarela.php` | Duplicado no cargado - rutas ya en `admin.php` |
| `routes/_web.php` | Backup antiguo de `web.php`, no cargado |
| `app/Http/Controllers/Backend/ShowController.php` | Reemplazado por `EventController`, tenia bugs de tipo |
| `app/Http/Controllers/Backend/NoticiaController.php` | Reemplazado por `NewsController` |

---

## Modelos y politicas

- **`EventPolicy`**: CRUD de `Event`. Admins con acceso total, colaboradores pueden crear/editar propios.
- **`NewsPolicy`**: CRUD de `News`. Compatibilidad con permisos legacy `read noticia`.
- **`KnowledgeArticlePolicy`**: CRUD de `KnowledgeArticle`. Reutiliza permisos legacy de Noticias y acceso total para `administrador`.
- **`FestivalPolicy`**: autorizacion del CRUD y API modernizada de Festivales.
- **`InterpretePolicy`**: autorizacion de Biografias/Artistas.
- **`ComidaPolicy`**: autorizacion de Recetas.
- **`MitoPolicy`**: autorizacion de Mitos y Leyendas.
- **`AlbumPolicy`** y **`CancionPolicy`**: autorizacion de Discografia/Cancionero.
- Las policies de dominio estan registradas en `AuthServiceProvider`.

---

## Validacion local reciente

- `php artisan migrate:status`
- `php artisan test tests/Feature/Knowledge/KnowledgeAuthorizationTest.php tests/Feature/Knowledge/KnowledgeCategorySeederTest.php tests/Feature/Knowledge/KnowledgeArticleApiTest.php tests/Feature/Knowledge/KnowledgeFrontendVisibilityTest.php`
- `php artisan test tests/Feature/Festivals/FestivalFrontendRestructureTest.php tests/Feature/Seo/PublicTemplateSeoTest.php tests/Feature/Seo/SeoInfrastructureTest.php`
- `php artisan test tests/Feature/Mitos/MitosFrontendTest.php`
- `docker compose exec -T app php artisan route:list --path=deleteuserdata`
- `docker compose exec -T app php artisan test tests/Feature/MetaDataDeletionFlowTest.php`
- `docker compose exec -T app php artisan test`
- `docker compose exec -T app php artisan test tests/Feature/Performance/PublicMobilePerformanceBudgetTest.php tests/Feature/Recipes/PublicRecipesFrontendTest.php tests/Feature/Mitos/MitosFrontendTest.php tests/Feature/Festivals/FestivalFrontendRestructureTest.php`
- `curl -I http://localhost/privacidad`
- `curl -I http://localhost/condiciones`
- `curl -I http://localhost/eliminacion-de-datos`
- `curl -I http://localhost/deleteuserdata`

Resultados recientes relevantes:

- Knowledge: `9 passed (18 assertions)`
- SEO + Festivales + Canonical + Sitemaps: `30 passed (52060 assertions)` en la tanda previa y `27 passed (52046 assertions)` en las ultimas tandas de performance/mobile
- Festivales, Biografias, Recetas y Mitos: releases tecnicos integrados por PR/CI y desplegados por oleadas; auditores reales ejecutados en produccion el 2026-09-01
- Meta legales/data deletion: `10 passed (50 assertions)`
- Performance public mobile budget + recetas + mitos + festivales: `12 passed (57 assertions)`
- `php artisan test --list-tests`: carga correctamente. El antiguo bloqueo sintactico de `TwoFactorAuthenticationSettingsTest.php` fue resuelto al retirar la suite Jetstream obsoleta en `9b3dc68`.

### Medicion local orientativa de performance

Mejoras observadas en entorno local Docker/Apache antes de despliegue:

- Home: paso de tiempos significativamente mas altos a respuestas cercanas a sub-segundo en escenarios estables de cache local.
- `festivales-y-fiestas-tradicionales`: bajo a menos de 1s en una de las pasadas medidas.
- Paginas por letra de `mitos` y `recetas`: bajaron de ~2.5-3s a menos de 1s tras reducir cards auxiliares y peso visual.

Nota: los `curl` locales no reflejan completamente la mejora de Core Web Vitals mobile. El impacto real debe validarse nuevamente en produccion con PageSpeed Insights luego del deploy, la compilacion de assets y el refresco de caches.

---

## Pendientes identificados

1. Probar Pasarela de Contenidos end-to-end en browser.
2. Volver a medir en PageSpeed Insights mobile y desktop sobre produccion para cuantificar el impacto real de las tres tandas de performance.
3. Extender el criterio del bundle publico liviano y del render diferido a otras plantillas publicas que aun queden pesadas.
4. Evaluar limpieza de tablas legacy (`noticias`, `shows`, `images`) despues de confirmar que no exista informacion unica.
5. Mantener vigilancia sobre MariaDB local: el volumen Docker `lmfa_db_data` reemplazo el bind mount que fallaba y supero 81 migraciones y la suite completa; conservar `database_local` como respaldo hasta varias sesiones estables.
6. Validar en el Apps Script externo que los `422` con `code: BLOQUEADO_CATEGORIA` pasen a estado de correccion y no vuelvan a reintentarse automaticamente.
7. Confirmar en Meta las URLs canonicas nuevas (`/privacidad`, `/condiciones`, `/deleteuserdata`, `/auth/facebook/callback`) y probar el callback real; la migración productiva ya fue aplicada.
8. Mantener los seeders demo idempotentes y no usarlos como evidencia editorial o de staging: el lote local ya fue cargado y verificado.

9. Cerrar en produccion los seis casos controlados de la integracion editorial: CREAR y ACTUALIZAR un Artista, una Receta y un Mito; luego reactivar el flujo automatico con monitoreo de errores.
10. Confirmar en producción la lectura efectiva de los flags, limpiar config/response cache y verificar NAV y rutas de Peñas/Radios; luego ejecutar casos controlados antes de automatizar volumen.

11. Completar los primeros lotes de curacion de Biografias, Recetas y Mitos, registrando score antes/despues y evitando actualizaciones masivas sin revision.

12. La auditoría de `BL-0018A` mapeó 4.608 canciones activas y 397 discos activos con rutas, pivots y señales de tráfico locales. Las letras completas carecen de campos de fuente, licencia, autoría o autorización: no se debe automatizar ni ampliar su publicación hasta que exista una decisión humana de derechos y una spec aprobada que distinga obra, versión/grabación y créditos.

### Reparacion operativa local 2026-09-09: permisos de vistas

- Pedido del usuario: recuperar el sitio local ante `file_put_contents(...storage/framework/views/...): Permission denied`.
- Causa comprobada: vista compilada propiedad de `root:root` con modo `644`; Apache ejecuta como `www-data`.
- Se corrigio exclusivamente la propiedad de archivos/directorios de root en `storage/framework/views`, `storage/framework/cache`, `storage/framework/sessions`, `storage/logs` y `bootstrap/cache` dentro de `lmfa-app-1` a `www-data:www-data`.
- Validacion: archivo escribible por www-data y home `http://mfa.localhost` con HTTP 200 en dos solicitudes; primer TTFB 0.768 s. Sin cambios de codigo, BD ni produccion.
- Prevencion: ejecutar comandos Artisan que generan caches con `docker compose exec --user www-data app php artisan ...`, para evitar recrear archivos como root.
### Build versionado 2026-09-09

- Aprobacion explicita del usuario y spec `openspec/specs/version-local-vite-build.md`: distribuir `public/build` completo mediante Git.
- Se retiro `/public/build` de `.gitignore`; node_modules y public/hot siguen ignorados. Runbook actualizado.
- Validacion: `npm run build` correcto y `npm run build:verify` confirma 13 assets; Git detecta todos los archivos generados. Mismos assets y hashes que antes: sin costo adicional de runtime; mayor historial Git.
- Pendiente del usuario: incluir codigo y build en el commit, push e integracion en main antes del pull productivo. No se realizo commit ni despliegue.
### Incidente de datos locales 2026-09-09

- Auditoria de solo lectura: volumen activo `lmfa_lmfa_db_data`, creado 2026-09-04 23:44:35 UTC. Commit `9108865` sustituyo `./database_local` por volumen nombrado; tablas actuales creadas alrededor de 23:59 UTC y 81 migraciones en batch 1.
- Conteos exactos: news/comidas/mitos/albunes/canciones = 0; events/interpretes/festivales = 2 cada una. Coinciden con reconstruccion y escenario demo documentados; no evidencian restauracion de los datos editoriales anteriores.
- El archivo `storage/app/local-backups/mfa-pre-named-volume-20260904-2355.sql` tiene 0 bytes: NO es un respaldo utilizable. `database_local` aun existe pero su recuperabilidad no fue comprobada. No montar ni modificar el original para intentar recuperar.
- Riesgo adicional: .env.testing apunta a mfa en db, igual que .env, y tres tests de autenticacion usaban RefreshDatabase. Se reemplazo por DatabaseTransactions segun AGENTS.md, con spec `openspec/specs/prevent-local-auth-tests-database-reset.md`; sintaxis PHP correcta. No se ejecuto la suite ni SQL modificatorio. No hay impacto en runtime/performance del sitio.
- general_log y log_bin estan OFF: no se puede atribuir el borrado a una ejecucion concreta con esa evidencia. Proximo paso: localizar respaldo valido o evaluar recuperacion sobre copia aislada del almacenamiento anterior, con autorizacion antes de restaurar o crear otra BD.
### Revision de respaldos locales 2026-09-09

- Busqueda de dumps y archivos de backup en proyecto, Downloads, Documents y Desktop: no se encontro otro respaldo SQL de MFA utilizable. El dump previo al volumen sigue en 0 bytes.
- Inspeccion binaria de solo lectura: los .ibd antiguos de interpretes, canciones, albunes, comidas, mitos, festivales, noticias y shows contienen exclusivamente bytes cero. Esos archivos no proporcionan datos para restaurar esas entidades; no se hizo recuperacion forense de ibdata1/redo ni se afirma recuperabilidad de esos otros archivos.
- No se arranco MariaDB sobre el almacenamiento anterior, no se modificaron datos y no se importo nada. Siguiente via acordada: recibir backup de produccion solo de datos, verificar tablas/columnas/conflictos y preservar base local antes de una importacion autorizada.
### Recuperacion local aplicada 2026-09-09

- Usuario autorizo explicitamente reemplazar la BDD local desde `u376128922_mifolkarg.sql`. Spec: `openspec/specs/restore-local-production-data-20260909.md`.
- Restaurados: 445 interpretes, 4608 canciones, 411 discos, 808 recetas, 284 mitos, 364 noticias, 309 eventos, 47 festivales, 40 articulos de enciclopedia y 434 usuarios, con pivots/catalogos del respaldo. Datos demo reemplazados; Penias y Radios quedan vacias porque el backup no contiene registros de esos modulos.
- Esquema local conservado y 81 migraciones sin cambios. Se omitieron noticias legacy inexistente localmente y columnas adicionales de shows; shows.detalle NULL se adapto a texto vacio. El dump original completo permanece disponible.
- Sesiones, tokens de acceso/recuperacion, cuentas sociales y colas excluidos; remember_token y secretos 2FA no importados. Sin cambios productivos ni ejecucion de migraciones.
- Respaldo previo valido: `storage/app/local-backups/mfa-before-production-data-import-20260909.sql` (155947 bytes). Resultados: `import-check.json` e `import-result.json` en la misma carpeta ignorada por Git.
- Validacion: ensayo transaccional revertido y luego importacion confirmada; conteos exactos y 87 relaciones FK sin huerfanos; sintaxis del runner PHP valida. No se ejecuto suite de tests sobre los datos recuperados.
- QA HTTP: home, login, noticias, recetas y festivales 200. Home con cache caliente TTFB 0.490 s; primeras respuestas 1.95-5.54 s en Docker local. Cache de aplicacion limpiada. No se verifico autenticacion interactiva ni disponibilidad de todas las imagenes; el SQL no incluye archivos multimedia.
